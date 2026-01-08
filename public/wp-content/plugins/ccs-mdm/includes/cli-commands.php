<?php
declare(strict_types=1);

namespace CCS\MDMImport;


use App\Model\Framework;
use App\Model\Lot;
use App\Model\LotSupplier;
use App\Model\Supplier;
use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;
use App\Services\Database\DatabaseConnection;
use App\Services\Logger\ImportLogger;
use App\Services\MDM\MdmApi;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use App\Services\OpGenie\OpGenieLogger;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use WP_CLI;

WP_CLI::add_command('mdm-import', Import::class);

class Import extends \WP_CLI_Command
{
    private LockFactory $lockFactory;
    private ImportLogger $logger;
    private array $importCount = ['frameworks' => 0, 'lots' => 0, 'suppliers' => 0];
    private array $errorCount = ['frameworks' => 0, 'lots' => 0, 'suppliers' => 0];

    private MdmApi $mdmApi;
    private dbManager $dbManager;
    private SyncText $syncText;
    private FrameworkRepository $frameworkRepository;
    private LotRepository $lotRepository;
    private SupplierRepository $supplierRepository;
    private LotSupplierRepository $lotSupplierRepository;
    private FrameworkSearchClient $frameworkSearchClient;
    private SupplierSearchClient $supplierSearchClient;
    private array $wordpressFrameworks = [];
    private array $wordpressLots = [];

    public function __construct()
    {
        $this->initializeResources();
    }

    /**
    * Logic separated so it can be called explicitly if __construct is bypassed
    */
    private function initializeResources(): void
    {
        if (isset($this->lockFactory)) {
            return;
        }

        // Initialise lock
        $store = new FlockStore(sys_get_temp_dir());
        $this->lockFactory = new LockFactory($store);
        
        // Initialise logger
        $this->logger = new ImportLogger();

        // Initialise resources
        $this->mdmApi = new MdmApi();
        $this->syncText = new SyncText();
        $this->frameworkRepository = new FrameworkRepository();
        $this->dbManager = new dbManager(new DatabaseConnection());
        $this->lotRepository = new LotRepository();
        $this->supplierRepository = new SupplierRepository();
        $this->lotSupplierRepository = new LotSupplierRepository();
        $this->frameworkSearchClient = new FrameworkSearchClient();
        $this->supplierSearchClient = new SupplierSearchClient();
    }

    public function importAll(): void {

        $this->initializeResources(); // Safety check for tests
        $start_time = microtime(true);

        $lock = $this->lockFactory->createLock('ccs-mdm-import-all');

        if (!$lock->acquire()) {
            $this->addErrorAndExit('Lock file is currently in use by another process, quitting script');
        }

        try {
            $this->wordpressFrameworks = $this->syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $this->syncText->getLotsFromWordPress();
        } catch (\Exception $e) {
            $this->addErrorAndExit("Process cannot complete without WordPress data. Error: {$e->getMessage()}");
        }

        WP_CLI::line("Starting import all frameworks");

        try {
            $frameworkRmNumbers = $this->mdmApi->getAgreementsRmNumbers();
        } catch (\Exception $e) {
            $this->addErrorAndExit("Failed to retrieve frameworks from MDM: " . $e->getMessage());
        }

        $this->addSuccess(count($frameworkRmNumbers) . " frameworks returned from MDM.", null, true);

        $importCounter = 0;

        foreach ($frameworkRmNumbers as $rmNumber) {
            $this->single($rmNumber);
            $importCounter++;

            if ($importCounter % 25 === 0) {
                WP_CLI::line("{$importCounter} frameworks imported so far");
            }
        }

        $this->addSuccess(sprintf('Import took %s seconds to run', round(microtime(true) - $start_time, 2)), null, true);

        $this->printSummary();

        WP_CLI::line("Starting post-import tasks...");

        $this->postImportTask();

        WP_CLI::success("Import process completed.");

    }
    public function importSingle(array $args): void {
        $this->initializeResources(); // Safety check for tests
        $start_time = microtime(true);
        
        $lock = $this->lockFactory->createLock('ccs-mdm-import-single');

        if (!$lock->acquire()) {
            $this->addErrorAndExit('Lock file is currently in use by another process, quitting script');
        }

        $rmNumber = $args[0] ?? null;
        if (empty($rmNumber)) {
            WP_CLI::error('RM number is required');
            return; 
        }

        WP_CLI::line("Starting single import for RM number: $rmNumber");

        try {
            $this->wordpressFrameworks = $this->syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $this->syncText->getLotsFromWordPress();
        } catch (\Exception $e) {
            $this->addErrorAndExit("Process cannot complete without WordPress data. Error: {$e->getMessage()}");
        }

        $this->single($rmNumber);

        WP_CLI::success(sprintf('Import took %s seconds to run', round(microtime(true) - $start_time, 2)));

        $this->printSummary();

        WP_CLI::line("Starting post-import tasks...");

        $this->postImportTask();

        $lock->release();

        WP_CLI::success("Import completed for $rmNumber.");
    }


