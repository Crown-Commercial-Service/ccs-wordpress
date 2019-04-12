<?php
/**
 * Salesforce importer
 *
 * Example commands:
 * wp cli salesforce import sync-text-from-wordpress
 *
 * @see https://make.wordpress.org/cli/handbook/commands-cookbook/
 *
 */

namespace CCS\SFI;

use App\Services\Database\DatabaseConnection;
use App\Services\Logger\ImportLogger;
use \WP_CLI;

use App\Model\LotSupplier;
use App\Repository\FrameworkRepository;
use App\Repository\LotRepository;
use App\Repository\LotSupplierRepository;
use App\Repository\SupplierRepository;
use App\Services\Salesforce\SalesforceApi;

WP_CLI::add_command('salesforce import', 'CCS\SFI\Import');

/**
 * Class Import
 * @package CCS\SFI
 */
class Import
{

    /**
     * @var \App\Services\Logger\ImportLogger
     */
    protected $logger;

    /**
     * Import time remaining
     *
     * @var int
     */
    protected $timeRemaining = 0;

    /**
     * @var int
     */
    protected $startTime = 0;

    /**
     * @var array
     */
    protected $importCount = [
      'frameworks' => 0,
      'lots'       => 0,
      'suppliers'  => 0
    ];

    /**
     * @var array
     */
    protected $errorCount = [
      'frameworks' => 0,
      'lots'       => 0,
      'suppliers'  => 0
    ];


    /**
     * @var \App\Services\Salesforce\SalesforceApi
     */
    protected $salesforceApi;

    /**
     * @var \App\Repository\FrameworkRepository
     */
    protected $frameworkRepository;

    /**
     * @var \App\Repository\LotRepository
     */
    protected $lotRepository;

    /**
     * @var \App\Repository\SupplierRepository
     */
    protected $supplierRepository;

    /**
     * @var \App\Repository\LotSupplierRepository
     */
    protected $lotSupplierRepository;

    /**
     * @var \App\Services\Database\DatabaseConnection
     */
    protected $dbConnection;

    /**
     * @var array
     */
    protected $wordpressFrameworks;

    /**
     * @var array
     */
    protected $wordpressLots;


    /**
     * Import constructor.
     */
    public function __construct()
    {
        $this->logger = new ImportLogger();
        $this->salesforceApi = new SalesforceApi();
        $this->frameworkRepository = new FrameworkRepository();
        $this->lotRepository = new LotRepository();
        $this->supplierRepository = new SupplierRepository();
        $this->lotSupplierRepository = new LotSupplierRepository();
        $this->dbConnection = new DatabaseConnection();
    }

