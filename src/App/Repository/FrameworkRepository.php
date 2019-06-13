<?php

namespace App\Repository;

use App\Exception\DbException;
use App\Model\Framework;
use PDOException;

class FrameworkRepository extends AbstractRepository {

    protected $databaseBindings = [
      'rm_number'               => ':rm_number',
      'wordpress_id'            => ':wordpress_id',
      'salesforce_id'           => ':salesforce_id',
      'title'                   => ':title',
      'type'                    => ':type',
      'terms'                   => ':terms',
      'pillar'                  => ':pillar',
      'category'                => ':category',
      'status'                  => ':status',
      'start_date'              => ':start_date',
      'end_date'                => ':end_date',
      'tenders_open_date'       => ':tenders_open_date',
      'tenders_close_date'      => ':tenders_close_date',
      'expected_live_date'      => ':expected_live_date',
      'expected_award_date'     => ':expected_award_date',
      'description'             => ':description',
      'updates'                 => ':updates',
      'summary'                 => ':summary',
      'benefits'                => ':benefits',
      'how_to_buy'              => ':how_to_buy',
      'document_updates'        => ':document_updates',
      'publish_on_website'      => ':publish_on_website',
      'published_status'        => ':published_status',
      'keywords'                => ':keywords',
      'upcoming_deal_details'   => ':upcoming_deal_details',
    ];

     /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'ccs_frameworks';

    /**
     * Any data fields present in the database which are originally from Wordpress
     *
     * @var array
     */
    protected $wordpressDataFields = [
      'wordpress_id',
      'type',
      'description',
      'updates',
      'summary',
      'benefits',
      'how_to_buy',
      'document_updates',
      'published_status',
      'keywords',
      'upcoming_deal_details'
    ];

    public function createModel($data = null)
    {
        return new Framework($data);
    }

    /**
     * @param \App\Model\Framework $framework
     * @return mixed
     */
    public function create(Framework $framework) {

        // Build the bindings PDO statement
        $columns = implode(", ", array_keys($this->databaseBindings));
        $fieldParams = implode(", ", array_values($this->databaseBindings));

        $sql = 'INSERT INTO ' . $this->tableName . ' (' . $columns . ') VALUES(' . $fieldParams . ')';

        $query = $this->connection->prepare($sql);

        $query = $this->bindValues($this->databaseBindings, $query, $framework);

        $result = $query->execute();
        if ($result === false) {
            // @see https://www.php.net/manual/en/pdo.errorinfo.php
            $info = $query->errorInfo();
            throw new DbException(sprintf('Create framework record failed. Error %s: %s', $info[0], $info[2]));
        }

        return $result;
    }

    /**
     * @param $searchField
     * @param $searchValue
     * @param \App\Model\Framework $framework
     * @return mixed
     */
    public function update($searchField, $searchValue, Framework $framework)
    {
        // Remove the field which we're using for the update command
        if (isset($this->databaseBindings[$searchField]))
        {
            unset($this->databaseBindings[$searchField]);
        }

        // Build the bindings PDO statement
        $sql = 'UPDATE ' . $this->tableName . ' SET ';
        $count = 0;
        foreach ($this->databaseBindings as $column => $field) {
            $sql .= '`' . $column . '` = ' . $field;
            if (count($this->databaseBindings) != ($count + 1)) {
                $sql .= ', ';
            } else {
                $sql .= ' ';
            }
            $count++;
        }

        $sql .= 'WHERE ' . $searchField . ' = :searchValue';
        $query = $this->connection->prepare($sql);
        $query->bindParam(':searchValue', $searchValue, \PDO::PARAM_STR);

        $query = $this->bindValues($this->databaseBindings, $query, $framework);

        $result = $query->execute();
        if ($result === false) {
            $info = $query->errorInfo();
            throw new DbException(sprintf('Update framework record failed. Error %s: %s', $info[0], $info[2]));
        }

        return $result;
    }


