<?php

// PHP script to create database and table

use Dotenv\Dotenv;

require_once __DIR__ . '/../vendor/autoload.php';

// Loading the env variables
$dotenv = Dotenv::createImmutable(__DIR__. '/../');
$dotenv->load();


/**
 * Get PDO database connection.
 *
 * @return PDO
 */
function getDbConnection(): PDO
{
    $host = $_ENV['DB_HOST'];
    $dbName = $_ENV['DB_NAME'];
    $username = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];
    $dsn = 'mysql:host=' . $host . ';dbname=' . $dbName;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    return new PDO($dsn, $username, $password, $options);
}

try {
    $pdo = getDbConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Creating database if not already exists
    $sql = "CREATE DATABASE IF NOT EXISTS phonebook";
    $pdo->exec($sql);

    // Selecting the database
    $pdo->exec("USE phonebook");

    // Creating the table if not already exists
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
                id INT AUTO_INCREMENT PRIMARY KEY,
                last_name VARCHAR(50) NOT NULL,
                first_name VARCHAR(50) NOT NULL,
                phone_number VARCHAR(20) NOT NULL,
                INDEX idx_contacts_name (first_name, last_name)
            )";
    $pdo->exec($sql);
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
