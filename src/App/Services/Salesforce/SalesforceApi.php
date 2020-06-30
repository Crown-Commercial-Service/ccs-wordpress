<?php

declare(strict_types=1);

namespace App\Services\Salesforce;

use App\Model\Framework;
use App\Model\Lot;
use App\Model\Supplier;
use App\Utils\YamlLoader;
use GuzzleHttp\Client;

/**
 * Class SalesforceApi
 * @package App\Services\Salesforce
 */
class SalesforceApi
{

    /**
     * @var \GuzzleHttp\Client
     */
    protected $client;

    /**
     * @var \GuzzleHttp\Psr7\Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $headers;

    /**
     * SalesforceApi constructor.
     */
    public function __construct()
    {
        $this->initClient();
        $this->setupHeaders();
    }

    /**
     * Set up the client initally
     */
    protected function initClient()
    {
        $this->client = new Client(['base_uri' => getenv('SALESFORCE_INSTANCE_URL') . 'services/data/' . getenv('SALESFORCE_API_VERSION') . '/']);
    }

    /**
     * Configure the required headers
     * @param null $accessToken
     */
    public function setupHeaders($accessToken = null)
    {
        if (!$accessToken) {
            $accessToken = getenv('SALESFORCE_ACCESS_TOKEN');
        }

        $this->headers = [
          'Authorization' => 'Bearer ' . $accessToken
        ];
    }