    /**
     * Fetches latest contact data from Salesforce and places it in a temporary database.
     *
     *       wp salesforce import tempData
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function processTempData()
    {
        $this->logger->info('Temp data refreshing');

        WP_CLI::success('Truncating temp tables');
        $this->truncateTempTables();

        $this->startTime = microtime(true);
        WP_CLI::success('Starting temp data import');

        // Get the first batch of contacts
        $contacts = $this->salesforceApi->getContacts();
        WP_CLI::success(count($contacts->records) . ' contacts returned.');
        $allContactsReturned = $contacts->done;
        $this->saveContactsToTempTable($contacts->records);
        $importCount = count($contacts->records);
        WP_CLI::success($importCount . ' contacts imported.');

        while (!$allContactsReturned) {
            $nextRecordsId = substr($contacts->nextRecordsUrl, strrpos($contacts->nextRecordsUrl, "/") + 1);
            $contacts = $this->salesforceApi->getNextRecords($nextRecordsId);
            WP_CLI::success(count($contacts->records) . ' contacts returned.');
            $this->saveContactsToTempTable($contacts->records);
            $importCount += count($contacts->records);
            WP_CLI::success($importCount . ' contacts imported.');
            $allContactsReturned = $contacts->done;
        }

        WP_CLI::success('All Contacts saved to temp DB.');


        // Get the first batch of lot contacts
        $contacts = $this->salesforceApi->getMasterFrameworkLotContacts();
        WP_CLI::success(count($contacts->records) . ' master framework lot contacts returned.');
        $allContactsReturned = $contacts->done;
        $this->saveMasterFrameworkLotContactsToTempTable($contacts->records);
        $importCount = count($contacts->records);
        WP_CLI::success($importCount . ' master framework lot contacts imported.');

        while (!$allContactsReturned) {
            $nextRecordsId = substr($contacts->nextRecordsUrl, strrpos($contacts->nextRecordsUrl, "/") + 1);
            $contacts = $this->salesforceApi->getNextRecords($nextRecordsId);
            WP_CLI::success(count($contacts->records) . ' master framework lot contacts returned.');
            $this->saveMasterFrameworkLotContactsToTempTable($contacts->records);
            $importCount += count($contacts->records);
            WP_CLI::success($importCount . ' master framework lot contacts imported.');
            $allContactsReturned = $contacts->done;
        }

        WP_CLI::success('All master framework lot contacts saved to temp DB.');

        $timer = round(microtime(true) - $this->startTime, 2);
        WP_CLI::success(sprintf('Import took %s seconds to run', $timer));

        $this->logger->info('Temp data refresh complete.');
    }

    /**
     * Script command to import a simple framework.
     * Usage: wp salesforce import single 1asdE123f45daSFqwDF
     * - Where asdE123f45daSFqwDF is the Salesforce Framework ID
     *
     * @param $args
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function single($args)
    {
        $this->addSuccess('Salesforce single Framework import started', null, true);

        // Lets hardcode this on average, it's correct.
        $this->timeRemaining = 'Less than 2';

        $frameworkId = $args[0];

        // Lets generate an access token
        $this->generateSalesforceToken();

        // Get all frameworks from Salesforce
        try {
            $framework = $this->salesforceApi->getSingleFramework($frameworkId);
            $this->addSuccess('Framework successfully returned from Salesforce', null, true);
        } catch (\Exception $e)
        {
            $this->addError('Error fetching Framework ID: ' . $frameworkId . ' from Salesforce. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework data');
            die('Process can not complete without Framework data');
        }


        try {
            $syncText = new SyncText();
            $this->wordpressFrameworks = $syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $syncText->getLotsFromWordPress();
        } catch (\Exception $e)
        {
            $this->addError('Error fetching existing frameworks and lots from Wordpress. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework and Lot data from Wordpress');
            die('Process can not complete without Framework and Lot data from Wordpress');
        }

        // Import this Framework
        $this->importSingleFramework($framework);

        //Mark whether a supplier has any live frameworks
        $this->checkSupplierLiveFrameworks();

        $response = [
          'importCount' => $this->importCount,
          'errorCount'  => $this->errorCount
        ];

        $this->logger->info('Import complete.', $response);

        return $response;

    }

    /**
     * Imports Salesforce objects into Wordpress database
     *
     * ## EXAMPLES
     *
     *     wp salesforce import frameworks
     *
     * @when after_wp_load
     */

    public function all()
    {
        $this->addSuccess('Salesforce import started', null, true);

        // Lets generate an access token
        $this->generateSalesforceToken();

        $this->processTempData();
        $this->startTime = microtime(true);

        // Get all frameworks from Salesforce
        try {
            $frameworks = $this->salesforceApi->getAllFrameworks();
            $this->addSuccess(count($frameworks) . ' Frameworks successfully returned from Salesforce', null, true);
        } catch (\Exception $e)
        {
            $this->addError('Error fetching Frameworks from Salesforce. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework data');
            die('Process can not complete without Framework data');
        }

        try {
            $syncText = new SyncText();
            $this->wordpressFrameworks = $syncText->getFrameworksFromWordPress();
            $this->wordpressLots = $syncText->getLotsFromWordPress();
        } catch (\Exception $e)
        {
            $this->addError('Error fetching existing frameworks and lots from Wordpress. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework and Lot data from Wordpress');
            die('Process can not complete without Framework and Lot data from Wordpress');
        }


        foreach ($frameworks as $index => $framework) {
            // How much time has elapsed
            $elapsedTime = round(microtime(true) - $this->startTime, 2);
            // What is the estimated remaining time in minutes.
            $this->timeRemaining = round((($elapsedTime/$index)*count($frameworks)-$index)/60, 0);

            $this->importSingleFramework($framework);
        }


        //Mark whether a supplier has any live frameworks
        $this->checkSupplierLiveFrameworks();

        //Update framework titles in WordPress to include the RM number
        $this->updateFrameworkTitleInWordpress();

        //Update lot titles in WordPress to include the RM number and the lot number
        $this->updateLotTitleInWordpress();

        $timer = round(microtime(true) - $this->startTime, 2);
        WP_CLI::success(sprintf('Import took %s seconds to run', $timer));

        $response = [
          'importCount' => $this->importCount,
          'errorCount'  => $this->errorCount
        ];

        $this->logger->info('Import complete. Import took ' . $timer/60 . ' minutes to complete.', $response);

        return $response;
    }


