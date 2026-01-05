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

    public function getAgreementsRmNumbers()
    {
        $filter = "CreateDraftWebPage eq '1' and (status eq 'Expired - Data Still Received' or status eq 'Live')";
        $response = $this->requestResource('[vw_Framework]', ['filter' => $filter]);

        $rmNumbers = [];
        foreach ($response as $apiRecord) {
            $rmNumbers[] = $apiRecord["FrameworkNumber"];
        }

        return $rmNumbers;
    }

    public function getAgreement(string $agreementNumber)
    {

        $filter = "FrameworkNumber eq '$agreementNumber'";
        $response = $this->requestResource('[vw_Framework]', ['filter' => $filter]);

        $framework = new Framework();
        $framework->setData($response[0]);

        return $framework;
    }

    public function getAgreementLots(string $salesforceAgreementId)
    {
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

    public function getLotSuppliers(string $salesforceLotId)
    {
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
