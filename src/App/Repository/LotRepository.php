<?php

namespace App\Repository;

use App\Model\Lot;

class LotRepository extends AbstractRepository
{
    protected $databaseBindings = [
      'framework_id'   => ':framework_id',
      'wordpress_id'   => ':wordpress_id',
      'salesforce_id'  => ':salesforce_id',
      'lot_number'     => ':lot_number',
      'title'          => ':title',
      'status'         => ':status',
      'description'    => ':description',
      'expiry_date'    => ':expiry_date',
      'hide_suppliers' => ':hide_suppliers',
    ];

    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'ccs_lots';

    /**
     * Any data fields present in the database which are originally from Wordpress
     *
     * @var array
     */
    protected $wordpressDataFields = ['description'];

    public function createModel($data = null)
    {
        return new Lot($data);
    }

    /**
     * @param \App\Model\Lot $lot
     * @return mixed
     */
    public function create(Lot $lot) {

        // Build the bindings PDO statement
        $columns = implode(", ", array_keys($this->databaseBindings));
        $fieldParams = implode(", ", array_values($this->databaseBindings));

        $sql = 'INSERT INTO ' . $this->tableName . ' (' . $columns . ') VALUES(' . $fieldParams . ')';

        $query = $this->connection->prepare($sql);

        $query = $this->bindValues($this->databaseBindings, $query, $lot);

        return $query->execute();
    }

    /**
     * @param $searchField
     * @param $searchValue
     * @param \App\Model\Lot $lot
     * @return mixed
     */
    public function update($searchField, $searchValue, Lot $lot)
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

        $query = $this->bindValues($this->databaseBindings, $query, $lot);

        return $query->execute();
    }


    /**
     * Bind PDO Values
     *
     * @param $databaseBindings
     * @param $query
     * @param $lot
     * @return mixed
     */
    protected function bindValues($databaseBindings, $query, Lot $lot)
    {
        if (isset($databaseBindings['framework_id']))
        {
            $frameworkId = $lot->getFrameworkId();
            $query->bindParam(':framework_id', $frameworkId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['wordpress_id']))
        {
            $wordpressId = $lot->getWordpressId();
            $query->bindParam(':wordpress_id', $wordpressId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['salesforce_id']))
        {
            $salesforceId = $lot->getSalesforceId();
            $query->bindParam(':salesforce_id', $salesforceId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['lot_number']))
        {
            $lotNumber = $lot->getLotNumber();
            $query->bindParam(':lot_number', $lotNumber, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['title']))
        {
            $title = $lot->getTitle();
            $query->bindParam(':title', $title, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['status']))
        {
            $status = $lot->getStatus();
            $query->bindParam(':status', $status, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['description']))
        {
            $description = $lot->getDescription();
            $query->bindParam(':description', $description, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['expiry_date']))
        {
            $expiryDate = $lot->getExpiryDate();
            if ($expiryDate instanceof \DateTime)
            {
                $expiryDate = $expiryDate->format('Y-m-d');
            }
            $query->bindParam(':expiry_date', $expiryDate, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['hide_suppliers']))
        {
            $hideSuppliers = $lot->isHideSuppliers();
            $query->bindParam(':hide_suppliers', $hideSuppliers, \PDO::PARAM_BOOL);
        }

        return $query;
    }

    /**
     * Find all lots for framework by the id
     *
     * @param $id
     * @return mixed
     */
    public function findFrameworkLots($id){

        $sql = 'SELECT l.salesforce_id FROM `ccs_frameworks` f
JOIN `ccs_lots` l ON f.salesforce_id = l.framework_id
WHERE f.rm_number = \'' . $id  . '\'
AND l.salesforce_id IS NOT NULL';

        return $this->findAllLots($sql);
    }

    /**
     * Find the lot for framework by framework id and lot number
     *
     * @param $id
     * @param $lotNumber
     * @return mixed
     */
    public function findSingleFrameworkLot($id, $lotNumber) {

        $sql = 'SELECT l.* FROM `ccs_frameworks` f
JOIN `ccs_lots` l ON f.salesforce_id = l.framework_id
WHERE f.rm_number = \'' . $id . '\' AND lot_number=\'' . $lotNumber . '\'
AND l.salesforce_id IS NOT NULL';

        return $this->findSingleRow($sql);
    }

    /**
     * Find all lots by the framework id and supplier id
     *
     * @param $frameworkId
     * @param $supplierId
     * @return bool
     */
    public function findAllByFrameworkIdSupplierId($frameworkId, $supplierId) {

        $sql = <<<EOD
SELECT l.* 
FROM ccs_frameworks f
JOIN ccs_lots l ON l.framework_id = f.salesforce_id
JOIN ccs_lot_supplier ls ON ls.lot_id = l.salesforce_id
WHERE f.salesforce_id = '$frameworkId'
AND ls.supplier_id = '$supplierId'
AND l.salesforce_id IS NOT NULL
ORDER BY cast(l.lot_number as unsigned)
EOD;
        return $this->findAllLots($sql);
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
    public function findAllLots($sql = null, $paginate = false, $limit = 20, $page = 0)
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