    /**
     * Import a single framework
     *
     * @param $framework
     */
    protected function importSingleFramework($framework)
    {
        try {
            // Save framework to DB (ccs_frameworks)
            $this->frameworkRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $framework->getSalesforceId(), $framework);
        } catch (\Exception $e) {
            $this->addError('Error saving Framework. Framework ' . $framework->getSalesforceId() . ' not imported. Error: ' . $e->getMessage(), 'frameworks');
            return;
        }


        try {
            // Read in framework data from DB (ccs_frameworks)
            $salesforceId = $framework->getSalesforceId();
            if (!$framework = $this->frameworkRepository->findById($salesforceId, 'salesforce_id'))
            {
                $this->addError('Framework ID: ' . $salesforceId . ' not found.');
                return;
            }
        } catch (\Exception $e) {
            $this->addError('Framework ' . $framework->getSalesforceId() . ' not imported. Error: ' . $e->getMessage(), 'frameworks');
        }

        $this->addSuccess('Framework imported. ' . 'SF ID: ' . $framework->getSalesforceId(),
          'frameworks');

        // Create or update framework title in WordPress
        try {
            $this->createFrameworkInWordpress($framework, $this->wordpressFrameworks);
        } catch (\Exception $e) {
            $this->addError('Framework ' . $framework->getSalesforceId() . ' not saved to Wordpress. Error: ' . $e->getMessage(),
              'frameworks');
        }


        // Read lots for framework for Salesforce
        try {
            $lots = $this->salesforceApi->getFrameworkLots($framework->getSalesforceId());
        } catch (\Exception $e) {
            $this->addError('Error fetching Lots from Salesforce. Error: ' . $e->getMessage(), 'lots');
            return;
        }


