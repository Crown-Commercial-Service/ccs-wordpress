<?php
namespace CCS\MDMImport;

use App\Services\Database\DatabaseConnection;

class dbManager {
    protected DatabaseConnection $dbConnection;

    public function __construct(DatabaseConnection $dbConnection) {
        $this->dbConnection = $dbConnection;
    }

    public function getLotWordpressIdBySalesforceId(?string $salesforceId) {
        $lotWordpressId = null;

        $sql = "SELECT wordpress_id FROM ccs_lots WHERE salesforce_id = '" . $salesforceId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetch(\PDO::FETCH_ASSOC);

        if(!empty($sqlData['wordpress_id'])) {
            $lotWordpressId = (string) $sqlData['wordpress_id'];
        }

        return $lotWordpressId;
    }

    public function deleteLotPostInWordpress($wordpressId) {
        $sql = " DELETE FROM ccs_15423_posts WHERE ID = '" . $wordpressId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();
    }

    public function updateFrameworkTitleInWordpress() {
        $sql = <<<EOD
            UPDATE ccs_15423_posts p
            INNER JOIN ccs_frameworks f ON f.wordpress_id = p.id
            SET p.post_title = CONCAT(f.rm_number, ': ', f.title)
            WHERE p.post_type = 'framework'
                AND f.rm_number IS NOT NULL
                AND f.title IS NOT NULL
                AND TRIM(f.title) != ''
        EOD;

        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response) {
            print_r($query->errorInfo());
            throw new \Exception('Framework title could not be updated in the database.');
        }

    }

    public function updateLotTitleInWordpress() {
        $sql = <<<EOD
            UPDATE ccs_15423_posts p
            INNER JOIN ccs_lots l ON l.wordpress_id = p.id
            INNER JOIN ccs_frameworks f ON f.salesforce_id = l.framework_id
            SET p.post_title = CONCAT(f.rm_number, ' Lot ', l.lot_number, ': ', l.title)
            WHERE p.post_type = 'lot'
                AND f.rm_number IS NOT NULL
                AND l.lot_number IS NOT NULL
                AND l.title IS NOT NULL
                AND TRIM(l.title) != '';
        EOD;

        $query = $this->dbConnection->connection->prepare($sql);
        $response = $query->execute();

        if (!$response) {
            print_r($query->errorInfo());
            throw new \Exception('Lot title could not be updated in the database.');
        }
    }

    public function getLotSalesforceIdByFrameworkId(?string $frameworkId){
        $sql = "SELECT salesforce_id FROM ccs_lots WHERE framework_id = '" . $frameworkId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetchAll(\PDO::PARAM_STR);

        return count($sqlData) == 0 ? null :  array_column($sqlData, 'salesforce_id');
    }

    public function getLotSuppliersSalesforceIdByLotId(?string $lotId) {
        $sql = "SELECT supplier_id FROM ccs_lot_supplier WHERE lot_id = '" . $lotId . "';";

        $query = $this->dbConnection->connection->prepare($sql);
        $query->execute();

        $sqlData = $query->fetchAll(\PDO::PARAM_STR);

        return count($sqlData) == 0 ? null :  array_column($sqlData, 'supplier_id');
    }
}