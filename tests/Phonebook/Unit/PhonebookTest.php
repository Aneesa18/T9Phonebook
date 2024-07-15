<?php

namespace Phonebook\Unit;

use App\Phonebook\Phonebook;
use PDO;
use PHPUnit\Framework\TestCase;

class PhonebookTest extends TestCase
{
    private PDO $pdo;
    private Phonebook $phonebook;

    protected function setUp(): void
    {
        // Using the env variables
        $dbHost = $_ENV['DB_HOST'];
        $dbName = $_ENV['TEST_DB_NAME'];
        $dbUser = $_ENV['DB_USER'];
        $dbPass = $_ENV['DB_PASS'];
        // Initializing the test environment
        $this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Creating the test database and schema
        $this->createDatabase();
        $this->createSchema();

        $this->phonebook = new Phonebook($this->pdo);
        parent::setUp();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->pdo->exec('DROP TABLE IF EXISTS contacts');
        $this->pdo->exec('DROP DATABASE IF EXISTS phonebooks_test');
        parent::tearDown();
    }

    private function createDatabase(): void
    {
        $this->pdo->exec('CREATE DATABASE IF NOT EXISTS phonebook_test');
    }

    private function createSchema(): void
    {
        $this->pdo->exec('USE phonebook_test');
        $this->pdo->exec('CREATE TABLE IF NOT EXISTS contacts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            last_name VARCHAR(50) NOT NULL,
            first_name VARCHAR(50) NOT NULL,
            phone_number VARCHAR(20) NOT NULL,
            INDEX idx_contacts_name (first_name, last_name)
        )');
    }

    // Testing the successful add contact case
    public function testAddContact()
    {
        $result = $this->phonebook->addContact('Depp', 'Johnny', '1234567890');
        $this->assertTrue($result);

        // Asserting that the contact is added to the database
        $stmt = $this->pdo->query('SELECT * FROM contacts');
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEquals('Depp', $contacts[0]['last_name']);
        $this->assertEquals('Johnny', $contacts[0]['first_name']);
        $this->assertEquals('1234567890', $contacts[0]['phone_number']);
    }

    // Testing add contact with an invalid input
    public function testAddContactInvalidPhoneNumber()
    {
        // Adding contact with an invalid phone number
        $result = $this->phonebook->addContact('Depp', 'Johnny', 'invalid-phone-number');

        // Asserting that the contact was not added successfully
        $this->assertFalse($result);

        // Asserting that no contact exists in the database
        $stmt = $this->pdo->query('SELECT * FROM contacts');
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->assertEmpty($contacts);
    }

    // Testing that only the entries that match the search query are in the result
    public function testSearchContacts()
    {
        // Adding two contacts
        // One contact matches the search pattern and the other does not
        $this->phonebook->addContact('Anny', 'Bai', '1234567890');
        $this->phonebook->addContact('Shabz', 'Sharma', '1234567809');

        // Searching for contacts using search pattern
        $results = $this->phonebook->searchContacts('266',10,0);

        $this->assertNotEmpty($results['results']);
        // Asserting that only one matching contact is found for the search query
        $this->assertEquals(1, $results['total']);
        $this->assertEquals('Anny', $results['results'][0]['last_name']);
        $this->assertEquals('Bai', $results['results'][0]['first_name']);
        $this->assertEquals('1234567890', $results['results'][0]['phone_number']);
    }

    // Testing that the result is empty in case no entries match the search query
    public function testSearchContactsNoResults()
    {
        // Adding a contact that does not match the search query
        $this->phonebook->addContact('Shabz', 'Sharm', '1234567809');

        $results = $this->phonebook->searchContacts('266',10,0);

        // Asserting that no results are returned when none of the contacts match the search query
        $this->assertEmpty($results['results']);
        $this->assertEquals(0, $results['total']);
    }
}
