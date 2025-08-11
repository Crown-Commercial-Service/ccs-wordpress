<?php
declare(strict_types=1);

namespace App\Services\MDM;

use App\Model\Framework;
use App\Model\Lot;
use App\Model\Supplier;

class MdmApi
{
    protected $apiKey;
    protected $client;
    protected $response;

    public function __construct()
    {
        $this->apiKey = getenv('MDM_API_Key');
        $this->client = new \GuzzleHttp\Client([
            'base_uri' => getenv('MDM_API_URL')
        ]);
    }

    public function getAgreement(string $agreementNumber){
        $url =  "https://prod-43.uksouth.logic.azure.com/workflows/55e2c6c78b71475cac35395953b2ee9a/triggers/manual/paths/invoke/[web].[vw_Framework]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=$this->apiKey&filter=FrameworkNumber eq '$agreementNumber'";

        $this->response = $this->client->request('GET', $url);

        $framework = new Framework();
        $framework->setData($this->getResponseContent()[0]);

        return $framework;
    }

    public function getAgreementLots(string $salesforceAgreementId){
        $url =  "https://prod-43.uksouth.logic.azure.com/workflows/55e2c6c78b71475cac35395953b2ee9a/triggers/manual/paths/invoke/[web].[vw_FrameworkLots]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=$this->apiKey&filter=FrameworkSalesforceID eq '$salesforceAgreementId'";

        $this->response = $this->client->request('GET', $url);

        $lots = [];

        foreach ($this->getResponseContent() as $apiRecord) {
            $lot = new Lot();
            $lot->setData($apiRecord);
            $lots[] = $lot;
        }

        return $lots;
    }

    public function getLotSuppliers(string $salesforceLotId){
        $url =  "https://prod-43.uksouth.logic.azure.com/workflows/55e2c6c78b71475cac35395953b2ee9a/triggers/manual/paths/invoke/[web].[vw_FrameworkLotSupplierContacts]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig=$this->apiKey&filter=FrameworkLotSalesforceID eq '$salesforceLotId'";

        $this->response = $this->client->request('GET', $url);

        $suppliers = [];

        foreach ($this->getResponseContent() as $apiRecord) {
            $supplier = new Supplier();
            $supplier->setData($apiRecord);
            $suppliers[] = $supplier;
        }

        return $suppliers;
    }

    private function getResponseContent($json = false){
        if ($this->response->getStatusCode() != 200) {
            throw new \Exception('Response has no content. Response status code: ' . $this->response->getStatusCode() . ' Response Error Message: ' . $this->response->getReasonPhrase());
        }

        $contents = $this->response->getBody()->getContents();

        if ($json == true) {
            return $contents;
        }

        return json_decode($contents, true);
    }

}






// [vw_Framework]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig={{sig}}&filter=FrameworkNumber eq 'RM6100'
// [vw_FrameworkLots]/?api-version=2016-10-01&sp=%2Ftriggers%2Fmanual%2Frun&sv=1.0&sig={{sig}}&filter=FrameworkSalesforceID eq 'a044L000004LZDIQA4'