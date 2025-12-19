<?php
declare(strict_types=1);

namespace App\Services\MDM;

use App\Model\Framework;
use App\Model\Lot;
use App\Model\Supplier;

class MdmApi
{
    protected $baseURL;
    protected $apiKey;
    protected $client;

    public function __construct()
    {
        $this->apiKey = getenv('MDM_API_Key');
        $this->baseURL = getenv('MDM_API_URL');
        $this->client = new \GuzzleHttp\Client([]);
    }
    
    // public function getAgreements(){
        
    //     $url =  "https://prod-43.uksouth.logic.azure.com/workflows/7559aad54efd4b8fa422d811359ae08f/triggers/manual/paths/invoke/[web].[vw_Framework]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=$this->apiKey&filter=CreateDraftWebPage%20eq%20%271%27%20and%20(status%20eq%20%27Expired%20-%20Data%20Still%20Received%27%20or%20status%20eq%20%27Live%27)";

    //     $this->response = $this->client->request('GET', $url);

    //     $framework = new Framework();
    //     $framework->setData($this->getResponseContent()[0]);

    //     return $framework;
    // }

    public function getAgreement(string $agreementNumber){

        $filter = "FrameworkNumber eq '$agreementNumber'";
        $response = $this->requestResource('[vw_Framework]', ['filter' => $filter]);

        $framework = new Framework();
        $framework->setData($response[0]);

        return $framework;
    }

    public function getAgreementLots(string $salesforceAgreementId){
        $filter = "FrameworkSalesforceID eq '$salesforceAgreementId'";
        $response = $this->requestResource('[vw_FrameworkLots]', ['filter' => $filter]);

        $lots = [];

        foreach ($response as $apiRecord) {
            $lot = new Lot();
            $lot->setData($apiRecord);
            $lots[] = $lot;
        }

        return $lots;
    }

    public function getLotSuppliers(string $salesforceLotId){
        $filter = "FrameworkLotSalesforceID eq '$salesforceLotId'";
        $response = $this->requestResource('[vw_FrameworkLotSupplierContacts]', ['filter' => $filter]);

        $suppliers = [];

        foreach ($response as $apiRecord) {
            $supplier = new Supplier();
            $supplier->setData($apiRecord);
            $suppliers[] = $supplier;
        }

        return $suppliers;
    }

    private function requestResource(string $resourcePath, array $extraQuery = [])
        {
            $defaultQuery = [
                'api-version' => '2016-10-01',
                'sp'          => '/triggers/manual/run',
                'sv'          => '1.0',
                'sig'         => $this->apiKey,
            ];

            $resourcePath = trim($resourcePath, '/') . '/';
            $query = array_merge($defaultQuery, $extraQuery);
            $url = rtrim($this->baseURL, '/') . $resourcePath . '?' . http_build_query($query);

        try {
            $response = $this->client->request('GET', $url);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $resp = $e->getResponse();
            if ($resp && $resp->getStatusCode() === 400) {
                return [];
            }
            throw $e;
        }


            if ($response->getStatusCode() != 200) {
                throw new \Exception('Response has no content. Response status code: ' . $response->getStatusCode() . ' Response Error Message: ' . $response->getReasonPhrase());
            }

            $contents = $response->getBody()->getContents();

            return json_decode($contents, true);
        }
}
