<?php

namespace App\Services\Database;

/**
 * Class DatabaseConnection
 * @package App\Services\Database
 */
class DatabaseConnection
{
    /**
     * @property \PDO;
     */
    public $connection;

    /**
     * S24Database constructor.
     */
    public function __construct()
    {
        $this->connect();
    }

    /**
     * Connect to the database using PDO
     */
    protected function connect()
    {
        $host = getenv('WP_DB_HOST');
        $dbname = getenv('WP_DB_NAME');
        $username = getenv('WP_DB_USER');
        $password = getenv('WP_DB_PASSWORD');

        $this->connection = new \PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password
        );
    }
}
