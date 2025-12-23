<?php
declare(strict_types=1);

namespace CCS\MDMImport;

use DateTime;
use DateTimeZone;
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
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;
use WP_CLI;

WP_CLI::add_command('mdm-import', Import::class);

class Import extends \WP_CLI_Command
{
    private LockFactory $lockFactory;
    private ImportLogger $logger;
    private DateTime $timer;
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
        // Initialise lock
        $store = new FlockStore(sys_get_temp_dir());
        $this->lockFactory = new LockFactory($store);
        
        // Initialise logger and timer
        $this->logger = new ImportLogger();
        $this->timer = new DateTime("now", new DateTimeZone('Europe/London')); 

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

    public function importSingle(array $args): void 
    {
        $rmNumber = $args[0] ?? null; 
        if (!$rmNumber) {
            $this->addError('RM number is required');
            return;
        }
        
        WP_CLI::line("Starting single import for RM number: $rmNumber");

        try {
            $this->wordpressFrameworks = $this->syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $this->syncText->getLotsFromWordPress();
        } catch (\Exception $e) {
            $this->addErrorAndExit("Process cannot complete without WordPress data. Error: {$e->getMessage()}");
        }

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
        
        $this->checkAllSuppliersIfOnLiveFrameworks();
        $this->dbManager->updateFrameworkTitleInWordpress();
        $this->dbManager->updateLotTitleInWordpress();

        $this->printSummary();
        WP_CLI::success("Import completed for $rmNumber.");
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
            'post_status' => 'publish'
        ]);

        if (is_wp_error($wordpressId) || $wordpressId === 0) {
            $this->addError("Failed to create WordPress post for $type: " . $entity->getSalesforceId());
            return;
        }

        update_field($metaKey, $entity->getSalesforceId(), $wordpressId);
        $entity->setWordpressId($wordpressId);

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