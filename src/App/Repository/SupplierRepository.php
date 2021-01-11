<?php

namespace App\Repository;

use App\Model\Supplier;

class SupplierRepository extends AbstractRepository
{

    protected $databaseBindings = [
      'salesforce_id'       => ':salesforce_id',
      'duns_number'         => ':duns_number',
      'name'                => ':name',
      'phone_number'        => ':phone_number',
      'street'              => ':street',
      'city'                => ':city',
      'country'             => ':country',
      'postcode'            => ':postcode',
      'website'             => ':website',
      'trading_name'        => ':trading_name',
      'on_live_frameworks'  => ':on_live_frameworks',
    ];

    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'ccs_suppliers';

    public function createModel($data = null)
    {
        return new Supplier($data);
    }

    /**
     * This method excludes the Wordpress Id, so it will not be overwritten with new (or null) data.
     * Create the the current data object in the database or update it if it already exists
     *
     * @param $searchField
     * @param $searchValue
     * @param $object
     * @return mixed
     */
    public function createOrUpdateExcludingLiveFrameworkField($searchField, $searchValue, $object)
    {
        $originalDataBindings = $this->databaseBindings;

        if (isset($this->databaseBindings['on_live_frameworks'])) {
            unset($this->databaseBindings['on_live_frameworks']);
        }

        $response = $this->createOrUpdate($searchField, $searchValue, $object);

        $this->databaseBindings = $originalDataBindings;

        return $response;
    }

    /**
     * @param \App\Model\Supplier $supplier
     * @return mixed
     */
    public function create(Supplier $supplier)
    {
        // Build the bindings PDO statement
        $columns = implode(", ", array_keys($this->databaseBindings));
        $fieldParams = implode(", ", array_values($this->databaseBindings));

        $sql = 'INSERT INTO ' . $this->tableName . ' (' . $columns . ') VALUES(' . $fieldParams . ')';

        $query = $this->connection->prepare($sql);

        $query = $this->bindValues($this->databaseBindings, $query, $supplier);

        return $query->execute();
    }

