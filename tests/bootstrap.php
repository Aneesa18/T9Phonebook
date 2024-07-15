<?php

use Dotenv\Dotenv;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Loading environment variables from .env file
if (file_exists(__DIR__ . '/../.env')) {
    echo "Found .env file\n";
    $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
} else {
    echo "No .env file found\n";
}
