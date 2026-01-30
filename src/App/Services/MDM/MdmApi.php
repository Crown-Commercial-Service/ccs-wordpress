<?php

declare(strict_types=1);

namespace App\Services\MDM;

use App\Model\Framework;
use App\Model\Lot;
use App\Model\Supplier;

class MdmApi
{
    protected $client;
    protected $baseURL;
    protected $tokenEndpoint;
    protected $clientId;
    protected $clientSecret;
    protected $scope;
    protected $accessToken;
    protected $tokenExpiry;

    public function __construct()
    {
        $this->tokenEndpoint = getenv('MDM_TOKEN_ENDPOINT');
        $this->clientId = getenv('MDM_CLIENT_ID');
        $this->clientSecret = getenv('MDM_CLIENT_SECRET');
        $this->baseURL = getenv('MDM_API_URL');
        $this->scope = getenv('MDM_SCOPE');
        $this->client = new \GuzzleHttp\Client([]);
        $this->accessToken = null;
        $this->tokenExpiry = null;
    }

    public function getAgreementsRmNumbers()
    {
        $filter = "CreateDraftWebPage eq '1' and (
            status eq 'Live' or 
            status eq 'Expired - Data Still Received' or 
            status eq 'Future (Pipeline)' or 
            status eq 'Planned (Pipeline)' or 
            status eq 'Underway (Pipeline)' or 
            status eq 'Awarded (Pipeline)'
            )";
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

    private function getBearerToken()
    {
        if ($this->accessToken && $this->tokenExpiry && time() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        try {
            $response = $this->client->request('POST', $this->tokenEndpoint, [
                'form_params' => [
                    'grant_type'    => 'client_credentials',
                    'client_id'     => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope'         => $this->scope
                ],
            ]);

            $data = json_decode($response->getBody()->getContents(), true);
            $this->accessToken = $data['access_token'];
            $this->tokenExpiry = time() + $data['expires_in'] - 300;

            return $this->accessToken;
        } catch (\Exception $e) {
            throw new \Exception('Failed to obtain bearer token: ' . $e->getMessage());
        }
    }

    private function requestResource(string $resourcePath, array $extraQuery = [])
    {
        $resourcePath = trim($resourcePath, '/') . '/';
        $url = rtrim($this->baseURL, '/') . $resourcePath . '?' . http_build_query($extraQuery);

        $bearerToken = $this->getBearerToken();

        try {
            $response = $this->client->request('GET', $url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $bearerToken,
                ],
            ]);
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