    /**
     * @param $searchField
     * @param $searchValue
     * @param \App\Model\Supplier $supplier
     * @return mixed
     */
    public function update($searchField, $searchValue, Supplier $supplier)
    {
        // Remove the field which we're using for the update command
        if (isset($this->databaseBindings[$searchField])) {
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

        $query = $this->bindValues($this->databaseBindings, $query, $supplier);

        return $query->execute();
    }


    /**
     * Bind PDO Values
     *
     * @param $databaseBindings
     * @param $query
     * @param $supplier
     * @return mixed
     */
    protected function bindValues($databaseBindings, $query, Supplier $supplier)
    {
        if (isset($databaseBindings['salesforce_id'])) {
            $salesforceId = $supplier->getSalesforceId();
            $query->bindParam(':salesforce_id', $salesforceId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['duns_number'])) {
            $dunsNumber = $supplier->getDunsNumber();
            $query->bindParam(':duns_number', $dunsNumber, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['name'])) {
            $name = $supplier->getName();
            $query->bindParam(':name', $name, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['phone_number'])) {
            $phoneNumber = $supplier->getPhoneNumber();
            $query->bindParam(':phone_number', $phoneNumber, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['street'])) {
            $street = $supplier->getStreet();
            $query->bindParam(':street', $street, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['city'])) {
            $city = $supplier->getCity();
            $query->bindParam(':city', $city, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['country'])) {
            $country = $supplier->getCountry();
            $query->bindParam(':country', $country, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['postcode'])) {
            $postcode = $supplier->getPostcode();
            $query->bindParam(':postcode', $postcode, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['website'])) {
            $website = $supplier->getWebsite();
            $query->bindParam(':website', $website, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['trading_name'])) {
            $tradingName = $supplier->getTradingName();
            $query->bindParam(':trading_name', $tradingName, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['on_live_frameworks'])) {
            $onLiveFrameworks = $supplier->isOnLiveFrameworks();
            $query->bindParam(':on_live_frameworks', $onLiveFrameworks, \PDO::PARAM_STR);
        }

        return $query;
    }

    /**
     * Find all suppliers for lots based on all lot ids, with pagination
     *
     * @param $lotIds
     * @param bool $paginate
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function findLotSuppliers($lotIds, $paginate = false, $limit = 20, $page = 0, $unique = false)
    {
        if ($unique) {
            $sql = 'SELECT DISTINCT s.id, s.salesforce_id, s.name, ls.trading_name, s.phone_number, s.street, s.city, s.postcode, s.website, s.country, ls.trading_name, IFNULL(ls.trading_name, s.name) as order_name FROM `ccs_suppliers` s
JOIN `ccs_lot_supplier` ls ON ls.supplier_id=s.salesforce_id
WHERE ls.lot_id IN (\'' . $lotIds . '\')
ORDER BY order_name';
        } else {
            $sql = 'SELECT DISTINCT s.id, s.salesforce_id, s.name, ls.trading_name, s.phone_number, s.street, s.city, s.postcode, s.website, s.country, ls.contact_name, ls.contact_email, ls.trading_name, IFNULL(ls.trading_name, s.name) as order_name FROM `ccs_suppliers` s
JOIN `ccs_lot_supplier` ls ON ls.supplier_id=s.salesforce_id
WHERE ls.lot_id IN (\'' . $lotIds . '\')
ORDER BY order_name';
        }

        return $this->findAllSuppliers($sql, $paginate, $limit, $page);
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
    public function findAllSuppliers($sql = null, $paginate = false, $limit = 20, $page = 0)
    {
        if ($paginate) {
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

    /**
     * Find the eligible Supplier for live frameworks
     *
     * @param $id
     * @return mixed
     */
    public function findLiveSupplier($id)
    {

        $sql = 'SELECT * from `ccs_suppliers` 
WHERE id = \'' . $id . '\' 
AND on_live_frameworks = TRUE ';

        return $this->findSingleRow($sql);
    }

    /**
     * Find a Supplier
     *
     * @param $salesforce_id
     * @return mixed
     */
    public function findASupplierBySalesforceId($salesforce_id)
    {

        $sql = 'SELECT * from `ccs_suppliers` 
WHERE salesforce_id = \'' . $salesforce_id . '\'';

        return $this->findSingleRow($sql);
    }

    /**
     * Count all unique suppliers for lots
     *
     * @param $lotIds
     * @return mixed
     */
    public function countAllSuppliers($lotIds)
    {
        $sql = 'SELECT count(DISTINCT s.id) as count FROM `ccs_suppliers` s
JOIN `ccs_lot_supplier` ls ON ls.supplier_id=s.salesforce_id
WHERE ls.lot_id IN (\'' . $lotIds . '\')';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count'])) {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Count all suppliers existing in the db for a single lot
     *
     * @param $lotId
     * @return mixed
     */
    public function countSuppliersForLot($lotId)
    {
        $sql = 'SELECT count(*) as count FROM `ccs_suppliers` s
JOIN `ccs_lot_supplier` ls ON ls.supplier_id=s.salesforce_id
WHERE ls.lot_id =\'' . $lotId . '\'';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count'])) {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Find the Supplier by the duns number
     *
     * @param $number
     * @return mixed
     */
    public function searchByDunsNumber($number)
    {

        $sql = 'SELECT * from `ccs_suppliers` 
WHERE duns_number = \'' . $number . '\' 
AND on_live_frameworks = TRUE ';

        return $this->findSingleRow($sql);
    }

    /**
     * Find all rows based on the rm number search, with pagination
     *
     * @param $rmNumber
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function searchByRmNumber($rmNumber, $limit, $page)
    {
        $sql = 'SELECT s.* 
FROM ccs_suppliers s
JOIN ccs_lot_supplier ls ON ls.supplier_id = s.salesforce_id
JOIN ccs_lots l ON l.salesforce_id = ls.lot_id
JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
WHERE f.rm_number = \'' . $rmNumber . '\'
AND f.published_status = \'publish\'
AND s.on_live_frameworks = TRUE 
GROUP BY s.id
ORDER by s.name ASC;';

        return $this->findAllSuppliers($sql, true, $limit, $page);
    }

    /**
     * Count all results of suppliers that are on live frameworks based on the search rm number
     * @param $rmNumber
     * @return mixed
     */
    public function countSearchByRmNumberResults($rmNumber)
    {

        $sql = 'SELECT COUNT(*) as count FROM 
(SELECT s.* 
FROM ccs_suppliers s
JOIN ccs_lot_supplier ls ON ls.supplier_id = s.salesforce_id
JOIN ccs_lots l ON l.salesforce_id = ls.lot_id
JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
WHERE f.rm_number = \'' . $rmNumber . '\'
AND f.published_status = \'publish\'
AND s.on_live_frameworks = TRUE 
GROUP BY s.id
ORDER by s.name ASC) SearchRmNumberAlias';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count'])) {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Find all rows based on the keyword text search, with pagination
     *
     * @param $keyword
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function performKeywordSearch($keyword, $limit, $page)
    {
        $sql = 'SELECT s.* 
FROM ccs_suppliers s
JOIN ccs_lot_supplier ls ON ls.supplier_id = s.salesforce_id
JOIN ccs_lots l ON l.salesforce_id = ls.lot_id
JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
WHERE (s.name LIKE \'%' . $keyword . '%\'
      OR s.trading_name LIKE \'%' . $keyword . '%\'
      OR s.city LIKE \'%' . $keyword . '%\'
      OR s.postcode LIKE \'%' . $keyword . '%\')
AND s.on_live_frameworks = TRUE 
GROUP BY s.id
ORDER by s.name ASC;';

        return $this->findAllSuppliers($sql, true, $limit, $page);
    }

    /**
     * Return a list of suppliers from a Framework Id
     *
     * @param $frameworkId
     * @return mixed
     */
    public function fetchSuppliersOnLiveFrameworksViaFrameworkId($frameworkId)
    {
        $sql = 'SELECT s.* 
        FROM ccs_suppliers s
        JOIN ccs_lot_supplier ls ON ls.supplier_id = s.salesforce_id
        JOIN ccs_lots l ON l.salesforce_id = ls.lot_id
        JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
        WHERE f.salesforce_id = \'' . $frameworkId . '\'
        AND s.on_live_frameworks = FALSE 
        AND (f.status = \'Live\' OR f.status = \'Expired - Data Still Received\')
        GROUP BY s.id
        ORDER by s.name ASC;';

        return $this->findAllSuppliers($sql);
    }

    /**
     * Count all results of suppliers that are on live frameworks based on the search keyword
     *
     * @param $keyword
     * @return mixed
     */
    public function countSearchResults($keyword)
    {

        $sql = 'SELECT COUNT(*) as count FROM 
(SELECT s.* 
FROM ccs_suppliers s
JOIN ccs_lot_supplier ls ON ls.supplier_id = s.salesforce_id
JOIN ccs_lots l ON l.salesforce_id = ls.lot_id
JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
WHERE (s.name LIKE \'%' . $keyword . '%\'
      OR s.trading_name LIKE \'%' . $keyword . '%\'
      OR s.city LIKE \'%' . $keyword . '%\'
      OR s.postcode LIKE \'%' . $keyword . '%\')
AND s.on_live_frameworks = TRUE 
GROUP BY s.id
ORDER by s.name ASC) SearchTableAlias';

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count'])) {
            return 0;
        }

        return (int) $results['count'];
    }
}
