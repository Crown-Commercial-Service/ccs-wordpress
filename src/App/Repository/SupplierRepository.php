<?php

namespace App\Repository;

use App\Model\Supplier;

class SupplierRepository extends AbstractRepository
{

    protected $databaseBindings = [
      'salesforce_id' => ':salesforce_id',
      'duns_number'   => ':duns_number',
      'name'          => ':name',
      'phone_number'  => ':phone_number',
      'street'        => ':street',
      'city'          => ':city',
      'country'       => ':country',
      'postcode'      => ':postcode',
      'website'       => ':website',
      'trading_name'  => ':trading_name',
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

        return $query;
    }


}