    /**
     * Bind PDO Values
     *
     * @param $databaseBindings
     * @param $query
     * @param $framework
     * @return mixed
     */
    protected function bindValues($databaseBindings, $query, Framework $framework)
    {
        if (isset($databaseBindings['rm_number']))
        {
            $rmNumber = $framework->getRmNumber();
            $query->bindParam(':rm_number', $rmNumber, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['title']))
        {
            $title = $framework->getTitle();
            $query->bindParam(':title', $title, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['wordpress_id']))
        {
            $wordpressId = $framework->getWordpressId();
            $query->bindParam(':wordpress_id', $wordpressId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['salesforce_id']))
        {
            $salesforceId = $framework->getSalesforceId();
            $query->bindParam(':salesforce_id', $salesforceId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['terms']))
        {
            $terms = $framework->getTerms();
            $query->bindParam(':terms', $terms, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['type']))
        {
            $type = $framework->getType();
            $query->bindParam(':type', $type, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['pillar']))
        {
            $pillar = $framework->getPillar();
            $query->bindParam(':pillar', $pillar, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['category']))
        {
            $category = $framework->getCategory();
            $query->bindParam(':category', $category, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['status']))
        {
            $status = $framework->getStatus();
            $query->bindParam(':status', $status, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['start_date']))
        {
            $startDate = $framework->getStartDate();
            if ($startDate instanceof \DateTime)
            {
                $startDate = $startDate->format('Y-m-d');
            }
            $query->bindParam(':start_date', $startDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['end_date']))
        {
            $endDate = $framework->getEndDate();
            if ($endDate instanceof \DateTime)
            {
                $endDate = $endDate->format('Y-m-d');
            }
            $query->bindParam(':end_date', $endDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['tenders_open_date']))
        {
            $tendersOpenDate = $framework->getTendersOpenDate();
            if ($tendersOpenDate instanceof \DateTime)
            {
                $tendersOpenDate = $tendersOpenDate->format('Y-m-d');
            }
            $query->bindParam(':tenders_open_date', $tendersOpenDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['tenders_close_date']))
        {
            $tendersCloseDate = $framework->getTendersCloseDate();
            if ($tendersCloseDate instanceof \DateTime)
            {
                $tendersCloseDate = $tendersCloseDate->format('Y-m-d');
            }
            $query->bindParam(':tenders_close_date', $tendersCloseDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['expected_live_date']))
        {
            $expectedLiveDate = $framework->getExpectedLiveDate();
            if ($expectedLiveDate instanceof \DateTime)
            {
                $expectedLiveDate = $expectedLiveDate->format('Y-m-d');
            }
            $query->bindParam(':expected_live_date', $expectedLiveDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['expected_award_date']))
        {
            $expectedAwardDate = $framework->getExpectedAwardDate();
            if ($expectedAwardDate instanceof \DateTime)
            {
                $expectedAwardDate = $expectedAwardDate->format('Y-m-d');
            }
            $query->bindParam(':expected_award_date', $expectedAwardDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['description']))
        {
            $description = $framework->getDescription();
            $query->bindParam(':description', $description, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['updates']))
        {
            $updates = $framework->getUpdates();
            $query->bindParam(':updates', $updates, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['summary']))
        {
            $summary = $framework->getSummary();
            $query->bindParam(':summary', $summary, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['benefits']))
        {
            $benefits = $framework->getBenefits();
            $query->bindParam(':benefits', $benefits, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['how_to_buy']))
        {
            $howToBuy = $framework->getHowToBuy();
            $query->bindParam(':how_to_buy', $howToBuy, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['document_updates']))
        {
            $documentUpdates = $framework->getDocumentUpdates();
            $query->bindParam(':document_updates', $documentUpdates, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['publish_on_website']))
        {
            $publishOnWeb = ($framework->isPublishOnWebsite()) ? 1 : 0;
            $query->bindParam(':publish_on_website', $publishOnWeb, \PDO::PARAM_INT);
        }

        if (isset($databaseBindings['published_status']))
        {
            $publishedStatus = $framework->getPublishedStatus();
            $query->bindParam(':published_status', $publishedStatus, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['keywords']))
        {
            $keywords = $framework->getKeywords();
            $query->bindParam(':keywords', $keywords, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['upcoming_deal_details']))
        {
            $upcomingDealDetails = $framework->getUpcomingDealDetails();
            $query->bindParam(':upcoming_deal_details', $upcomingDealDetails, \PDO::PARAM_STR);
        }

        return $query;
    }


    /**
     * Find Framework by id that is published in Wordpress and has status live or expired - data still received
     *
     * @param $id
     * @return mixed
     */
    public function findLiveFramework($id) {

        $sql = <<<EOD
SELECT * from `ccs_frameworks` 
WHERE rm_number = '$id' 
AND published_status = 'publish' 
AND (status = 'Live' 
    OR status = 'Expired - Data Still Received')
EOD;
        return $this->findSingleRow($sql);
    }

    /**
     * Find Framework by id that is published in Wordpress and has status live or expired - data still received or upcoming
     *
     * @param $id
     * @return mixed
     */
    public function findLiveOrUpcomingFramework($id) {

        $sql = <<<EOD
SELECT * from `ccs_frameworks` 
WHERE rm_number = '$id' 
AND published_status = 'publish' 
AND (status = 'Live' 
    OR status = 'Expired - Data Still Received'
    OR status = 'Future (Pipeline)' 
    OR status = 'Planned (Pipeline)' 
    OR status = 'Underway (Pipeline)' 
    OR status = 'Awarded (Pipeline)')
EOD;
        return $this->findSingleRow($sql);
    }

