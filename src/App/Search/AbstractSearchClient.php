<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Services\Logger\SearchLogger;
use Elastica\Exception\NotFoundException;
use Elastica\Index;
use Elastica\Query;
use Elastica\Request;
use Elastica\ResultSet;
use Elastica\Search;
use Psr\Log\LoggerInterface;

class AbstractSearchClient extends \Elastica\Client
{

    /**
     * @var null
     */
    protected $indexExists = null;

    protected $indexMapping;

    protected $defaultSortField = '_score';

    protected $synonyms = [];
    

    /**
     * Client constructor.
     * @param array $config
     * @param callable|null $callback
     * @param \Psr\Log\LoggerInterface|null $logger
     * @throws \Exception
     */
    public function __construct($config = [], callable $callback = null, LoggerInterface $logger = null)
    {

        if (empty($logger)) {
            $logger = new SearchLogger();
        }

        $this->checkForRequiredEnvVars();

        $config = [
          'transport' => getenv('ELASTIC_TRANSPORT'),
          'host'      => getenv('ELASTIC_HOST'),
          'port'      => getenv('ELASTIC_PORT'),
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
        if (!getenv('ELASTIC_TRANSPORT')) {
            throw new \Exception('Please set the ELASTIC_TRANSPORT environment variable before continuing.');
        }

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
     * Creates the index and sets the mappings
     */
    protected function createIndex()
    {
        $index = $this->getIndex($this->getQualifiedIndexName());

        $analysis = [
          'analysis' => array(
            'analyzer' => array(
              'english_analyzer' => array(
                'tokenizer' => 'standard',
                'filter'    => array('lowercase', 'english_stemmer', 'english_stop'),
              ),
            ),
            'filter'   => array(
              'english_stemmer' => array(
                'type' => 'stemmer',
                'name' => 'english'
              ),
              'english_stop' => array(
                'type' => 'stop',
                'stopwords' => '_english_'
              )
            )
          )
        ];

        $index->create(['settings' => $analysis]);

        $index->setMapping($this->getIndexMapping());
    }

    /**
     * Returns the ElasticSearch index, and creates it if it doesnt already exist
     *
     * @return \Elastica\Index
     */
    protected function getIndexOrCreate(): Index
    {
        if (!$this->indexExists) {
            $response = $this->request($this->getQualifiedIndexName(), Request::HEAD);
            
            if ($response->getStatus() > 299) {
                $this->createIndex();
            }

            $this->indexExists = true;
        }

        return $this->getIndex($this->getQualifiedIndexName());
    }

    /**
     * Remove a document from the ElasticSearch index
     *
     * @param \App\Model\ModelInterface $model
     */
    public function removeDocument(ModelInterface $model)
    {
        try {
            $this->getIndexOrCreate()->deleteById($model->getId());
        } catch (NotFoundException $exception) {
            // We can ignore this exception. The document was never in the index.
        }
    }


    /**
     * Get the index name for the current index
     *
     * @return string
     */
    protected function getQualifiedIndexName(): string
    {
        return $this->getIndexName() . '_' . getenv('ELASTIC_SUFFIX');
    }


    /**
     * Adds the search filters to the query.
     *
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param array $filters each array item must contain the keys 'field' and 'value'
     * @return \Elastica\Query\BoolQuery
     */
    public function addSearchFilters(Query\BoolQuery $boolQuery, array $filters): Query\BoolQuery
    {

        if (empty($filters)) {
            return $boolQuery;
        }

        foreach ($filters as $filter) {
            if (isset($filter['nested'])) {
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
     * @param $filter
     * @return \Elastica\Query\BoolQuery
     */
    protected function addSimpleSearchFilter(Query\BoolQuery $boolQuery, $filter): Query\BoolQuery
    {
        
        if (is_array($filter['value'])) {
            $newBoolQuery = new Query\BoolQuery();
            foreach ($filter['value'] as $value) {
                $newBoolQuery = $this->addMatchQueryToBoolFilter($newBoolQuery, $filter['field'], $value, $filter['condition'] ?? null);
            }
            $boolQuery->addMust($newBoolQuery);
        } else {
            $newBoolQuery = new Query\BoolQuery();
            $newBoolQuery = $this->addMatchQueryToBoolFilter($newBoolQuery, $filter['field'], $filter['value'], $filter['condition'] ?? null);
            $boolQuery->addMust($newBoolQuery);
        }

        return $boolQuery;
    }

    /**
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param $key
     * @param $value
     * @param string $condition
     * @return \Elastica\Query\BoolQuery
     */
    protected function addMatchQueryToBoolFilter(Query\BoolQuery $boolQuery, $key, $value, $condition = 'AND'): Query\BoolQuery
    {
        $matchQuery = new Query\Match($key, $value);

        if (strtoupper($condition) == 'AND') {
            $boolQuery->addMust($matchQuery);
        } else {
            $boolQuery->addShould($matchQuery);
        }

        return $boolQuery;
    }

    /**
     * @param \Elastica\Query\BoolQuery $boolQuery
     * @param $filter
     * @return \Elastica\Query\BoolQuery
     */
    protected function addNestedSearchFilter(Query\BoolQuery $boolQuery, $filter): Query\BoolQuery
    {
        $newBoolQuery = new Query\BoolQuery();

        foreach ($filter['nested'] as $nestedFilter) {
            $nestedFilter['condition'] = $filter['condition'];
            $newBoolQuery = $this->addSimpleSearchFilter($newBoolQuery, $nestedFilter);
        }

        $nested = new Query\Nested();
        $nested->setPath($filter['field']);
        $nested->setQuery($newBoolQuery);
        $boolQuery->addMust($nested);

        return $boolQuery;
    }


    /**
     * Sort the query
     *
     * @param \Elastica\Query $query
     * @param string $keyword
     * @param string $sortField
     * @return \Elastica\Query
     */
    protected function sortQuery(Query $query, string $keyword, string $sortField): Query
    {
        if (empty($keyword) && empty($sortField)) {
            $query->addSort($this->defaultSortField);
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
    protected function translatePageNumberAndLimitToStartNumber(int $page, int $limit): int
    {
        if ($page >= 2) {
            $page = $page - 1;
        } else {
            $page = 0;
        }

        return $page * $limit;
    }

    /**
     * Adds the aggregations to the query
     *
     * @param \Elastica\Query $query
     * @return \Elastica\Query
     */
    public function addAggregationsToQuery(Query $query): Query
    {
        return $query;
    }

    /**
     * Return all items (default is 1000)
     *
     * @param int $limit
     * @param null $sortField
     * @return \Elastica\ResultSet
     */
    public function getAll($limit = 1000, $sortField = ''): ResultSet
    {
        $search = new Search($this);
        $search->addIndex($this->getIndexOrCreate());
        $matchAll = new Query\MatchAll();
        $query = new Query($matchAll);
        $query->setSize($limit);
        $query = $this->sortQuery($query, '', $sortField);
        $search->setQuery($query);

        return $search->search();
    }

    /**
     * Kills the query and outputs a representative string of the query
     * Useful for debugging to check a query is doing what you expect
     *
     * @param $query
     */
    protected function outputDebug($query)
    {
        print_r(json_encode($query->getQuery()->toArray()));
        die();
    }

    /**
     * Takes an array of Synonyms and converts the output to the synonym.
     * Currently this only works on complete search phrase, not individual terms
     *
     * @param string $searchPhrase
     * @return string
     */
    protected function checkKeywordAgainstSynonyms(string $searchPhrase): string
    {
        foreach ($this->synonyms as $keyword => $synonym) {
            if (strtolower($keyword) === strtolower($searchPhrase)) {
                return $synonym;
            }
        }

        return $searchPhrase;
    }
}
