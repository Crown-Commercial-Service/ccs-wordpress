<?php
namespace CCS\MDMImport;
use \Datetime;
use \DateTimeZone;

// Composer
require __DIR__ . '/../../../../../vendor/autoload.php';

use App\Model\Framework;
use App\Search\FrameworkSearchClient;
use App\Search\SupplierSearchClient;
use App\Services\Database\DatabaseConnection;
use App\Services\Logger\ImportLogger;
use App\Search\AbstractSearchClient;
use \WP_CLI;

use App\Model\LotSupplier;
use App\Model\Supplier;
use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;
use App\Services\MDM\MdmApi;
use App\Services\Salesforce\SalesforceApi;
use App\Services\OpGenie\OpGenieLogger;

use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\FlockStore;


WP_CLI::add_command('mdm-import', 'CCS\MDMImport\Import');


class Import extends \WP_CLI_Command
{
    protected $lockFactory;

    protected $logger;
    protected $timer;
    protected $importCount;
    protected $errorCount;

    protected $mdmApi;

    protected $frameworkRepository;
    protected $lotRepository;
    protected $supplierRepository;
    protected $lotSupplierRepository;
    protected $dbConnection;
    protected $wordpressFrameworks;
    protected $wordpressLots;
    protected $frameworkSearchClient;
    protected $supplierSearchClient;

