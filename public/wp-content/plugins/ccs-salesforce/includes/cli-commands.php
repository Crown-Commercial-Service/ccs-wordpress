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
     * Import constructor.
     */
    public function __construct()
    {
        $this->logger = new ImportLogger();
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
        $salesforceApi = new SalesforceApi();
        WP_CLI::success('Starting temp data import');

        // Lets generate an access token
        $accessTokenRequest = $salesforceApi->generateToken();
        if (!empty($accessTokenRequest->access_token))
        {
            $accessToken = $accessTokenRequest->access_token;
            $salesforceApi->setupHeaders($accessToken);
        }

        // Get the first batch of contacts
        $contacts = $salesforceApi->getContacts();
        WP_CLI::success(count($contacts->records) . ' contacts returned.');
        $allContactsReturned = $contacts->done;
        $this->saveContactsToTempTable($contacts->records);
        $importCount = count($contacts->records);
        WP_CLI::success($importCount . ' contacts imported.');

        while (!$allContactsReturned) {
            $nextRecordsId = substr($contacts->nextRecordsUrl, strrpos($contacts->nextRecordsUrl, "/") + 1);
            $contacts = $salesforceApi->getNextRecords($nextRecordsId);
            WP_CLI::success(count($contacts->records) . ' contacts returned.');
            $this->saveContactsToTempTable($contacts->records);
            $importCount += count($contacts->records);
            WP_CLI::success($importCount . ' contacts imported.');
            $allContactsReturned = $contacts->done;
        }

        WP_CLI::success('All Contacts saved to temp DB.');


        // Get the first batch of lot contacts
        $contacts = $salesforceApi->getMasterFrameworkLotContacts();
        WP_CLI::success(count($contacts->records) . ' master framework lot contacts returned.');
        $allContactsReturned = $contacts->done;
        $this->saveMasterFrameworkLotContactsToTempTable($contacts->records);
        $importCount = count($contacts->records);
        WP_CLI::success($importCount . ' master framework lot contacts imported.');

        while (!$allContactsReturned) {
            $nextRecordsId = substr($contacts->nextRecordsUrl, strrpos($contacts->nextRecordsUrl, "/") + 1);
            $contacts = $salesforceApi->getNextRecords($nextRecordsId);
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
        $salesforceApi = new SalesforceApi();
        $frameworkRepository = new FrameworkRepository();
        $lotRepository = new LotRepository();
        $supplierRepository = new SupplierRepository();
        $lotSupplierRepository = new LotSupplierRepository();

        WP_CLI::success('Salesforce import started');
        $this->logger->info('Salesforce import started');

        $this->processTempData();

        $this->startTime = microtime(true);

        // Lets generate an access token
        $accessTokenRequest = $salesforceApi->generateToken();
        if (!empty($accessTokenRequest->access_token))
        {
            $accessToken = $accessTokenRequest->access_token;
            $salesforceApi->setupHeaders($accessToken);
        }

        // Get all frameworks from Salesforce
        try {
            $frameworks = $salesforceApi->getAllFrameworks();
        } catch (\Exception $e)
        {
            $this->addError('Error fetching Frameworks from Salesforce. Error: ' . $e->getMessage());
            $this->addError('Process can not complete without Framework data');
            die('Process can not complete without Framework data');
        }

        try {
            $syncText = new SyncText();
            $wordpressFrameworks = $syncText->getFrameworksFromWordPress();
            $wordpressLots = $syncText->getLotsFromWordPress();
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
            $this->timeRemaining = round((($elapsedTime/$index)*count($frameworks))/60, 0);


            try {
                // Save framework to DB (ccs_frameworks)
                $frameworkRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $framework->getSalesforceId(), $framework);
            } catch (\Exception $e) {
                $this->addError('Error saving Framework. Framework ' . $framework->getSalesforceId() . ' not imported. Error: ' . $e->getMessage(), 'frameworks');
                continue;
            }


            try {
                // Read in framework data from DB (ccs_frameworks)
                $framework = $frameworkRepository->findById($framework->getSalesforceId(), 'salesforce_id');
            } catch (\Exception $e) {
                $this->addError('Framework ' . $framework->getSalesforceId() . ' not imported. Error: ' . $e->getMessage(), 'frameworks');
            }

            $this->addSuccess('Framework ' . $index . ' imported. ' . 'SF ID: ' . $framework->getSalesforceId(), 'frameworks');

            // Create or update framework title in WordPress
            try {
                $this->createFrameworkInWordpress($framework, $wordpressFrameworks);
            } catch (\Exception $e)
            {
                $this->addError('Framework ' . $framework->getSalesforceId() . ' not saved to Wordpress. Error: ' . $e->getMessage(), 'frameworks');
            }


            // Read lots for framework for Salesforce
            try {
                $lots = $salesforceApi->getFrameworkLots($framework->getSalesforceId());
            } catch (\Exception $e)
            {
                $this->addError('Error fetching Lots from Salesforce. Error: ' . $e->getMessage(), 'lots');
                continue;
            }


            foreach ($lots as $lot) {
                $lotWordPressId = $this->getLotWordpressIdBySalesforceId($lot->getSalesforceId());
                $lot->setWordpressId($lotWordPressId);

                if (!$lotRepository->createOrUpdateExcludingWordpressFields('salesforce_id', $lot->getSalesforceId(), $lot)) {
                    $this->addError('Lot ' . $lot->getSalesforceId() . ' not imported.', 'lots');
                    continue;
                }
                $lot = $lotRepository->findById($lot->getSalesforceId(), 'salesforce_id');

                $this->addSuccess('Lot ' . $lot->getSalesforceId() . ' imported.', 'lots');

                try {
                    $this->createLotInWordpress($lot, $wordpressLots);
                } catch (\Exception $e)
                {
                    $this->addError('Lot ' . $framework->getSalesforceId() . ' not saved to Wordpress. Error: ' . $e->getMessage(), 'lots');
                }


                //Hide the suppliers on this lot on website
                if($lot->isHideSuppliers()){
                    WP_CLI::success('Hiding suppliers for this Lot.');
                    continue;
                }

                WP_CLI::success('Retrieving Lot Suppliers.');
                $suppliers = $salesforceApi->getLotSuppliers($lot->getSalesforceId());
                WP_CLI::success(count($suppliers) . ' Lot Suppliers found.');

                // Remove all the current relationships to this lot, and create fresh ones.
                WP_CLI::success('Deleting lot suppliers for Lot ID: ' . $lot->getSalesforceId());
                $lotSupplierRepository->deleteById($lot->getSalesforceId(), 'lot_id');

                foreach ($suppliers as $supplier) {
                    if (!$supplierRepository->createOrUpdateExcludingWordpressFields('salesforce_id',
                      $supplier->getSalesforceId(), $supplier)) {
                        WP_CLI::error('Supplier not imported.');
                        $this->logger->info('Supplier ' . $supplier->getSalesforceId() . ' not imported.');
                        $errorCount['suppliers']++;
                        continue;
                    }

                    WP_CLI::success('Supplier imported.'  . ' Estimated time remaining: ' . $this->timeRemaining . ' minutes.');
                    $importCount['suppliers']++;
                    $lotSupplier = new LotSupplier([
                      'lot_id' => $lot->getSalesforceId(),
                      'supplier_id' => $supplier->getSalesforceId()
                    ]);

                    if ($tradingName = $salesforceApi->getTradingName($framework->getSalesforceId(), $supplier->getSalesforceId()))
                    {
                        WP_CLI::success('Framework supplier trading name found.');
                        $lotSupplier->setTradingName($tradingName);
                    }

                    WP_CLI::success('Searching for contact details for Lot: ' . $lotSupplier->getLotId() . ' and Supplier: ' . $lotSupplier->getSupplierId());
                    $contactDetails = $this->findContactDetails($lotSupplier->getLotId(), $lotSupplier->getSupplierId());
                    if ($contactDetails)
                    {
                        WP_CLI::success('Contact details found....');
                        $lotSupplier = $this->addContactDetailsToLotSupplier($lotSupplier, $contactDetails);
                    }

                    $lotSupplierRepository->create($lotSupplier);
                }

            }
        }

        //Mark whether a supplier has any live frameworks
        $this->checkSupplierLiveFrameworks();

        $timer = round(microtime(true) - $this->startTime, 2);
        WP_CLI::success(sprintf('Import took %s seconds to run', $timer));

        $response = [
          'importCount' => $importCount,
          'errorCount'  => $errorCount
        ];

        $this->logger->info('Import complete. Import took ' . $timer/60 . ' minutes to complete.', $response);

        return $response;
    }



    protected function findContactDetails($lotId, $supplierId) {

        $dbConnection = new DatabaseConnection();

        $sql = "SELECT * FROM temp_master_framework_lot_contact WHERE master_framework_lot_salesforce_id = '" . $lotId . "';";
        $query = $dbConnection->connection->prepare($sql);
        $query->execute();

        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return false;
        }

        foreach ($results as $result)
        {
            $sql = "SELECT * FROM temp_contact WHERE salesforce_id = '" . $result['supplier_contact_salesforce_id'] . "';";

            $query = $dbConnection->connection->prepare($sql);
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
     * @param $type framework, lot, supplier
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
     * @param $type framework, lot, supplier
     */
    protected function addSuccess($message, $type = null)
    {
        WP_CLI::success($message . ' Estimated time remaining: ' . $this->timeRemaining . ' minutes.');
        $this->logger->info($message);

        if ($type)
        {
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
        $frameworkRepository = new FrameworkRepository();
        $frameworkRepository->update('salesforce_id', $framework->getSalesforceId(), $framework);
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
        $lotRepository = new LotRepository();
        $lotRepository->update('salesforce_id', $lot->getSalesforceId(), $lot);
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

        $frameworkRepository = new FrameworkRepository();
        $supplierRepository = new SupplierRepository();

        $suppliers = $supplierRepository->findAll();

        WP_CLI::success(count($suppliers) . ' Suppliers found');

        foreach ($suppliers as $supplier) {

            $liveFrameworksCount = $frameworkRepository->countAllSupplierLiveFrameworks($supplier->getSalesforceId());

            if ($liveFrameworksCount > 0) {
                //Update the Supplier model with the flag true for live frameworks
                $supplier->setOnLiveFrameworks(true);

                // Save the Supplier back into the custom database.
                $supplierRepository->update('salesforce_id', $supplier->getSalesforceId(), $supplier);
            }
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
        $dbConnection = new DatabaseConnection();

        $sql = "TRUNCATE TABLE temp_contact;";
        $query = $dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response)
        {
            throw new \Exception('Temp table temp_contact could not be truncated.');
        }

        $sql = "TRUNCATE TABLE temp_master_framework_lot_contact;";
        $query = $dbConnection->connection->prepare($sql);
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
        $dbConnection = new DatabaseConnection();

        foreach ($contacts as $contact)
        {
            $sql = "INSERT INTO temp_contact (salesforce_id, account_id) VALUES (:id, :accountId);";

            $query = $dbConnection->connection->prepare($sql);

            $query->bindParam(':id', $contact->Id, \PDO::PARAM_STR);
            $query->bindParam(':accountId', $contact->AccountId, \PDO::PARAM_STR);

            $response = $query->execute();

            if (!$response)
            {
                throw new \Exception('Data could not be saved to database correctly.');
            }

        }
    }



    public function getLotWordpressIdBySalesforceId(?string $salesforceId)
    {
        $lotWordpressId = null;

        $sql = "SELECT wordpress_id FROM ccs_lots WHERE salesforce_id = '" . $salesforceId . "';";

        $dbConnection = new DatabaseConnection();
        $query = $dbConnection->connection->prepare($sql);
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
        $dbConnection = new DatabaseConnection();

        foreach ($contacts as $contact)
        {
            $sql = "INSERT INTO temp_master_framework_lot_contact (contact_name, contact_email, website_contact, master_framework_lot_salesforce_id, supplier_contact_salesforce_id) VALUES (:contactName, :contactEmail, :websiteContact, :mflsId, :scsId);";

            $query = $dbConnection->connection->prepare($sql);

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

    protected function updateTimeRemaining()
    {

    }
}


