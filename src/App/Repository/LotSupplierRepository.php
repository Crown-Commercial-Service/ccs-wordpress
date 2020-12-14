<?php

namespace App\Repository;

use App\Model\LotSupplier;

class LotSupplierRepository extends AbstractRepository
{

    protected $databaseBindings = [
      'lot_id'          => ':lot_id',
      'supplier_id'     => ':supplier_id',
      'contact_name'    => ':contact_name',
      'contact_email'   => ':contact_email',
      'website_contact' => ':website_contact',
      'trading_name'    => ':trading_name',
      'guarantor_id'    => ':guarantor_id',
    ];

    /**
     * Database table name
     *
     * @var string
     */
    protected $tableName = 'ccs_lot_supplier';

    public function createModel($data = null)
    {
        return new LotSupplier($data);
    }

    /**
     * @param \App\Model\LotSupplier $lotSupplier
     * @return mixed
     */
    public function create(LotSupplier $lotSupplier)
    {
        // Build the bindings PDO statement
        $columns = implode(", ", array_keys($this->databaseBindings));
        $fieldParams = implode(", ", array_values($this->databaseBindings));

        $sql = 'INSERT INTO ' . $this->tableName . ' (' . $columns . ') VALUES(' . $fieldParams . ')';

        $query = $this->connection->prepare($sql);

        $query = $this->bindValues($this->databaseBindings, $query, $lotSupplier);

        return $query->execute();
    }

    /**
     * @param $searchField
     * @param $searchValue
     * @param \App\Model\LotSupplier $lotSupplier
     * @return mixed
     */
    public function update($searchField, $searchValue, LotSupplier $lotSupplier)
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

        $query = $this->bindValues($this->databaseBindings, $query, $lotSupplier);

        return $query->execute();
    }


    /**
     * Bind PDO Values
     *
     * @param $databaseBindings
     * @param $query
     * @param LotSupplier $lotSupplier
     * @return mixed
     */
    protected function bindValues($databaseBindings, $query, LotSupplier $lotSupplier)
    {
        if (isset($databaseBindings['lot_id'])) {
            $lotId = $lotSupplier->getLotId();
            $query->bindParam(':lot_id', $lotId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['supplier_id'])) {
            $supplierId = $lotSupplier->getSupplierId();
            $query->bindParam(':supplier_id', $supplierId, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['contact_name'])) {
            $contactName = $lotSupplier->getContactName();
            $query->bindParam(':contact_name', $contactName, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['contact_email'])) {
            $contactEmail = $lotSupplier->getContactEmail();
            $query->bindParam(':contact_email', $contactEmail, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['website_contact'])) {
            $websiteContact = $lotSupplier->isWebsiteContact();
            $query->bindParam(':website_contact', $websiteContact, \PDO::PARAM_BOOL);
        }

        if (isset($databaseBindings['trading_name'])) {
            $tradingName = $lotSupplier->getTradingName();
            $query->bindParam(':trading_name', $tradingName, \PDO::PARAM_STR);
        }

        if (isset($databaseBindings['guarantor_id'])) {
            $guarantor_id = $lotSupplier->getGuarantorId();
            $query->bindParam(':guarantor_id', $guarantor_id, \PDO::PARAM_STR);
        }

        return $query;
    }

    /**
     * Find a row by supplying a lot ID and supplier ID
     *
     * @param $lotId
     * @param $supplierId
     * @return bool
     */
    public function findByLotIdAndSupplierId($lotId, $supplierId)
    {
        $sql = 'SELECT * from ' . $this->tableName . ' where lot_id = :lot_id AND supplier_id = :supplier_id';

        try {
            $query = $this->connection->prepare($sql);
            $query->bindParam(':lot_id', $lotId, \PDO::PARAM_STR);
            $query->bindParam(':supplier_id', $supplierId, \PDO::PARAM_STR);

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
}
