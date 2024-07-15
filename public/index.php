<?php

// This is where the database and table creations are done
// As this is a small application, I have kept this part in index.php for ease
// In case of large applications, I would prefer running the CreateDatabase script separately and not in index.php
require_once '../src/DatabaseSetup.php';

use App\Phonebook\Phonebook;
use App\RateLimitStorage;
use App\DatabaseSetup;

session_start();

$host = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

$databaseSetup = new DatabaseSetup($host, $dbName, $username, $password);
$databaseSetup->createDatabaseAndTable();
$db = $databaseSetup->getDbConnection();

$phonebook = new Phonebook($db);

// Function to clean user input for security
function sanitizeInput(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

// Implementing rate limiting using RateLimitStorage
$rateLimitStorage = new RateLimitStorage();
$identifier = $_SERVER['REMOTE_ADDR'];
$requestsLimit = 100; // Max requests per hour
$timeWindow = 3600; // 1 hour

// Handling POST request to add contact
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add'])) {
    // Checking rate limit for adding contacts
    if (!$rateLimitStorage->isAllowed($identifier . '_add', $requestsLimit, $timeWindow)) {
        http_response_code(429); // HTTP response code 429 - Too Many Requests Status
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }
    $lastName = sanitizeInput($_POST['last_name']);
    $firstName = sanitizeInput($_POST['first_name']);
    $phoneNumber = sanitizeInput($_POST['phone_number']);

    //Ensuring that none of the input fields is empty
    if (empty($lastName) || empty($firstName) || empty($phoneNumber)) {
        echo "<div style='color: red;'>All fields are required.</div>";
    } elseif (!preg_match('/^[a-zA-Z]+$/', $lastName) || !preg_match('/^[a-zA-Z]+$/', $firstName)) {
        echo "<div style='color: red;'>Names must contain only alphabetic characters.</div>";
    } elseif (!preg_match('/^[0-9*()-]*$/', $phoneNumber)) {
        echo "<div style='color: red;'>Invalid phone number. Only numeric values and symbols '*', '-', '(', ')' are allowed.</div>";
    } else {
        // If all validations pass, adding the contact
        $phonebook->addContact($lastName, $firstName, $phoneNumber);
        echo "<div style='color: green;'>Contact added successfully!</div>";
    }
}

$results = [];
$totalResults = 0;
$pageLimit = 10; // 10 entries per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $pageLimit;
$query = isset($_GET['query']) ? sanitizeInput($_GET['query']) : '';

// Handling GET request to search contacts
if (isset($_GET['query'])) {
    $query = sanitizeInput($_GET['query']);
    // Checking rate limit for search contacts
    if (!$rateLimitStorage->isAllowed($identifier, $requestsLimit, $timeWindow)) {
        http_response_code(429); // HTTP response code 429 - Too Many Requests Status
        echo json_encode(['error' => 'Rate limit exceeded']);
        exit;
    }

    // Validating that the search query is not empty and is numeric
    if (empty($query)) {
        echo "<div style='color: red;'>Query cannot be empty.</div>";
    } elseif (!ctype_digit($query)) {
        echo "<div style='color: red;'>Invalid query. Only numeric values are allowed for search.</div>";
    } else {
        // Get search results with pagination
        $searchResults = $phonebook->searchContacts($query, $pageLimit, $offset);
        $results = $searchResults['results'];
        $totalResults = $searchResults['total'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Phonebook</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .pagination {
            margin: 20px 0;
        }
        .pagination a {
            margin: 0 5px;
            padding: 5px 10px;
            border: 1px solid #000;
            text-decoration: none;
        }
    </style>
</head>
<body>
<h1>Phonebook</h1>
<form method="post">
    <h2>Add Contact</h2>
    <label>Last Name: <input type="text" name="last_name" required></label><br>
    <label>First Name: <input type="text" name="first_name" required></label><br>
    <label>Phone Number: <input type="text" name="phone_number" pattern="[0-9*()-]*" title="Only numbers and symbols '*', '-', '(', ')' are allowed" required></label><br>
    <button type="submit" name="add">Add Contact</button>
</form>

<form method="get">
    <h2>Search Contacts</h2>
    <label>Query: <input type="text" name="query" pattern="[0-9]*" title="Only numbers are allowed" required></label>
    <button type="submit">Search</button>
</form>

<?php if (!empty($results)): ?>
    <h2>Results</h2>
    <table>
        <tr>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Phone Number</th>
        </tr>
        <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['phone_number']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
    <div class="pagination">
        <?php
        $totalPages = ceil($totalResults / $pageLimit);
        if ($page > 1): ?>
            <a href="?query=<?= urlencode($query) ?>&page=<?= $page - 1 ?>">Previous</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?query=<?= urlencode($query) ?>&page=<?= $i ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?query=<?= urlencode($query) ?>&page=<?= $page + 1 ?>">Next</a>
        <?php endif; ?>
    </div>
<?php endif; ?>
</body>
</html>
