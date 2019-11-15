<?php

namespace App\Search;

use App\Model\Supplier;
use App\Search\Mapping\SupplierMapping;
use App\Services\Logger\SearchLogger;
use Elastica\Aggregation\Filters;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\ReverseNested;
use Elastica\Aggregation\Terms;
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

        $this->checkForRequiredEnvVars();

        $config = [
            'host' => getenv('ELASTIC_HOST'),
            'port' => getenv('ELASTIC_PORT'),
        ];

        parent::__construct($config, $callback, $logger);
    }

    /**
     * Checks for required environment variables before booting
     *
     * @throws \Exception
     */
    protected function checkForRequiredEnvVars()
    {
        if (!getenv('ELASTIC_HOST')) {
            throw new \Exception('Please set the ELASTIC_HOST environment variable before continuing.');
        }

        if (!getenv('ELASTIC_PORT')) {
            throw new \Exception('Please set the ELASTIC_PORT environment variable before continuing.');
        }

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
                $tempFramework['end_date'] = $framework->getEndDate()->format('Y-m-d');
                $tempFramework['status'] = $framework->getStatus();
                $frameworkData[] = $tempFramework;
            }
        }

        $supplierData['live_frameworks'] = $frameworkData;

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
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @param string $sortField
     * @return \Elastica\ResultSet
     * @throws \IndexNotFoundException
     */
    public function querySupplierIndexByKeyword(string $type, string $keyword = '', int $page, int $limit, array $filters = [], string $sortField = ''): ResultSet {
        $search = new Search($this);

        $search->addIndex($this->convertIndexTypeToIndex($type));

        // Create a bool query to allow us to set up multiple query types
        $boolQuery = new Query\BoolQuery();

        if (!empty($keyword)) {
            // Create a multimatch query so we can search multiple fields
            $multiMatchQuery = new Query\MultiMatch();
            $multiMatchQuery->setQuery($keyword);
            $multiMatchQuery->setFuzziness(1);
            $boolQuery->addShould($multiMatchQuery);

            // Add a boost to the title
            $multiMatchQueryForNameField = new Query\MultiMatch();
            $multiMatchQueryForNameField->setQuery($keyword);
            $multiMatchQueryForNameField->setFields(['name^2']);
            $multiMatchQueryForNameField->setFuzziness(1);
            $boolQuery->addShould($multiMatchQueryForNameField);

            $multiMatchQueryWithoutFuzziness = new Query\MultiMatch();
            $multiMatchQueryWithoutFuzziness->setQuery($keyword);
            $nestedQuery = new Query\Nested();
            $nestedQuery->setQuery($multiMatchQueryWithoutFuzziness);
            $nestedQuery->setPath('live_frameworks');
            $boolQuery->addShould($nestedQuery);

        }

        $boolQuery = $this->addSelectedSearchFilters($boolQuery, $filters);

        $query = new Query($boolQuery);

        $query->setSize($limit);
        $query->setFrom($this->translatePageNumberAndLimitToStartNumber($page, $limit));

        $query = $this->sortQuery($query, $keyword, $sortField);

        $query = $this->addAggregationsToQuery($query);

        $search->setQuery($query);

        return $search->search();
    }

    /**
     * Adds the search filters to the query.
     *
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param array $filters each array item must contain the keys 'field' and 'value'
     * @return \Elastica\Query\BoolQuery
     */
    protected function addSelectedSearchFilters(Query\BoolQuery $boolQuery, array $filters): Query\BoolQuery {

        foreach ($filters as $filter)
        {
            if (strpos($filter['field'], '.') !== false) {
                $boolQuery = $this->addNestedSearchFilter($boolQuery, $filter);
            } else {
                $boolQuery = $this->addSimpleSearchFilter($boolQuery, $filter);
            }
        }

        return $boolQuery;
    }

    /**
     * Add a simple search filter
     *
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param array $filter must contain the keys 'field' and 'value'
     * @return \Elastica\Query\BoolQuery
     */
    protected function addSimpleSearchFilter(Query\BoolQuery $boolQuery, array $filter): Query\BoolQuery {
        $matchQuery = new Query\Match($filter['field'], $filter['value']);
        $boolQuery->addMust($matchQuery);

        return $boolQuery;
    }

    /**
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param array $filter must contain the keys 'field' and 'value'
     * @return \Elastica\Query\BoolQuery
     */
    protected function addNestedSearchFilter(Query\BoolQuery $boolQuery, array $filter): Query\BoolQuery {
        $matchQuery = new Query\Match($filter['field'], $filter['value']);
        $nested = new Query\Nested();
        $nested->setPath(strtok($filter['field'], '.'));
        $nested->setQuery($matchQuery);
        $boolQuery->addMust($nested);

        return $boolQuery;
    }

    /**
     * Adds the aggregations to the supplier query
     *
     * @param \Elastica\Query $query
     * @return \Elastica\Query
     */
    protected function addAggregationsToQuery(Query $query): Query {
        $termsAggregation = new Terms('titles');
        $termsAggregation->setField('live_frameworks.title');
        $nestedAggregation = new Nested('frameworks', 'live_frameworks');
        $nestedAggregation->addAggregation($termsAggregation);
        $termsAggregation->setSize(1000);

        return $query->addAggregation($nestedAggregation);
    }

    /**
     * Sort the query
     *
     * @param \Elastica\Query $query
     * @param string $keyword
     * @param string $sortField
     * @return \Elastica\Query
     */
    protected function sortQuery(Query $query, string $keyword, string $sortField): Query {
        if (empty($keyword) && empty($sortField)) {
            $query->addSort('name.raw');
            return $query;
        }

        if (empty($keyword) && !empty($sortField)) {
            $query->addSort($sortField);
            return $query;
        }

        // Otherwise let's order by the score
        $query->addSort('_score');
        return $query;

    }

    /**
     * For pagination we work out the result number to start searching from
     *
     * @param int $page
     * @param int $limit
     * @return int
     */
    protected function translatePageNumberAndLimitToStartNumber(int $page, int $limit): int {
        if ($page >= 2)
        {
            $page = $page-1;
        } else {
            $page = 0;
        }

        return $page * $limit;
    }

}