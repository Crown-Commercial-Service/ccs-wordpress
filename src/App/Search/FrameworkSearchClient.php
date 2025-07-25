<?php

namespace App\Search;

use App\Model\ModelInterface;
use App\Search\Mapping\FrameworkMapping;
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
    private const INDEX_NAME = 'framework';

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
    public function createOrUpdateDocument(ModelInterface $model, array $relationships = null): void
    {

        /** @var \App\Model\Framework $model */
        $framework = $model;

        // Create a document
        $documentData = [
          'id'                  => $framework->getId(),
          'salesforce_id'       => $framework->getSalesforceId(),
          'title'               => $framework->getTitle(),
          'start_date'          => !empty($framework->getStartDate()) ? $framework->getStartDate()->format('Y-m-d') : null,
          'end_date'            => !empty($framework->getEndDate()) ? $framework->getEndDate()->format('Y-m-d') : null,
          'tenders_open_date'   => !empty($framework->getTendersOpenDate()) ? $framework->getTendersOpenDate()->format('Y-m-d') : null,
          'tenders_close_date'  => !empty($framework->getTendersCloseDate()) ? $framework->getTendersCloseDate()->format('Y-m-d') : null,
          'expected_live_date'  => !empty($framework->getExpectedLiveDate()) ? $framework->getExpectedLiveDate()->format('Y-m-d') : null,
          'expected_award_date'  => !empty($framework->getExpectedAwardDate()) ? $framework->getExpectedAwardDate()->format('Y-m-d') : null,
          'rm_number'           => $framework->getRmNumber(),
          'rm_number_numerical' => preg_replace("/[^0-9]/", "", $framework->getRmNumber()),
          'summary'             => $framework->getSummary(),
          'upcoming_deal_summary'  => $framework->getUpcomingDealSummary(),
          'description'         => $framework->getDescription(),
          'terms'               => $framework->getTerms(),
          'pillar'              => $framework->getPillar(),
          'category'            => $framework->getCategory(),
          'status'              => $framework->getStatus(),
          'published_status'    => $framework->getPublishedStatus(),
          'benefits'            => $framework->getBenefits(),
          'how_to_buy'          => $framework->getHowToBuy(),
          'keywords'            => $framework->getKeywords(),
          'regulation'          => $framework->getRegulation(),
          'regulation_type'     => $framework->getRegulationType(),
        ];

        $lotData = [];
        if (!empty($relationships)) {
            /** @var \App\Model\Lot $lot */
            foreach ($relationships as $lot) {
                $tempLot['title'] = $lot->getTitle();
                $tempLot['description'] = $lot->getDescription();
                $lotData[] = $tempLot;
            }
        }

        $documentData['lots'] = $lotData;

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
     * @param string $keyword
     * @param int $page
     * @param int $limit
     * @param array $filters
     * @param string $sortField
     * @param array $rmNumbers
     * @return \Elastica\ResultSet
     */
    public function queryByKeyword(string $keyword, int $page, int $limit, array $filters = [], string $sortField = '', array $rmNumbers = []): ResultSet
    {
        $search = new Search($this);

        $search->addIndex($this->getIndex($this->getQualifiedIndexName()));

        // Create a bool query to allow us to set up multiple query types
        $boolQuery = new Query\BoolQuery();

        $publishedStatusQuery = new Query\MatchQuery('published_status', 'publish');
        $boolQuery->addMust($publishedStatusQuery);

        // upcoming agreements
        if (!empty($rmNumbers)) {
            $termsQuery = new Query\Terms('rm_number_numerical', $rmNumbers);
            $boolQuery->addMust($termsQuery);
        }

        if (!empty($keyword)) {
            $keywordBool = new Query\BoolQuery();

            $keyword = $this->checkKeywordAgainstSynonyms($keyword);

            // Create a multimatch query so we can search multiple fields
            $multiMatchQuery = new Query\MultiMatch();
            $multiMatchQuery->setQuery($keyword);
            $multiMatchQuery->setFields(['description^2', 'summary', 'benefits', 'how_to_buy', 'keywords^2']);
            $multiMatchQuery->setFuzziness(1);
            $multiMatchQuery->setPrefixLength(3);
            $keywordBool->addShould($multiMatchQuery);

            // Add a boost to the title
            $multiMatchQueryForNameField = new Query\MultiMatch();
            $multiMatchQueryForNameField->setQuery($keyword);
            $multiMatchQueryForNameField->setFields(['title^3']);
            $multiMatchQueryForNameField->setFuzziness(1);
            $multiMatchQueryForNameField->setPrefixLength(3);
            $keywordBool->addShould($multiMatchQueryForNameField);

            // RM Number search
            $queryForNumericalRmNumber = new Query\MultiMatch();
            $queryForNumericalRmNumber->setQuery($keyword);
            $queryForNumericalRmNumber->setFields(['rm_number^2', 'rm_number.raw^2']);
            $keywordBool->addShould($queryForNumericalRmNumber);

            $rmNumberQuery = new Query\Wildcard('rm_number.raw', $keyword . '*', 2);
            $keywordBool->addShould($rmNumberQuery);

            // Regulation search
            $multiMatchQueryForRegulation = new Query\MultiMatch();
            $multiMatchQueryForRegulation->setQuery($keyword);
            $multiMatchQueryForRegulation->setFields(['regulation^2']);
            $multiMatchQueryForRegulation->setFuzziness(1);
            $keywordBool->addShould($multiMatchQueryForRegulation);

            // Regulation type
            $multiMatchQueryForRegulationType = new Query\MultiMatch();
            $multiMatchQueryForRegulationType->setQuery($keyword);
            $multiMatchQueryForRegulationType->setFields(['regulation_type^2']);
            $multiMatchQueryForRegulationType->setFuzziness(1);
            $keywordBool->addShould($multiMatchQueryForRegulationType);

            $number = preg_replace("/[^0-9]/", "", $keyword);
            if (!empty($number)) {
                $rmNumberQuery = new Query\Wildcard('rm_number_numerical', $number . '*', 2);
                $keywordBool->addShould($rmNumberQuery);
            }

            // Look for the RM Number without 'RM'
            $queryForNumericalRmNumber = new Query\MultiMatch();
            $queryForNumericalRmNumber->setQuery($keyword . '*');
            $queryForNumericalRmNumber->setFields(['rm_number_numerical^3']);
            $keywordBool->addShould($queryForNumericalRmNumber);

            $keywordBool->setMinimumShouldMatch(1);

            $boolQuery->addMust($keywordBool);
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
    public function addAggregationsToQuery(Query $query): Query
    {
        return $query;
    }
}
