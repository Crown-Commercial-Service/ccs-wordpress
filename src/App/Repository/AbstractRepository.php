<?php

namespace App\Repository;

use App\Services\Database\DatabaseConnection;
use PDOException;

abstract class AbstractRepository implements RepositoryInterface
{
    /**
     * Database bindings array
     *
     * @var array
     */
    protected $databaseBindings = [];

    /**
     * Any data fields present in the database which are originally from Wordpress
     *
     * @var array
     */
    protected $wordpressDataFields = ['wordpress_id'];

    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = '';

    /**
     * @var \App\Services\Database\DatabaseConnection
     */
    protected $connection;

    /**
     * AbstractRepository constructor.
     */
    public function __construct()
    {
        $this->connection  = ( new DatabaseConnection() )->connection;
    }

    /**
     * Adds the required SQL to make pagination work
     *
     * @param $sql
     * @param $limit
     * @param $page
     * @return string
     */
    protected function addPaginationQuery($sql, $limit, $page)
    {
        if ($page >= 2) {
            $page = $page - 1;
        } else {
            $page = 0;
        }

        $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $page * $limit;

        return $sql;
    }

    /**
     * Count all
     *
     * @param $condition
     * @return mixed
     */
    public function countAll($condition = null)
    {
        $sql = 'SELECT count(*) as count from  ' . $this->tableName . ' where ' . $condition;
        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetch(\PDO::FETCH_ASSOC);

        if (!isset($results['count'])) {
            return 0;
        }

        return (int) $results['count'];
    }

    /**
     * Find all
     *
     * @param bool $paginate
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function findAll($paginate = false, $limit = 20, $page = 0)
    {
        $sql = 'SELECT * from  ' . $this->tableName;

        if ($paginate) {
            $sql = $this->addPaginationQuery($sql, $limit, $page);
        }

        $query = $this->connection->prepare($sql);
        $query->execute();

        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return false;
        }

        $modelCollection = $this->translateResultsToModels($results);

        return $modelCollection;
    }

    /**
     * Find all rows with a certain condition
     *
     * @param $condition
     * @param bool $paginate
     * @param int $limit
     * @param int $page
     * @return mixed
     */
    public function findAllWhere($condition = null, $paginate = false, $limit = 20, $page = 0)
    {
        $sql = 'SELECT * from ' . $this->tableName . ' where ' . $condition ;


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
     * Find a row based on a custom query
     *
     * @param $sql
     * @return mixed
     */
    public function findSingleRow($sql = null)
    {
        try {
            $query = $this->connection->prepare($sql);
            $query->execute();

            $result = $query->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
        }
        if (empty($result)) {
            return false;
        }

        return $this->translateSingleResultToModel($result);
    }

    /**
     * Find a row with a certain Id
     *
     * @param string $fieldName
     * @param $id
     * @return \App\Model\Framework|\App\Model\Lot|\App\Model\Supplier|bool
     */
    public function findById($id, $fieldName = 'id')
    {
        $sql = 'SELECT * from ' . $this->tableName . ' where ' . $fieldName . ' = :id';
        try {
            $query = $this->connection->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_STR);

            $query->execute();

            $result = $query->fetch(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            trigger_error('Wrong SQL: ' . $sql . ' Error: ' . $e->getMessage(), E_USER_ERROR);
        }
        if (empty($result)) {
            return false;
        }

        return $this->translateSingleResultToModel($result);
    }

    /**
     * Find a row with a certain Id
     *
     * @param string $fieldName
     * @param $id
     * @return mixed
     */
    public function findAllById($id, $fieldName = 'id')
    {
        $sql = 'SELECT * from ' . $this->tableName . ' where ' . $fieldName . ' = :id';

        $query = $this->connection->prepare($sql);
        $query->bindParam(':id', $id, \PDO::PARAM_STR);

        $query->execute();

        $results = $query->fetchAll(\PDO::FETCH_ASSOC);

        if (empty($results)) {
            return false;
        }

        $modelCollection = $this->translateResultsToModels($results);

        return $modelCollection;
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
    public function createOrUpdateExcludingWordpressFields($searchField, $searchValue, $object)
    {
        $originalDataBindings = $this->databaseBindings;
        foreach ($this->wordpressDataFields as $wordpressDataField) {
            if (isset($this->databaseBindings[$wordpressDataField])) {
                unset($this->databaseBindings[$wordpressDataField]);
            }
        }

        $response = $this->createOrUpdate($searchField, $searchValue, $object);

        $this->databaseBindings = $originalDataBindings;

        return $response;
    }

    /**
     * Create the the current data object in the database or update it if it already exists
     *
     * @param $searchField
     * @param $searchValue
     * @param $object
     * @return mixed
     */
    public function createOrUpdate($searchField, $searchValue, $object)
    {
        if ($this->idExists($searchValue, $searchField)) {
            return $this->update($searchField, $searchValue, $object);
        }

        return $this->create($object);
    }

    /**
     * Check if an Id exists in the DB already
     *
     * @param $fieldName
     * @param $id
     * @return bool
     */
    public function idExists($id, $fieldName = 'id')
    {
        $sql = 'SELECT * from ' . $this->tableName . ' where ' . $fieldName . ' = :id';

        $query = $this->connection->prepare($sql);
        $query->bindParam(':id', $id, \PDO::PARAM_STR);

        $query->execute();

        $results = $query->fetchAll();

        if (empty($results)) {
            return false;
        }

        return true;
    }

    /**
     * Delete a record via ID
     *
     * @param $id
     * @param string $fieldName
     * @return mixed
     */
    public function deleteById($id, $fieldName = 'id')
    {
        $sql = 'DELETE FROM ' . $this->tableName . ' where ' . $fieldName . ' = :id';
        $query = $this->connection->prepare($sql);

        $query->bindParam(':id', $id, \PDO::PARAM_STR);

        $outcome = $query->execute();

        return $outcome;
    }

    /**
     * Translates a bunch of DB row results to an array of appropriate models
     *
     * @param array $results
     * @return array
     */
    protected function translateResultsToModels(array $results)
    {
        $modelCollection = [];

        foreach ($results as $result) {
            $modelCollection[] = $this->translateSingleResultToModel($result);
        }

        return $modelCollection;
    }

    /**
     * Translates a single result to a model
     *
     * @param array $result
     */
    protected function translateSingleResultToModel(array $result)
    {
        return $this->createModel($result);
    }

    /**
     * Update only certain fields based on a search field (e.g. wordpress_id)
     *
     * @param array $fields Array of column names => values
     * @param $searchField Field column name to update data on
     * @param $searchValue Field value to update data on
     * @return mixed
     */
    public function updateFields(array $fields, $searchField, $searchValue)
    {
        // Build the bindings PDO statement
        $sql = 'UPDATE ' . $this->tableName . ' SET ';
        $count = 1;
        foreach ($fields as $column => $value) {
            $sql .= '`' . $column . '` = :' . $column;
            if ($count < count($fields)) {
                $sql .= ', ';
            } else {
                $sql .= ' ';
            }
            $count++;
        }

        $sql .= 'WHERE ' . $searchField . ' = :searchValue';
        $query = $this->connection->prepare($sql);

        $query->bindParam(':searchValue', $searchValue);
        foreach ($fields as $column => $value) {
            $query->bindParam(':' . $column, $value);
        }

        return $query->execute();
    }
}