    /**
     * Count all live and published frameworks for a supplier, based on the supplier id
     *
     * @param $id
     * @return mixed
     */
    public function countAllSupplierLiveFrameworks($id){

        $sql = 'SELECT COUNT(*) as count FROM `ccs_frameworks` 
WHERE salesforce_id IN
        (SELECT `framework_id` FROM `ccs_lots`
	WHERE salesforce_id IN
        (SELECT `lot_id` FROM `ccs_lot_supplier`
		WHERE supplier_id= \'' . $id  . '\'))
AND (status = \'Live\' OR status = \'Expired - Data Still Received\')
AND published_status = \'publish\'';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count']))
        {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Find all live and published frameworks for a supplier, based on the supplier id
     *
     * @param $id
     * @return mixed
     */
    public function findSupplierLiveFrameworks($id){

        $query = 'SELECT * FROM `ccs_frameworks` 
WHERE salesforce_id IN
        (SELECT `framework_id` FROM `ccs_lots`
	WHERE salesforce_id IN
        (SELECT `lot_id` FROM `ccs_lot_supplier`
		WHERE supplier_id= \'' . $id  . '\'))
AND (status = \'Live\' OR status = \'Expired - Data Still Received\')
AND published_status = \'publish\' 
ORDER BY title';

        return $this->findAllFrameworks($query);
    }

    /**
     * Find the upcoming deals frameworks
     *
     * @return mixed
     */
    public function findUpcomingDeals() {

        $sql = 'SELECT * from `ccs_frameworks` 
WHERE published_status = \'publish\' 
AND (status = \'Future (Pipeline)\' 
    OR status = \'Planned (Pipeline)\' 
    OR status = \'Underway (Pipeline)\' 
    OR (status = \'Awarded (Pipeline)\' 
        OR (status = \'Live\' 
            AND start_date >= DATE_ADD(NOW(), INTERVAL -3 MONTH) 
            AND terms <> \'DPS\')) 
    OR (status = \'Live\' AND terms = \'DPS\'))';

        $upcomingDeals = $this->findAllFrameworks($sql);

        if (!$upcomingDeals)
        {
            return [];
        }

        return $upcomingDeals;

    }

    /**
     * Find all rows based on the keyword text search, with pagination
     *
     * @param $keyword
     * @return mixed
     */
    public function performKeywordSearch($keyword, $limit, $page)
    {
        $sql = 'SELECT f.* 
FROM ccs_frameworks f
JOIN ccs_lots l ON l.framework_id = f.salesforce_id
WHERE (f.title LIKE \'%' . $keyword . '%\'
      OR f.summary LIKE \'%' . $keyword . '%\'
      OR f.description LIKE \'%' . $keyword . '%\'
      OR f.keywords LIKE \'%' . $keyword . '%\'
      OR l.title LIKE \'%' . $keyword . '%\'
      OR l.description LIKE \'%' . $keyword . '%\')
AND f.published_status = \'publish\' 
AND (f.status = \'Live\' 
    OR f.status = \'Expired - Data Still Received\')
GROUP BY f.id
ORDER by f.title ASC;';

        return $this->findAllFrameworks($sql, true, $limit, $page);

    }

    /**
     * Count all results of live and published frameworks based on the search keyword
     *
     * @param $keyword
     * @return mixed
     */
    public function countSearchResults($keyword){

        $sql = 'SELECT COUNT(*) as count FROM 
(SELECT f.* 
FROM ccs_frameworks f
JOIN ccs_lots l ON l.framework_id = f.salesforce_id
WHERE (f.title LIKE \'%' . $keyword . '%\'
      OR f.summary LIKE \'%' . $keyword . '%\'
      OR f.description LIKE \'%' . $keyword . '%\'
      OR f.keywords LIKE \'%' . $keyword . '%\'
      OR l.title LIKE \'%' . $keyword . '%\'
      OR l.description LIKE \'%' . $keyword . '%\')
AND f.published_status = \'publish\' 
AND (f.status = \'Live\' 
    OR f.status = \'Expired - Data Still Received\')
GROUP BY f.id
ORDER by f.title ASC) SearchTableAlias';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count']))
        {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Find all rows based on a query, with pagination
     *
     * @param $sql
     * @param bool $paginate
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function findAllFrameworks($sql = null, $paginate = false, $limit = 20, $page = 0)
    {
        if ($paginate)
        {
            $sql = $this->addPaginationQuery($sql, $limit, $page);
        }
        try {
            $query = $this->connection->prepare($sql);
            $query->execute();

            $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
        }

        if (empty($results)) {
            return false;
        }

        $modelCollection = $this->translateResultsToModels($results);
        return $modelCollection;
    }

}