    public function __construct()
    {
        // Initialise lock
        $store = new FlockStore(sys_get_temp_dir());
        $this->lockFactory = new LockFactory($store);
        
        // Initialise logger and timer
        $this->logger = new ImportLogger();
        $this->timer = new DateTime("now", new DateTimeZone('Europe/London')); 
        $this->importCount = [
            'frameworks' => 0,
            'lots'       => 0,
            'suppliers'  => 0
        ];
        $this->errorCount = [
            'frameworks' => 0,
            'lots'       => 0,
            'suppliers'  => 0
        ];

        // Initialise resources
        $this->mdmApi = new MdmApi();
        $this->frameworkRepository = new FrameworkRepository();
        $this->lotRepository = new LotRepository();
        $this->supplierRepository = new SupplierRepository();
        $this->lotSupplierRepository = new LotSupplierRepository();
        $this->dbConnection = new DatabaseConnection();
        $this->frameworkSearchClient = new FrameworkSearchClient();
        $this->supplierSearchClient = new SupplierSearchClient();
    }

    
    public function importSingle(array $args) {

        $rm_number = $args[0] ?? null; 
        if (!$rm_number) {
            $this->addError('RM number is required');
        }
        
       print "Starting single import for RM number: $rm_number \n";

        try {
            $syncText = new SyncText();
            $this->wordpressFrameworks = $syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $syncText->getLotsFromWordPress();
        } catch (\Exception $e) {
            $this->addError('Error fetching existing frameworks and lots from Wordpress. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework and Lot data from Wordpress');
            die('Process can not complete without Framework and Lot data from Wordpress');
        }

        $framework = null;
        try {
            // Dealing with Agreement
            $framework = $this->mdmApi->getAgreement($rm_number);
            $this->frameworkRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $framework->getSalesforceId(), $framework);
            $framework = $this->frameworkRepository->findById($framework->getSalesforceId(), 'salesforce_id');
            $this->createFrameworkInWordpressIfNeeded($framework);

            // Dealing with Lots
            $lots = $this->mdmApi->getAgreementLots($framework->getSalesforceId());
            $this->checkAndDeleteLots($lots, $framework);
            foreach ($lots as $lot) {
                //wordpress id could be null 
                $lot->setWordpressId($this->getLotWordpressIdBySalesforceId($lot->getSalesforceId()));
                $this->lotRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $lot->getSalesforceId(), $lot);
                $lot = $this->lotRepository->findById($lot->getSalesforceId(), 'salesforce_id');
                $this->createLotInWordpressIfNeeded($lot, $this->wordpressLots);


                // Dealing with Lot Suppliers
                if ($lot->isHideSuppliers()) {
                    $this->lotSupplierRepository->deleteById($lot->getSalesforceId(), 'lot_id');
                    continue;
                }

                $suppliers = $this->mdmApi->getLotSuppliers($lot->getSalesforceId());
                $this->checkAndDeleteSuppliers($suppliers, $lot->getSalesforceId());

                foreach ($suppliers as $supplier) {
                    $this->supplierRepository->createOrUpdateExcludingLiveFrameworkField('salesforce_id', $supplier->getSalesforceId(), $supplier);
                    
                    $lotSupplier = $this->lotSupplierRepository->findByLotIdAndSupplierId($lot->getSalesforceId(), $supplier->getSalesforceId());

                    if (empty($lotSupplier)) {
                        $lotSupplier = new LotSupplier([
                            'lot_id'        => $lot->getSalesforceId(),
                            'supplier_id'   => $supplier->getSalesforceId(),
                            'contact_name'  => $supplier->getContactName(),
                            'contact_email' => $supplier->getContactEmail(),
                            'trading_name'  => $supplier->getTradingName(),
                        ]);

                        $this->lotSupplierRepository->create($lotSupplier);
                    } else {
                        $lotSupplier->setContactName($supplier->getContactName())
                                    ->setContactEmail($supplier->getContactEmail())
                                    ->setTradingName($supplier->getTradingName());
                        
                        $this->lotSupplierRepository->update('id', $lotSupplier->getId(), $lotSupplier);
                    }

                }
            }
        } catch (\Exception $e) {
            $this->addError("Something went wrong while importing $rm_number" . $e->getMessage(), '');
            return;
        }
        
        $this->updateFrameworkSearchIndexWithSingleFramework($framework);
        // $this->checkAllSuppliersIfOnLiveFrameworks();
        // $this->updateSupplierSearchIndex();
        
        $this->updateFrameworkTitleInWordpress();
        $this->updateLotTitleInWordpress();

        $this->printSummary();
        print "I am done bro\n";
    }



    private function createFrameworkInWordpressIfNeeded($framework){
        if (empty($framework->getWordpressId())){
            $wordpressId = wp_insert_post(array(
                'post_title' => $framework->getTitle(),
                'post_type' => 'framework'
            ));

            update_field('framework_id', $framework->getSalesforceId(), $wordpressId);

            WP_CLI::success('Created Framework in Wordpress.');
            $framework->setWordpressId($wordpressId);
            $this->frameworkRepository->update('salesforce_id', $framework->getSalesforceId(), $framework, true);
        }
    }

    private function createLotInWordpressIfNeeded($lot){
        if (empty($lot->getWordpressId())) {
            $wordpressId = wp_insert_post(array(
                'post_title' => $lot->getTitle(),
                'post_type' => 'lot'
            ));
            update_field('lot_id', $lot->getSalesforceId(), $wordpressId);

            WP_CLI::success('Created Lot in Wordpress.');
            $lot->setWordpressId($wordpressId);
            $this->lotRepository->update('salesforce_id', $lot->getSalesforceId(), $lot, true);
        }
    }

    private function getLotWordpressIdBySalesforceId(?string $salesforceId) {
        $lotWordpressId = null;

        $sql = "SELECT wordpress_id FROM ccs_lots WHERE salesforce_id = '" . $salesforceId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetch(\PDO::FETCH_ASSOC);

        if(!empty($sqlData['wordpress_id'])) {
            $lotWordpressId = $sqlData['wordpress_id'];
        }

        return $lotWordpressId;
    }

    private function deleteLotPostInWordpress($wordpressId) {
        $sql = " DELETE FROM ccs_15423_posts WHERE ID = '" . $wordpressId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();
    }

    private function updateFrameworkTitleInWordpress() {
        $sql = <<<EOD
            UPDATE ccs_15423_posts p 
            INNER JOIN ccs_frameworks f
            ON f.wordpress_id = p.id
            SET p.post_title = (SELECT CONCAT(f.rm_number,': ', f.title) FROM ccs_frameworks f WHERE f.wordpress_id = p.id)
            WHERE p.post_type='framework'
        EOD;

        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response) {
            print_r($query->errorInfo());
            throw new \Exception('Framework title could not be updated in the database.');
        }

    }

    private function updateLotTitleInWordpress() {
        $sql = <<<EOD
            UPDATE ccs_15423_posts p SET p.post_title = 
            (SELECT CONCAT(f.rm_number, ' Lot ', l.lot_number, ': ', l.title)
            FROM ccs_lots l, ccs_frameworks f
            WHERE l.wordpress_id = p.id
            AND f.salesforce_id = l.framework_id)
            WHERE post_type='lot'
        EOD;

        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response) {
            print_r($query->errorInfo());
            throw new \Exception('Lot title could not be updated in the database.');
        }
    }

    private function checkAndDeleteLots($lots, $framework) {

        $lotIdsFromAPI = array_map(fn($eachLot) => $eachLot->getSalesforceId(), $lots);
        $localLotId = $this->getLotSalesforceIdByFrameworkId($framework->getSalesforceId());

        $lotsToDelete = array_diff( (array) $localLotId, $lotIdsFromAPI);

        foreach ($lotsToDelete as $lotToDelete){

            $this->logger->info("Deleting lot with SF ID: $lotToDelete from $framework->getRmNumber()");

            $this->lotRepository->delete($lotToDelete);
            $this->deleteLotPostInWordpress($this->getLotWordpressIdBySalesforceId($lotToDelete));
            $this->addSuccess('Lot' . $lotToDelete . ' deleted.');
        }
    }

    private function checkAndDeleteSuppliers($suppliers, $lotId){
        $supplierIdsFromAPI = array_map(fn($eachSupplier) => $eachSupplier->getSalesforceId(), $suppliers);
        $localLotSuppliersId = $this->getLotSuppliersSalesforceIdByLotId($lotId);

        $suppliersToDelete = array_diff( (array) $localLotSuppliersId, $supplierIdsFromAPI);


        foreach ($suppliersToDelete as $supplierToDelete){
            $this->lotSupplierRepository->deleteByLotIdAndSupplierId($lotId, $supplierToDelete);
            $this->addSuccess('Lot supplier ' . $supplierToDelete . ' deleted.');
        }
    }

    private function getLotSalesforceIdByFrameworkId(?string $frameworkId){
        $sql = "SELECT salesforce_id FROM ccs_lots WHERE framework_id = '" . $frameworkId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetchAll(\PDO::PARAM_STR);

        return count($sqlData) == 0 ? null :  array_column($sqlData, 'salesforce_id');
    }

    private function getLotSuppliersSalesforceIdByLotId(?string $lotId) {
        $sql = "SELECT supplier_id FROM ccs_lot_supplier WHERE lot_id = '" . $lotId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetchAll(\PDO::PARAM_STR);

        return count($sqlData) == 0 ? null :  array_column($sqlData, 'supplier_id');
    }

    private function updateFrameworkSearchIndexWithSingleFramework($framework) {
        $lots = $this->lotRepository->findAllById($framework->getSalesforceId(), 'framework_id') ?? [];
        $this->frameworkSearchClient->createOrUpdateDocument($framework, $lots);
    }

    public function updateSupplierSearchIndex() {

        $suppliers = $this->supplierRepository->findAll();
        $count = 0;

        foreach ($suppliers as $supplier) {

            $liveFrameworks = $this->frameworkRepository->findSupplierLiveFrameworks($supplier->getSalesforceId());
            $dpsFrameworkCount = 0;
            $totalFrameworkCount = 0;

            if (!empty($liveFrameworks)) {
                $totalFrameworkCount = count($liveFrameworks);

                foreach ($liveFrameworks as $liveFramework) {
                    $lots = $this->lotRepository->findAllByFrameworkIdSupplierId($liveFramework->getSalesforceId(), $supplier->getSalesforceId());
                    $liveFramework->setLots($lots);

                    if ($liveFramework->getTerms() == 'DPS' || $liveFramework->getType() == 'Dynamic purchasing system' ){
                        $dpsFrameworkCount++;
                    }
                }
            }

            $lotSuppliers = $this->lotSupplierRepository->findAllById($supplier->getSalesforceId(), 'supplier_id');
            $alternativeTradingNames = [];

            if (!empty($lotSuppliers)) {
                foreach ($lotSuppliers as $lotSupplier) {
                    if (!empty($lotSupplier->getTradingName())) {
                        $alternativeTradingNames[] = $lotSupplier->getTradingName();
                    }
                }

                if (!empty($supplier->getTradingName())) {
                    $alternativeTradingNames[] = $supplier->getTradingName();
                }

                $supplier->setAlternativeTradingNames($alternativeTradingNames);
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

        WP_CLI::success('Operation completed successfully.');

        return;
    }

    private function checkAllSuppliersIfOnLiveFrameworks() {
        $suppliers = $this->supplierRepository->findAll();

        foreach ($suppliers as $supplier) {
            $liveFrameworksCount = $this->frameworkRepository->countAllSupplierLiveFrameworks($supplier->getSalesforceId());
            $this->supplierRepository->updateOnLiveField('salesforce_id', $supplier->getSalesforceId(), $liveFrameworksCount > 0);
        }

        return;
    }

    private function addError($message, $type = null) {
        $this->logger->error($message);
        print "$message \n";

        if ($type) {
            $this->errorCount[$type]++;
        }
    }

    private function addErrorAndExit($message) {
        $this->logger->error($message);

        // Passing true to WP_CLI::error exits the script
        WP_CLI::error($message, true);
    }

    private function addSuccess($message, $type = null, $log = false, $break = null) {
        $break ? WP_CLI::line(): null;

        // WP_CLI::success($message . ' Estimated time remaining: ' . $this->timeRemaining . ' minutes.');

        if ($log) {
            $this->logger->info($message);
        }


        if ($type) {
            $this->importCount[$type]++;
        }

    }

    private function printSummary(){
        echo "=====Summary=====\n";
        $this->frameworkRepository->printImportCount();
        $this->lotRepository->printImportCount();
        $this->lotSupplierRepository->printImportCount();
        echo "=================\n";

    }
}


