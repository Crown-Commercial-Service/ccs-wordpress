<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Search\Mapping\FrameworkMapping;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Mapping;
use Elastica\Query;
use Elastica\ResultSet;
use Elastica\Search;

/**
 * Class FrameworkSearchClient
 * @package App\Search
 */
class FrameworkSearchClient extends AbstractSearchClient implements SearchClientInterface
{

    /**
     * The name of the index
     */
    const INDEX_NAME = 'framework';

    /**
     * Default sorting field
     *
     * @var string
     */
    protected $defaultSortField = 'title.raw';

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
     * Returns the mapping properties for the index
     *
     * @return \Elastica\Mapping
     */
    public function getIndexMapping(): Mapping
    {
        return (new FrameworkMapping());
    }

    /**
     * Updates a framework or creates a new one if it doesnt already exist
     *
     * @param \App\Model\ModelInterface $model
     * @param array|null $relationships
     */
    public function createOrUpdateDocument(ModelInterface $model, array $relationships = null): void {

        /** @var \App\Model\Framework $model */
        $framework = $model;

        // Create a document
        $documentData = [
          'id'            => $framework->getId(),
          'salesforce_id' => $framework->getSalesforceId(),
          'title'          => $framework->getTitle(),
        ];

//        $frameworkData = [];
//        if (!empty($relationships)) {
//            /** @var \App\Model\Framework $framework */
//            foreach ($relationships as $framework)
//            {
//                $tempFramework['title'] = $framework->getTitle();
//                $tempFramework['rm_number'] = $framework->getRmNumber();
//                $tempFramework['end_date'] = $framework->getEndDate()->format('Y-m-d');
//                $tempFramework['status'] = $framework->getStatus();
//                $frameworkData[] = $tempFramework;
//            }
//        }
//
//        $supplierData['live_frameworks'] = $frameworkData;

        // Create a new document with the data we need
        $document = new Document();
        $document->setData($documentData);
        $document->setId($framework->getId());
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
            $multiMatchQueryForNameField->setFields(['title^2']);
            $multiMatchQueryForNameField->setFuzziness(1);
            $boolQuery->addShould($multiMatchQueryForNameField);

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
        return $query;
    }


}