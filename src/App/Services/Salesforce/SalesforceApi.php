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
        if (!$accessToken)
        {
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

        if ($json == true)
        {
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

        //Build the query for getting all the frameworks
        $sql = <<<EOD
SELECT {$fieldsToReturn} from {$frameworkMappings['objectName']}
WHERE (
  (
      Status__c = 'Future (Pipeline)' OR  Status__c = 'Planned (Pipeline)' OR 
      Status__c = 'Underway (Pipeline)' OR Status__c = 'Awarded (Pipeline)'
  ) 
      AND Don_t_publish_on_website__c = FALSE
  ) 
OR  Don_t_publish_as_Framework_on_website__c = FALSE
EOD;
        // Make API Request
        $response = $this->query($sql);

        $frameworks = [];

        foreach ($response->records as $salesforceRecord)
        {
            $framework = new Framework();
            $framework->setMappedFields($salesforceRecord);
            $frameworks[] = $framework;

        }

        return $frameworks;
    }

    public function getFrameworkLots($salesforceFrameworkId) {
        return $this->getAllLots('Master_Framework__c = \'' . $salesforceFrameworkId . '\'');
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

        foreach ($response->records as $salesforceRecord)
        {
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
        $suppliersToDisplay = $this->query("SELECT Id, Supplier__c from Supplier_Framework_Lot__c WHERE Master_Framework_Lot__c = '" . $lotId . "' AND (Status__c = 'Live' OR Status__c = 'Suspended')");

        $suppliers = [];
        foreach ($suppliersToDisplay->records as $supplierToDisplay)
        {
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
     * @param $lotId
     * @param $supplierId
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getContact($lotId, $supplierId)
    {
        $potentialLotContacts = $this->query("SELECT Id, Contact_Name__c, Email__c, Website_Contact__c, Master_Framework_Lot__c, Supplier_Contact__c from Master_Framework_Lot_Contact__c WHERE Master_Framework_Lot__c = '" . $lotId . "'");

        if ($potentialLotContacts->totalSize == 0)
        {
            // Nothing was found
            return null;
        }


        foreach ($potentialLotContacts->records as $potentialLotContact)
        {
            $contactRecord = $this->query("SELECT Id, AccountId from Contact where Id = '" . $potentialLotContact->Supplier_Contact__c . "'");

            if ($contactRecord->totalSize == 0)
            {
                continue;
            }

            $accountId = $contactRecord->records[0]->AccountId;

            if ($supplierId == $accountId)
            {
                return $potentialLotContact;
            }
        }

        return null;
    }


}
