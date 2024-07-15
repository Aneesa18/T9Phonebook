<?php

namespace App\Phonebook;

use PDO;
use PDOException;

/**
 * Phonebook class for managing contacts.
 */
class Phonebook {
    private PDO $db;

    /**
     * Constructor to initialize database connection.
     *
     * @param PDO $db Database connection
     */
    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * Add a contact to the phonebook.
     *
     * @param string $lastName Last name of the contact
     * @param string $firstName First name of the contact
     * @param string $phoneNumber Phone number of the contact // Phone number is treated as a string to preserve any initial zeros
     * @return bool True on success, false on failure
     */
    public function addContact(string $lastName, string $firstName, string $phoneNumber): bool {
        // Ensuring the phone number contains only digits and symbols including '+','-','(',')'
        if (!preg_match('/^[0-9+\-() ]+$/', $phoneNumber)) {
            return false; // Invalid phone number
        }

        // Sanitizing the inputs
        $lastName = htmlspecialchars(trim($lastName), ENT_QUOTES, 'UTF-8');
        $firstName = htmlspecialchars(trim($firstName), ENT_QUOTES, 'UTF-8');
        $phoneNumber = htmlspecialchars(trim($phoneNumber), ENT_QUOTES, 'UTF-8');

        try {
            $stmt = $this->db->prepare("INSERT INTO contacts (last_name, first_name, phone_number) VALUES (:lastName, :firstName, :phoneNumber)");
            $stmt->bindParam(':lastName', $lastName, PDO::PARAM_STR);
            $stmt->bindParam(':firstName', $firstName, PDO::PARAM_STR);
            $stmt->bindParam(':phoneNumber', $phoneNumber, PDO::PARAM_STR);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log('Failed to add contact: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Search contacts using T9 search.
     *
     * @param string $query The T9 search query
     * @return array The search results
     */
    public function searchContacts(string $query, int $pageLimit, int $offset): array {
        // Ensuring the search query contains only digits
        if (!ctype_digit($query)) {
            return [];
        }

        $t9 = new T9search();
        $possibleNames = $t9->getPossibleNames($query);

        if (empty($possibleNames)) {
            return [];
        }

        // Constructing the SQL query dynamically using parameterized queries
        $likeClauses = [];
        $likeClauses[] = "first_name LIKE ? OR last_name LIKE ?";
        $sql = "SELECT SQL_CALC_FOUND_ROWS * FROM contacts WHERE " . implode(' OR ', $likeClauses) . " LIMIT $pageLimit OFFSET $offset";

        $params = [];
        foreach ($possibleNames as $name) {
            $params[] = $name;
            $params[] = $name;
        }

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Get the total number of results matching the search query
            $totalStmt = $this->db->query("SELECT FOUND_ROWS()");
            $totalResults = $totalStmt->fetchColumn();

            // Sanitize output
            $sanitizedResults = array_map(function($row) {
                return array_map('htmlspecialchars', $row);
            }, $results);
            return ['results' => $sanitizedResults, 'total' => $totalResults];

        } catch (PDOException $e) {
            error_log('Failed to search contacts: ' . $e->getMessage());
            return ['results' => [], 'total' => 0];
        }
    }
}
