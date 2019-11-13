<?php

namespace App\Search;

use App\Model\Supplier;
use App\Search\Mapping\SupplierMapping;
use App\Services\Logger\SearchLogger;
use Elastica\Document;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\Query\Term;
use Elastica\Request;
use Elastica\ResultSet;
use Elastica\Search;
use Psr\Log\LoggerInterface;

class Client extends \Elastica\Client
{

    /**
     * @var \Elastica\Index
     */
    // TODO: We need to set the environment name here on the end of the index, to allow using the same server for both live and staging. But where is environment set?
    /**
     *
     */
    const SUPPLIER_TYPE_NAME = 'supplier';

    /**
     * @var null
     */
    protected $supplierIndexExists = null;

    /**
     * Client constructor.
     * @param array $config
     * @param callable|null $callback
     * @param \Psr\Log\LoggerInterface|null $logger
     * @throws \Exception
     */
    public function __construct($config = [], callable $callback = null, LoggerInterface $logger = null) {

        if (empty($logger)) {
            $logger = new SearchLogger();
        }

        parent::__construct($config, $callback, $logger);

        if (!getenv('ELASTIC_SUFFIX')) {
            throw new \Exception('Please set the ELASTIC_SUFFIX environment variable before continuing.');
        }
    }

    /**
     *
     */
    protected function createSupplierIndex() {
        $index = $this->getIndex($this->getIndexName(self::SUPPLIER_TYPE_NAME));

        // Create the index new
        $index->create();

        $index->setMapping(new SupplierMapping());
    }

    /**
     * Returns the ElasticSearch index for the supplier
     *
     * @return \Elastica\Index
     */
    protected function getSupplierIndex(): Index
    {
        if (!$this->supplierIndexExists) {
            $response = $this->request( $this->getIndexName(self::SUPPLIER_TYPE_NAME), Request::HEAD);
            
            if ($response->getStatus() > 299) {
                $this->createSupplierIndex();
            }

            $this->supplierIndexExists = true;
        }

        return $this->getIndex($this->getIndexName(self::SUPPLIER_TYPE_NAME));
    }

    /**
     * Remove a supplier from the ElasticSearch index
     *
     * @param \App\Model\Supplier $supplier
     */
    public function removeSupplier(Supplier $supplier)
    {
        try {
            $this->getSupplierIndex()->deleteById($supplier->getId());
        } catch (NotFoundException $exception)
        {
            // We can ignore this exception. The document was never in the index.
        }

    }

    /**
     * Updates a supplier or creates a new one if it doesnt already exist
     *
     * @param \App\Model\Supplier $supplier
     * @param array|null $frameworks
     */
    public function createOrUpdateSupplier(Supplier $supplier, array $frameworks = null) {

        // Create a document
        $supplierData = [
          'id'            => $supplier->getId(),
          'salesforce_id' => $supplier->getSalesforceId(),
          'name'          => $supplier->getName(),
          'duns_number'   => $supplier->getDunsNumber(),
          'trading_name'  => $supplier->getTradingName(),
          'city'          => $supplier->getCity(),
          'postcode'      => $supplier->getPostcode(),
        ];

        $frameworkData = [];
        if (!empty($frameworks)) {
            /** @var \App\Model\Framework $framework */
            foreach ($frameworks as $framework)
            {
                $tempFramework['title'] = $framework->getTitle();
                $tempFramework['rm_number'] = $framework->getRmNumber();
                $tempFramework['end_date'] = $framework->getEndDate();
                $frameworkData[] = $tempFramework;
            }
        }

        $supplierData['frameworks'] = $frameworkData;

        // Create a new document with the data we need
        $document = new Document();
        $document->setData($supplierData);
        $document->setId($supplier->getId());
        $document->setDocAsUpsert(true);

        // Add document
        $this->getSupplierIndex()->updateDocument($document);

        // Refresh Index
        $this->getSupplierIndex()->refresh();
    }

    /**
     * Get the index name for the current index
     *
     * @param string $type
     * @return string
     */
    protected function getIndexName(string $type): string {
        return $type . '_' . getenv('ELASTIC_SUFFIX');
    }

    /**
     * Provide this class with a index type string and it will return the index
     *
     * @param string $type
     * @return \Elastica\Index
     * @throws \IndexNotFoundException
     */
    protected function convertIndexTypeToIndex(string $type): Index {
        switch ($type) {
            case self::SUPPLIER_TYPE_NAME:
                return $this->getSupplierIndex();
                break;
        }

        throw new \IndexNotFoundException('Index with the name: "' . $type . '" not found');
    }

    /**
     * Query's the fields on a given index
     *
     * @param string $type
     * @param string $keyword
     * @return array
     */
    public function queryIndexByKeyword(string $type, string $keyword): array {
        $search = new Search($this);

        $search->addIndex($this->convertIndexTypeToIndex($type));

        $multiMatch = new Query\MultiMatch();
        $multiMatch->setQuery($keyword);
        $multiMatch->setFuzziness(10);
        $query = new Query($multiMatch);
        $search->setQuery($query);

        $resultSet = $search->search();

        return $resultSet->getResults();
    }



}