    private function single(string $rmNumber): void 
    {   
        try {
            $framework = $this->mdmApi->getAgreement($rmNumber);

            if (!$framework?->getSalesforceId()) {
                $this->addError("Framework data for $rmNumber is invalid or missing Salesforce ID.");
                return;
            }

            $this->frameworkRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $framework->getSalesforceId(), $framework);
            $framework = $this->frameworkRepository->findById($framework->getSalesforceId(), 'salesforce_id');
            $this->ensureWordPressPostExists($framework, 'framework');

            $lots = $this->mdmApi->getAgreementLots($framework->getSalesforceId());
            $this->checkAndDeleteLots($lots, $framework);

            foreach ($lots as $lot) {
                //we are skipping non-live frameworks and non-live lots
                if ($framework->getStatus() != "Live" || $lot->getStatus() != "Live") { 
                    return;
                }

                $lot->setWordpressId($this->dbManager->getLotWordpressIdBySalesforceId($lot->getSalesforceId()));
                $this->lotRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $lot->getSalesforceId(), $lot);
                $lot = $this->lotRepository->findById($lot->getSalesforceId(), 'salesforce_id');
                $this->ensureWordPressPostExists($lot, 'lot');

                if ($lot->isHideSuppliers()) {
                    $this->lotSupplierRepository->deleteById($lot->getSalesforceId(), 'lot_id');
                    continue;
                }

                $suppliers = $this->mdmApi->getLotSuppliers($lot->getSalesforceId());
                $this->checkAndDeleteSuppliers($suppliers, $lot->getSalesforceId());

                foreach ($suppliers as $supplier) {
                    if (empty($supplier->getSalesforceId())) {
                        $this->addError("Missing Salesforce ID for supplier on lot {$lot->getSalesforceId()}", 'suppliers');
                        continue;
                    }

                    $this->supplierRepository->createOrUpdateExcludingLiveFrameworkField('salesforce_id', $supplier->getSalesforceId(), $supplier);
                    
                    $lotSupplier = $this->lotSupplierRepository->findByLotIdAndSupplierId($lot->getSalesforceId(), $supplier->getSalesforceId());

                    if (!$lotSupplier) {
                        $this->lotSupplierRepository->create(new LotSupplier([
                            'lot_id'        => $lot->getSalesforceId(),
                            'supplier_id'   => $supplier->getSalesforceId(),
                            'contact_name'  => $supplier->getContactName(),
                            'contact_email' => $supplier->getContactEmail(),
                            'trading_name'  => $supplier->getTradingName(),
                        ]));
                    } else {
                        $lotSupplier->setContactName($supplier->getContactName())
                                    ->setContactEmail($supplier->getContactEmail())
                                    ->setTradingName($supplier->getTradingName());
                        
                        $this->lotSupplierRepository->update('id', $lotSupplier->getId(), $lotSupplier);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->addError("Something went wrong while importing $rmNumber: " . $e->getMessage());
            return;
        }
    }

    /**
     * Helper to handle WordPress post creation for both Frameworks and Lots.
     */
    private function ensureWordPressPostExists(object $entity, string $type): void
    {
        if ($entity->getWordpressId()) {
            return;
        }

        $metaKey = $type === 'framework' ? 'framework_id' : 'lot_id';
        
        $wordpressId = wp_insert_post([
            'post_title' => $entity->getTitle() ?: "Untitled $type - " . $entity->getSalesforceId(),
            'post_type'  => $type,
            'post_status' => 'draft',
        ]);

        if (is_wp_error($wordpressId) || $wordpressId === 0) {
            $this->addError("Failed to create WordPress post for $type: " . $entity->getSalesforceId());
            return;
        }

        update_field($metaKey, $entity->getSalesforceId(), $wordpressId);
        $entity->setWordpressId((string) $wordpressId);

        $repository = $type === 'framework' ? $this->frameworkRepository : $this->lotRepository;
        $repository->update('salesforce_id', $entity->getSalesforceId(), $entity, true);
        
        WP_CLI::success("Created $type in WordPress (ID: $wordpressId).");
    }

    public function checkAndDeleteLots(array $lots, Framework $framework): void 
    {
        $lotIdsFromAPI = array_map(fn($lot) => $lot->getSalesforceId(), $lots);
        $localLotIds = (array) $this->dbManager->getLotSalesforceIdByFrameworkId($framework->getSalesforceId());

        foreach (array_diff($localLotIds, $lotIdsFromAPI) as $lotToDelete) {
            $this->logger->info("Deleting lot: $lotToDelete from {$framework->getRmNumber()}");
            $this->lotRepository->delete($lotToDelete);
            $this->dbManager->deleteLotPostInWordpress($this->dbManager->getLotWordpressIdBySalesforceId($lotToDelete));
            $this->addSuccess("Lot $lotToDelete deleted.");
        }
    }

    protected function checkAndDeleteSuppliers(array $suppliers, string $lotId): void
    {
        $supplierIdsFromAPI = array_map(fn($s) => $s->getSalesforceId(), $suppliers);
        $localSuppliers = (array) $this->dbManager->getLotSuppliersSalesforceIdByLotId($lotId);

        foreach (array_diff($localSuppliers, $supplierIdsFromAPI) as $supplierToDelete) {
            $this->lotSupplierRepository->deleteByLotIdAndSupplierId($lotId, $supplierToDelete);
            $this->addSuccess("Lot supplier $supplierToDelete deleted.");
        }
    }

    protected function checkAllSuppliersIfOnLiveFrameworks(): void 
    {
        foreach ($this->supplierRepository->findAll() as $supplier) {
            $count = $this->frameworkRepository->countAllSupplierLiveFrameworks($supplier->getSalesforceId());
            $this->supplierRepository->updateOnLiveField('salesforce_id', $supplier->getSalesforceId(), $count > 0);
        }
    }

    public function checkEventCron() {
        $cron_jobs = get_option('cron');
        
        $check_event_dates = false;
        foreach ((array) $cron_jobs as $cron_job) {
            if (array_key_exists("check_event_dates", (array) $cron_job)){
                $check_event_dates = true;
                $this->addSuccess('"check_event_dates" cron job exist');

                break;
            }
        }

        if ($check_event_dates == false && getenv('CCS_FRONTEND_APP_ENV') == 'prod'){
            $OpGenieLogger = new OpGenieLogger();

            $OpGenieLogger->sendToOPGenie([  
                'priority' => 'P2',
                'message' => 'Website - Event Cron job disappear',
                'description' => 'The check_event_dates cron job has disappear, please start the "S24 Event Unpublisher" plugin on Wordpress.',
                'impactedServices' => [getenv('websiteProjectIDOnOPGenie')],
                'tags' => [strtoupper(getenv('CCS_FRONTEND_APP_ENV'))]
                ]);
        }
    }

    public function updateFrameworkSearchIndex()
    {
        WP_CLI::success('Beginning Search index update on Frameworks.');

        $frameworks = $this->frameworkRepository->findAll();

        WP_CLI::success(count($frameworks) . ' Frameworks found');

        $indexStatus = array('Live', 'Expired - Data Still Received', 'Future (Pipeline)', 'Planned (Pipeline)', 'Underway (Pipeline)', 'Awarded (Pipeline)');

        foreach ($frameworks as $framework)
        {
            if (in_array($framework->getStatus(), $indexStatus)) {
                $this->updateFrameworkSearchIndexWithSingleFramework($framework);
            }else{
                $this->frameworkSearchClient->removeDocument($framework);
            }
        }

        WP_CLI::success('updateFrameworkSearchIndex operation completed successfully.');

        return;
    }

    protected function updateFrameworkSearchIndexWithSingleFramework(Framework $framework) {
        WP_CLI::success('Updating Framework index for: ' . $framework->getRmNumber());

        $lots = $this->lotRepository->findAllById($framework->getSalesforceId(), 'framework_id');

        if (!$lots) {
            $lots = [];
        }

        $this->frameworkSearchClient->createOrUpdateDocument($framework, $lots);
    }

    public function updateSupplierSearchIndex()
    {
        WP_CLI::success('Beginning Search index update on Suppliers.');

        $suppliers = $this->supplierRepository->findAll();

        WP_CLI::success(count($suppliers) . ' Suppliers found');

        $count = 0;

        foreach ($suppliers as $supplier) {

            $liveFrameworks = $this->frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
            $dpsFrameworkCount = 0;
            $totalFrameworkCount = 0;

            if (!empty($liveFrameworks))
            {
                $totalFrameworkCount = count($liveFrameworks);

                foreach ($liveFrameworks as $liveFramework)
                {
                    $lots = $this->lotRepository->findAllByFrameworkIdSupplierId($liveFramework->getSalesforceId(), $supplier->getSalesforceId());
                    $liveFramework->setLots($lots);

                    if ($liveFramework->getTerms() == 'DPS' || $liveFramework->getType() == 'Dynamic purchasing system' ){
                        $dpsFrameworkCount++;
                    }
                }
            }

            $alternativeTradingNames = [];
            $lotSuppliers = $this->lotSupplierRepository->findAllById($supplier->getSalesforceId(), 'supplier_id');

            if (!empty($lotSuppliers))
            {

                foreach ($lotSuppliers as $lotSupplier)
                {
                    if (!empty($lotSupplier->getTradingName())) {
                        $alternativeTradingNames[$lotSupplier->getTradingName()] = $lotSupplier->getTradingName();
                    }
                }

                if (!empty($supplier->getTradingName())) {
                    $alternativeTradingNames[$supplier->getTradingName()] = $supplier->getTradingName();
                }

                $supplier->setAlternativeTradingNames(array_values($alternativeTradingNames));
            }


            if (!$liveFrameworks || $dpsFrameworkCount == $totalFrameworkCount ) {
                $this->supplierSearchClient->removeDocument($supplier);
            } else {
                $this->supplierSearchClient->createOrUpdateDocument($supplier, $liveFrameworks);
            }

            $count++;

            if ($count % 50 == 0) {
                WP_CLI::success($count . ' Suppliers imported...');
            }

        }

        WP_CLI::success('updateSupplierSearchIndex operation completed successfully.');

        return;
    }
    private function postImportTask(): void
    {
        // Check the event cron job exists
        $this->checkEventCron();

        //Mark whether a supplier has any live frameworks
        $this->checkAllSuppliersIfOnLiveFrameworks();

        //Update framework titles in WordPress to include the RM number
        $this->dbManager->updateFrameworkTitleInWordpress();

        //Update lot titles in WordPress to include the RM number and the lot number
        $this->dbManager->updateLotTitleInWordpress();

        // Update elasticsearch indexes
        // TODO
        // $this->updateFrameworkSearchIndex();
        // $this->updateSupplierSearchIndex();
    }

    private function addError(string $message, ?string $type = null): void 
    {
        $this->logger->error($message);
        WP_CLI::line("Error: $message");
        if ($type) {
            $this->errorCount[$type]++;
        }
    }

    private function addErrorAndExit(string $message): void 
    {
        $this->logger->error($message);
        WP_CLI::error($message, true);
    }

    private function addSuccess(string $message, ?string $type = null, bool $log = false): void 
    {
        if ($log) {
            $this->logger->info($message);
            WP_CLI::line($message);
        }
        if ($type) {
            $this->importCount[$type]++;
        }
    }

    private function printSummary(): void
    {
        WP_CLI::line("===== Summary =====");
        $this->frameworkRepository->printImportCount();
        $this->lotRepository->printImportCount();
        $this->lotSupplierRepository->printImportCount();
        WP_CLI::line("===================");
    }
}

// Usage:
// wp mdm-import importSingle RM526
// wp mdm-import importAll