        foreach ($lots as $lot) {
            $lotWordPressId = $this->getLotWordpressIdBySalesforceId($lot->getSalesforceId());
            $lot->setWordpressId($lotWordPressId);

            if (!$this->lotRepository->createOrUpdateExcludingWordpressFields('salesforce_id',
              $lot->getSalesforceId(), $lot)) {
                $this->addError('Lot ' . $lot->getSalesforceId() . ' not imported.', 'lots');
                continue;
            }
            $lot = $this->lotRepository->findById($lot->getSalesforceId(), 'salesforce_id');

            $this->addSuccess('Lot ' . $lot->getSalesforceId() . ' imported.', 'lots');

            try {
                $this->createLotInWordpress($lot, $this->wordpressLots);
            } catch (\Exception $e) {
                $this->addError('Lot ' . $framework->getSalesforceId() . ' not saved to Wordpress. Error: ' . $e->getMessage(), 'lots');
            }


            //Hide the suppliers on this lot on website
            if ($lot->isHideSuppliers()) {
                $this->addSuccess('Hiding suppliers for this Lot.');
                continue;
            }


            $this->addSuccess('Retrieving Lot Suppliers.');
            try {
                $suppliers = $this->salesforceApi->getLotSuppliers($lot->getSalesforceId());
                $this->addSuccess(count($suppliers) . ' Lot Suppliers found.');
            } catch (\Exception $e) {
                $this->addError('Lot Suppliers for Lot ' . $lot->getSalesforceId() . ' not saved to Wordpress. Error: ' . $e->getMessage(), 'suppliers');
                continue;
            }


            // Remove all the current relationships to this lot, and create fresh ones.
            $this->addSuccess('Deleting lot suppliers for Lot ID: ' . $lot->getSalesforceId());
            $this->lotSupplierRepository->deleteById($lot->getSalesforceId(), 'lot_id');

            foreach ($suppliers as $supplier) {
                if (!$this->supplierRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $supplier->getSalesforceId(), $supplier)) {
                        $this->addError('Supplier ' . $supplier->getSalesforceId() . ' not imported. Error: ' . $e->getMessage(), 'suppliers');
                        continue;
                    }
                    $this->addSuccess('Supplier ' . $supplier->getSalesforceId() . ' imported.', 'suppliers');

                $lotSupplier = new LotSupplier([
                  'lot_id'      => $lot->getSalesforceId(),
                  'supplier_id' => $supplier->getSalesforceId()
                ]);


                if ($tradingName = $this->salesforceApi->getTradingName($framework->getSalesforceId(),
                  $supplier->getSalesforceId())) {
                    $this->addSuccess('Framework supplier trading name found.');
                    $lotSupplier->setTradingName($tradingName);
                }

                $this->addSuccess('Searching for contact details for Lot: ' . $lotSupplier->getLotId() . ' and Supplier: ' . $lotSupplier->getSupplierId());

                try {
                    $contactDetails = $this->findContactDetails($lotSupplier->getLotId(),
                      $lotSupplier->getSupplierId());
                    if ($contactDetails) {
                        $this->addSuccess('Contact details found....');
                        $lotSupplier = $this->addContactDetailsToLotSupplier($lotSupplier, $contactDetails);
                    }
                } catch (\Exception $e) {
                    $this->addError('Supplier contact details for Lot ' . $lotSupplier->getLotId() . ' and Supplier ' . $lotSupplier->getSupplierId() . ' not found. Error: ' . $e->getMessage(), 'suppliers');
                }


                try {
                    $this->lotSupplierRepository->create($lotSupplier);
                } catch (\Exception $e) {
                    $this->addError('Error saving Lot Supplier for Lot ' . $lotSupplier->getLotId() . ' and Supplier ' . $lotSupplier->getSupplierId() . ' Error: ' . $e->getMessage(), 'suppliers');
                }

            }

        }
    }

    /**
     * @param $lotId
     * @param $supplierId
     * @return bool
     */
    protected function findContactDetails($lotId, $supplierId) {

        $this->dbConnection = new DatabaseConnection();

        $sql = "SELECT * FROM temp_master_framework_lot_contact WHERE website_contact = 1 AND master_framework_lot_salesforce_id = '" . $lotId . "';";
        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return false;
        }

        foreach ($results as $result)
        {
            $sql = "SELECT * FROM temp_contact WHERE salesforce_id = '" . $result['supplier_contact_salesforce_id'] . "';";

            $query = $this->dbConnection->connection->prepare($sql);
            $query->execute();

            $contactResult = $query->fetch(\PDO::FETCH_ASSOC);

            if (empty($contactResult))
            {
                continue;
            }

            if ($supplierId == $contactResult['account_id'])
            {
                return $result;
            }
        }

        return false;
    }

    /**
     * @param $message the error message to report
     * @param $type frameworks, lots, suppliers
     */
    protected function addError($message, $type = null)
    {
        WP_CLI::error($message . ' Estimated time remaining: ' . $this->timeRemaining . ' minutes.');
        $this->logger->error($message);

        if ($type)
        {
            $this->errorCount[$type]++;
        }

    }

    /**
     * @param $message the error message to report
     * @param $type frameworks, lots, suppliers
     * @param bool $log
     */
    protected function addSuccess($message, $type = null, $log = false)
    {
        WP_CLI::success($message . ' Estimated time remaining: ' . $this->timeRemaining . ' minutes.');

        if ($log) {
            $this->logger->info($message);
        }


        if ($type) {
            $this->importCount[$type]++;
        }

    }


    /**
     * Syncs frameworks & lots rich text from WordPress to custom ccs_ tables
     *
     * Useful to use after the initial import from Drupal 7 to WP
     *
     * Usage:
     * wp salesforce import syncText
     */
    public function syncText()
    {
        $sync = new SyncText();

        // Sync content for frameworks
        $wordpress = $sync->getFrameworksFromWordPress();
        WP_CLI::success(sprintf('Read in %d frameworks from WordPress', count($wordpress)));
        $custom = $sync->getFrameworksFromCustomTables();
        WP_CLI::success(sprintf('Read in %d frameworks from custom database table', count($custom)));
        $results = $sync->syncTextContent('frameworks', $wordpress, $custom);
        WP_CLI::success(sprintf('Text content for %d frameworks synced from WordPress to custom table', $results));

        // Sync content for lots
        $wordpress = $sync->getLotsFromWordPress();
        WP_CLI::success(sprintf('Read in %d lots from WordPress', count($wordpress)));
        $custom = $sync->getLotsFromCustomTables();
        WP_CLI::success(sprintf('Read in %d lots from custom database table', count($custom)));
        $results = $sync->syncTextContent('lots', $wordpress, $custom);
        WP_CLI::success(sprintf('Text content for %d lots synced from WordPress to custom table', $results));
    }


    /**
     * @param \App\Model\LotSupplier $lotSupplier
     * @param $contactDetails
     * @return \App\Model\LotSupplier
     */
    protected function addContactDetailsToLotSupplier(LotSupplier $lotSupplier, $contactDetails) {

        if (isset($contactDetails['contact_name'])) {
            $lotSupplier->setContactName($contactDetails['contact_name']);
        }

        if (isset($contactDetails['contact_email'])) {
            $lotSupplier->setContactEmail($contactDetails['contact_email']);
        }

        if (isset($contactDetails['website_contact'])) {
            $lotSupplier->setWebsiteContact($contactDetails['website_contact']);
        }

        return $lotSupplier;
    }


    /**
     * Determine if we need to create a new 'Framework' post in Wordpress, then (if we do) - create one.
     *
     * @param $framework
     * @param array $wordpressFrameworks
     */
    protected function createFrameworkInWordpress($framework, array $wordpressFrameworks)
    {
        if (!empty($framework->getWordpressId()))
        {
            return;
        }

        $wordpressId = $this->createFrameworkPostInWordpress($framework);
        WP_CLI::success('Created Framework in Wordpress.');

        //Update the Framework model with the new Wordpress ID
        $framework->setWordpressId($wordpressId);

        // Save the Framework back into the custom database.
        $this->frameworkRepository = new FrameworkRepository();
        $this->frameworkRepository->update('salesforce_id', $framework->getSalesforceId(), $framework);
    }

    /**
     * Determine if we need to create a new 'Lot' post in Wordpress, then (if we do) - create one.
     *
     * @param $lot
     * @param array $wordpressLots
     */
    protected function createLotInWordpress($lot, array $wordpressLots)
    {
        if (!empty($lot->getWordpressId()))
        {
            return;
        }

        $wordpressId = $this->createLotPostInWordpress($lot);
        WP_CLI::success('Created Lot in Wordpress.');

        //Update the Lot model with the new Wordpress ID
        $lot->setWordpressId($wordpressId);

        // Save the Lot back into the custom database.
        $this->lotRepository = new LotRepository();
        $this->lotRepository->update('salesforce_id', $lot->getSalesforceId(), $lot);
    }

    /**
     * Insert a new Framework post in to Wordpress
     *
     * @param $framework
     * @return int|\WP_Error
     */
    public function createFrameworkPostInWordpress($framework)
    {
        // Create a new post
        $wordpressId = wp_insert_post(array(
            'post_title' => $framework->getTitle(),
            'post_type' => 'framework'
        ));


        //Save the salesforce id in Wordpress
        update_field('framework_id', $framework->getSalesforceId(), $wordpressId);


        return $wordpressId;
    }

    /**
     * Insert a new Lot post in to Wordpress
     *
     * @param $lot
     * @return int|\WP_Error
     */
    public function createLotPostInWordpress($lot)
    {
        // Create a new post
        $wordpressId = wp_insert_post(array(
            'post_title' => $lot->getTitle(),
            'post_type' => 'lot'
        ));

        return $wordpressId;
    }


    /**
     * Check if a supplier has any live frameworks
     *
     */
    public function checkSupplierLiveFrameworks() {

        WP_CLI::success('Supplier check in progress to determine Live Framework status');

        $this->frameworkRepository = new FrameworkRepository();
        $this->supplierRepository = new SupplierRepository();

        $suppliers = $this->supplierRepository->findAll();

        WP_CLI::success(count($suppliers) . ' Suppliers found');

        foreach ($suppliers as $supplier) {

            $liveFrameworksCount = $this->frameworkRepository->countAllSupplierLiveFrameworks($supplier->getSalesforceId());

            if ($liveFrameworksCount > 0) {
                //Update the Supplier model with the flag true for live frameworks
                $supplier->setOnLiveFrameworks(true);
            } else {
                //Update the Supplier model with the flag false
                $supplier->setOnLiveFrameworks(false);
            }

                // Save the Supplier back into the custom database.
                $this->supplierRepository->update('salesforce_id', $supplier->getSalesforceId(), $supplier);
            }

        return;
    }


    /**
     * Saves all contacts to a temporary table.
     *
     * @throws \Exception
     */
    public function truncateTempTables()
    {
        $this->dbConnection = new DatabaseConnection();

        $sql = "TRUNCATE TABLE temp_contact;";
        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response)
        {
            throw new \Exception('Temp table temp_contact could not be truncated.');
        }

        $sql = "TRUNCATE TABLE temp_master_framework_lot_contact;";
        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response)
        {
            throw new \Exception('Temp table temp_contact could not be truncated.');
        }
    }


    /**
     * Saves all contacts to a temporary table.
     *
     * @param $contacts
     * @throws \Exception
     */
    public function saveContactsToTempTable($contacts)
    {
        $this->dbConnection = new DatabaseConnection();

        foreach ($contacts as $contact)
        {
            $sql = "INSERT INTO temp_contact (salesforce_id, account_id) VALUES (:id, :accountId);";

            $query = $this->dbConnection->connection->prepare($sql);

            $query->bindParam(':id', $contact->Id, \PDO::PARAM_STR);
            $query->bindParam(':accountId', $contact->AccountId, \PDO::PARAM_STR);

            $response = $query->execute();

            if (!$response)
            {
                throw new \Exception('Data could not be saved to database correctly.');
            }

        }
    }


    /**
     * @param null|string $salesforceId
     * @return null
     */
    public function getLotWordpressIdBySalesforceId(?string $salesforceId)
    {
        $lotWordpressId = null;

        $sql = "SELECT wordpress_id FROM ccs_lots WHERE salesforce_id = '" . $salesforceId . "';";

        $this->dbConnection = new DatabaseConnection();
        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetch(\PDO::FETCH_ASSOC);

        if(!empty($sqlData['wordpress_id'])) {
            $lotWordpressId = $sqlData['wordpress_id'];
        }

        return $lotWordpressId;
    }



    /**
     * Saves all contacts to a temporary table.
     *
     * @param $contacts
     * @throws \Exception
     */
    public function saveMasterFrameworkLotContactsToTempTable($contacts)
    {


        foreach ($contacts as $contact)
        {
            $sql = "INSERT INTO temp_master_framework_lot_contact (contact_name, contact_email, website_contact, master_framework_lot_salesforce_id, supplier_contact_salesforce_id) VALUES (:contactName, :contactEmail, :websiteContact, :mflsId, :scsId);";

            $query = $this->dbConnection->connection->prepare($sql);

            $query->bindParam(':contactName', $contact->Contact_Name__c, \PDO::PARAM_STR);
            $query->bindParam(':contactEmail', $contact->Email__c, \PDO::PARAM_STR);
            $query->bindParam(':websiteContact', $contact->Website_Contact__c, \PDO::PARAM_INT);
            $query->bindParam(':mflsId', $contact->Master_Framework_Lot__c, \PDO::PARAM_STR);
            $query->bindParam(':scsId', $contact->Supplier_Contact__c, \PDO::PARAM_STR);

            $response = $query->execute();

            if (!$response)
            {
                print_r($query->errorInfo());
                throw new \Exception('Data could not be saved to database correctly.');
            }

        }
    }

    /**
     * Generate salesforce token to use in this request
     */
    protected function generateSalesforceToken()
    {
        $accessTokenRequest = $this->salesforceApi->generateToken();
        if (!empty($accessTokenRequest->access_token))
        {
            $accessToken = $accessTokenRequest->access_token;
            $this->salesforceApi->setupHeaders($accessToken);
        }
    }


    /**
     * Updates all framework titles in WordPress to include the RM Number
     *
     * @throws \Exception
     */
    public function updateFrameworkTitleInWordpress()
    {

        $dbConnection = new DatabaseConnection();

        $sql = <<<EOD
UPDATE ccs_15423_posts p 
INNER JOIN ccs_frameworks f
ON f.wordpress_id = p.id
SET p.post_title = (SELECT CONCAT(f.rm_number,': ', f.title) FROM ccs_frameworks f WHERE f.wordpress_id = p.id)
WHERE p.post_type='framework'
EOD;
        $query = $dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response)
        {
            print_r($query->errorInfo());
            throw new \Exception('Framework title could not be updated in the database.');
        }

    }

    /**
     * Updates all lot titles in WordPress to include the RM Number and the lot number
     *
     * @throws \Exception
     */
    public function updateLotTitleInWordpress()
    {

        $dbConnection = new DatabaseConnection();

        $sql = <<<EOD
UPDATE ccs_15423_posts p SET p.post_title = 
(SELECT CONCAT(f.rm_number, ' Lot ', l.lot_number, ': ', l.title)
FROM ccs_lots l, ccs_frameworks f
WHERE l.wordpress_id = p.id
AND f.salesforce_id = l.framework_id)
WHERE post_type='lot'
EOD;
        $query = $dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response)
        {
            print_r($query->errorInfo());
            throw new \Exception('Lot title could not be updated in the database.');
        }
    }

}


