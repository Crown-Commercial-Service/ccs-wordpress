<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Model\Supplier;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;

/**
 * Class SupplierSearchClient
 * @package App\Search
 */
class SupplierSearchClient extends AbstractSearchClient implements SearchClientInterface
{

    /**
     * The name of the index
     */
    const INDEX_NAME = 'supplier';

    /**
     * Default sorting field
     *
     * @var string
     */
    protected $defaultSortField = 'name.raw';

    /**
     * Returns the name of the index
     *
     * @return string
     */
    public function getIndexName(): string
    {
        return self::INDEX_NAME;
    }

    /**
     * Updates a supplier or creates a new one if it doesnt already exist
     *
     * @param \App\Model\ModelInterface $model
     * @param array|null $relationships
     */
    public function createOrUpdateDocument(ModelInterface $model, array $relationships = null): void {

        /** @var Supplier $model */
        $supplier = $model;

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
        if (!empty($relationships)) {
            /** @var \App\Model\Framework $framework */
            foreach ($relationships as $framework)
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
        $this->getIndexOrCreate()->updateDocument($document);

        // Refresh Index
        $this->getIndexOrCreate()->refresh();
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
    public function queryByKeyword(string $keyword = '', int $page, int $limit, array $filters = [], string $sortField = ''): ResultSet {
        $search = new Search($this);

        $search->addIndex($this->getIndexOrCreate());

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

        $boolQuery = $this->addSearchFilters($boolQuery, $filters);

        $query = new Query($boolQuery);

        $query->setSize($limit);
        $query->setFrom($this->translatePageNumberAndLimitToStartNumber($page, $limit));

        $query = $this->sortQuery($query, $keyword, $sortField);

        $query = $this->addAggregationsToQuery($query);

        $search->setQuery($query);

        return $search->search();
    }



    /**
     * Adds the aggregations to the query
     *
     * @param \Elastica\Query $query
     * @return \Elastica\Query
     */
    public function addAggregationsToQuery(Query $query): Query {
        $termsAggregation = new Terms('titles');
        $termsAggregation->setField('live_frameworks.title');
        $nestedAggregation = new Nested('frameworks', 'live_frameworks');
        $nestedAggregation->addAggregation($termsAggregation);
        $termsAggregation->setSize(1000);

        return $query->addAggregation($nestedAggregation);
    }


}