<?php

namespace App;

use Dotenv\Dotenv;
use PDOException;
use PDO;

require_once __DIR__ . '/../vendor/autoload.php';

// Loading the env variables
$dotenv = Dotenv::createImmutable(__DIR__. '/../');
$dotenv->load();

/**
 * DatabaseSetup class for creating and getting database connection.
 */
class DatabaseSetup
{
    protected string $host;
    protected string $dbName;
    protected string $username;
    protected string $password;

    public function __construct(string $host, string $dbName, string $username, string $password)
    {
        $this->host = $host;
        $this->dbName = $dbName;
        $this->username = $username;
        $this->password = $password;
    }

    public function createDatabaseAndTable(): void
    {
        try {
            $pdo = new PDO("mysql:host=$this->host", $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Creating database if not already exists
            $sqlCreateDb = "CREATE DATABASE IF NOT EXISTS $this->dbName";
            $pdo->exec($sqlCreateDb);

            // Selecting the database
            $pdo->exec("USE $this->dbName");

            // Creating the table if not already exists
            $sqlCreateTable = "CREATE TABLE IF NOT EXISTS contacts (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        last_name VARCHAR(50) NOT NULL,
                        first_name VARCHAR(50) NOT NULL,
                        phone_number VARCHAR(20) NOT NULL,
                        INDEX idx_contacts_name (first_name, last_name)
                    )";
            $pdo->exec($sqlCreateTable);
        } catch(PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getDbConnection(): PDO
    {
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbName;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        try {
            return new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            throw new PDOException("Connection failed: " . $e->getMessage());
        }
    }
}