    public function generateToken()
    {
        $client = new Client(['base_uri' => getenv('SALESFORCE_INSTANCE_URL') . 'services/']);

        $queryParams = [
          'grant_type'    => 'password',
          'client_id'     => getenv('SALESFORCE_CLIENT_ID'),
          'client_secret' => getenv('SALESFORCE_CLIENT_SECRET'),
          'username'      => getenv('SALESFORCE_USERNAME'),
          'password'      => getenv('SALESFORCE_PASSWORD') . getenv('SALESFORCE_SECURITY_TOKEN'),
        ];

        $this->response = $client->request('POST', 'oauth2/token', [
          'query'   => $queryParams,
          'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ]);

        return $this->getResponseContent();
    }

    /**
     * @param string $query
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function query($query)
    {
        $queryParams = [
          'q' => $query,
        ];

        $this->response = $this->client->request('GET', 'query', [
          'query'   => $queryParams,
          'headers' => $this->headers,
        ]);

        return $this->getResponseContent();
    }

    /**
     * @param bool $json
     * @return mixed
     * @throws \Exception
     */
    public function getResponseContent($json = false)
    {
        if ($this->response->getStatusCode() != 200) {
            throw new \Exception('Response has no content. Response status code: ' . $this->response->getStatusCode() . ' Response Error Message: ' . $this->response->getReasonPhrase());
        }

        $contents = $this->response->getBody()->getContents();

        if ($json == true) {
            return $contents;
        }

        return json_decode($contents);
    }

    /**
     * @param $frameworkId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getSingleFramework($frameworkId)
    {
        // Make API Request
        $this->response = $this->client->request('GET', 'sobjects/Master_Framework__c/' . $frameworkId, [
          'headers' => $this->headers,
        ]);

        $framework = new Framework();
        $framework->setMappedFields($this->getResponseContent());

        return $framework;
    }

    /**
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getAllFrameworks()
    {
        $frameworkMappings = YamlLoader::loadMappings('Framework');
        $fieldsToReturn = implode(', ', array_values($frameworkMappings['properties']));

        //Build the query for getting all frameworks
        $sql = <<<EOD
SELECT {$fieldsToReturn} from {$frameworkMappings['objectName']}
WHERE Don_t_publish_on_website__c = FALSE
EOD;
        // Make API Request
        $response = $this->query($sql);

        $frameworks = [];

        foreach ($response->records as $salesforceRecord) {
            $framework = new Framework();
            $framework->setMappedFields($salesforceRecord);
            $frameworks[] = $framework;
        }

        return $frameworks;
    }

    public function getFrameworkLots($salesforceFrameworkId)
    {
        return $this->getAllLots('Master_Framework__c = \'' . $salesforceFrameworkId . '\' AND Master_Framework_Lot_Number__c > \'0\'');
    }

    /**
     * @param null $where
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getAllLots($where = null)
    {
        $frameworkMappings = YamlLoader::loadMappings('Lot');
        $fieldsToReturn = implode(', ', array_values($frameworkMappings['properties']));

        // Make API Request
        $query = 'SELECT ' . $fieldsToReturn . ' from ' . $frameworkMappings['objectName'];
        // Add where query if it exists
        if ($where) {
            $query .= ' WHERE ' . $where;
        }

        $response = $this->query($query);

        $lots = [];

        foreach ($response->records as $salesforceRecord) {
            $lot = new Lot();
            $lot->setMappedFields($salesforceRecord);
            $lots[] = $lot;
        }

        return $lots;
    }

    /**
     * @param $lotId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getLot($lotId)
    {
        $this->response = $this->client->request('GET', 'sobjects/Master_Framework_Lot__c/' . $lotId, [
          'headers' => $this->headers,
        ]);

        $lot = new Lot();
        $lot->setMappedFields($this->getResponseContent());

        return $lot;
    }

    public function getLotSuppliers($lotId)
    {
        $suppliersToDisplay = $this->query("SELECT Id, Supplier__c from Supplier_Framework_Lot__c WHERE Master_Framework_Lot__c = '" . $lotId . "' AND Status__c = 'Live'");

        $suppliers = [];
        foreach ($suppliersToDisplay->records as $supplierToDisplay) {
            $suppliers[] = $this->getSupplier($supplierToDisplay->Supplier__c);
        }

        return $suppliers;
    }


    /**
     * @param $accountId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     */
    public function getSupplier($accountId)
    {
        $this->response = $this->client->request('GET', 'sobjects/Account/' . $accountId, [
          'headers' => $this->headers,
        ]);

        $supplier = new Supplier();
        $supplier->setMappedFields($this->getResponseContent());

        return $supplier;
    }


    /**
     * Get all contacts possible per request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContacts()
    {
        $contacts = $this->query("SELECT Id, AccountId FROM Contact");

        return $contacts;
    }


    /**
     * Get all contacts possible per request
     *
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getMasterFrameworkLotContacts()
    {
        $contacts = $this->query("SELECT Contact_Name__c,Email__c,Master_Framework_Lot__c,Supplier_Contact__c,Website_Contact__c FROM Master_Framework_Lot_Contact__c");

        return $contacts;
    }


    /**
     * Gets the next records using a special internal Id from a paginated list returned from Salesforce
     *
     * @param $nextRecordsId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getNextRecords($nextRecordsId)
    {
        $this->response = $this->client->request('GET', 'query/' . $nextRecordsId, [
          'headers' => $this->headers,
        ]);

        return $this->getResponseContent();
    }

    /**
     * Get the Lot contact details for a supplier
     *
     * Master_Framework_Lot_Contact__c = Linking table, links to a contact and a lot
     * Contact = Supplier contact table
     *
     * @todo Read all entries in Master_Framework_Lot_Contact__c, Contact then loop through this (perhaps in a temporary table?)
     *
     * Suggested SOQL:
     * Table the following (Master Framework Lot Contact):
     * SELECT Email__c,Id,Master_Framework_Lot__c,Organisation_Name__c,Phone__c,Website_Contact__c FROM Master_Framework_Lot_Contact__c
     *
     * Table the following (Master Framework Lot)
     * SELECT Id,Master_Framework__c FROM Master_Framework_Lot__c
     *
     * Table the following (Framework Supplier)
     * SELECT Framework__c,Id,Status__c,Supplier__c,Trading_Name__c FROM Framework_Supplier__c
     *
     * Then use the ID's from each to create a join table which will display on the website.
     *
     * @param $lotId
     * @param $supplierId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContact($lotId, $supplierId)
    {
        $potentialLotContacts = $this->query("SELECT Id, Contact_Name__c, Email__c, Website_Contact__c, Master_Framework_Lot__c, Supplier_Contact__c from Master_Framework_Lot_Contact__c WHERE Master_Framework_Lot__c = '" . $lotId . "'");

        if ($potentialLotContacts->totalSize == 0) {
            // Nothing was found
            return null;
        }


        foreach ($potentialLotContacts->records as $potentialLotContact) {
            $contactRecord = $this->query("SELECT Id, AccountId from Contact where Id = '" . $potentialLotContact->Supplier_Contact__c . "'");

            if ($contactRecord->totalSize == 0) {
                continue;
            }

            $accountId = $contactRecord->records[0]->AccountId;

            if ($supplierId == $accountId) {
                return $potentialLotContact;
            }
        }

        return null;
    }

    /**
     * Select trading name for a supplier on a framework
     *
     * @todo SOQL: SELECT Framework__c, Id, Name, RM_Number__c, Status__c, Supplier__c, Trading_Name__c FROM Framework_Supplier__c
     *
     */
    public function getTradingName($frameworkId, $supplierId)
    {
        $queryResponse = $this->query("SELECT Trading_Name__c FROM Framework_Supplier__c WHERE Framework__c = '" . $frameworkId . "' AND Supplier__c = '" . $supplierId . "' ");

        if ($queryResponse->totalSize == 0) {
            return false;
        }

        if (empty($queryResponse->records[0]->Trading_Name__c)) {
            return false;
        }

        return $accountId = $queryResponse->records[0]->Trading_Name__c;
    }
}
