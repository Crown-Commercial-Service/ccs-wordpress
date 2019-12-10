<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Model\Supplier;
use App\Search\Mapping\SupplierMapping;
use Elastica\Aggregation\Nested;
use Elastica\Aggregation\Terms;
use Elastica\Document;
use Elastica\Mapping;
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
     * @var array
     */
    protected $synonyms = [
      'tmw' => 'Tullo Marshall Warren'
    ];

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
        return (new SupplierMapping());
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
        $documentData = [
          'id'                        => $supplier->getId(),
          'salesforce_id'             => $supplier->getSalesforceId(),
          'name'                      => $supplier->getName(),
          'duns_number'               => $supplier->getDunsNumber(),
          'trading_name'              => $supplier->getTradingName(),
          'alternative_trading_names' => $supplier->getAlternativeTradingNames(),
          'city'                      => $supplier->getCity(),
          'postcode'                  => $supplier->getPostcode(),
        ];

        $frameworkData = [];
        if (!empty($relationships)) {
            /** @var \App\Model\Framework $framework */
            foreach ($relationships as $framework)
            {
                $tempFramework['title'] = $framework->getTitle();
                $tempFramework['rm_number'] = $framework->getRmNumber();
                $tempFramework['rm_number_numerical'] = preg_replace("/[^0-9]/", "", $framework->getRmNumber());
                $tempFramework['end_date'] = !empty($framework->getEndDate()) ? $framework->getEndDate()->format('Y-m-d') : null;
                $tempFramework['status'] = $framework->getStatus();
                $tempFramework['lot_ids'] = $framework->getLotIds();
                $frameworkData[] = $tempFramework;
            }
        }

        $documentData['live_frameworks'] = $frameworkData;

        // Create a new document with the data we need
        $document = new Document();
        $document->setData($documentData);
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

            $keyword = $this->checkKeywordAgainstSynonyms($keyword);

            // Create a multimatch query so we can search multiple fields
            $multiMatchQuery = new Query\MultiMatch();
            $multiMatchQuery->setQuery($keyword);
            $multiMatchQuery->setFuzziness(1);
            $boolQuery->addShould($multiMatchQuery);

            // Add a boost to the title
            $multiMatchQueryForNameField = new Query\MultiMatch();
            $multiMatchQueryForNameField->setQuery($keyword);
            $multiMatchQueryForNameField->setFields(['name^2']);
            $multiMatchQueryForNameField->setFuzziness(0);
            $boolQuery->addShould($multiMatchQueryForNameField);

            $multiMatchQueryWithoutFuzziness = new Query\MultiMatch();
            $multiMatchQueryWithoutFuzziness->setQuery($keyword);
            $nestedQuery = new Query\Nested();
            $nestedQuery->setQuery($multiMatchQueryWithoutFuzziness);
            $nestedQuery->setPath('live_frameworks');
            $boolQuery->addShould($nestedQuery);

            $boolQuery->setMinimumShouldMatch(1);
        }
        


        $boolQuery = $this->addSearchFilters($boolQuery, $filters);


        $query = new Query($boolQuery);

//        print_r($query->toArray());
//        die();

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
        $termsAggregationRmNumber = new Terms('rm_number');
        $termsAggregationRmNumber->setField('live_frameworks.rm_number');
        $termsAggregationRmNumber->setSize(1);
        $termsAggregation->addAggregation($termsAggregationRmNumber);
        $nestedAggregation = new Nested('frameworks', 'live_frameworks');
        $nestedAggregation->addAggregation($termsAggregation);
        $termsAggregation->setSize(1000);

        return $query->addAggregation($nestedAggregation);
    }


}