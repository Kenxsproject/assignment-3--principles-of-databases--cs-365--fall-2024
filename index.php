<?php
require 'includes/config.php';
require 'includes/helpers.php';

$message = '';
$operation = $_POST['operation'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($operation) {
            case 'search':
                $value = $_POST['search_value'];
                // Show search results
                break;

            case 'update':
                $search_column = $_POST['search_column'] ?? null;
                $search_value = $_POST['search_value'] ?? null;
                $update_column = $_POST['update_column'] ?? null;
                $new_value = $_POST['new_value'] ?? null;

                if (!$search_column || !$search_value || !$update_column || !$new_value) {
                    throw new Exception("All fields are required for update.");
                }

                update($update_column, $new_value, $search_column, $search_value);
                break;

            case 'insert':
                $data = [
                    'website_name' => $_POST['website_name'],
                    'url' => $_POST['url'],
                    'email' => $_POST['email'],
                    'username' => $_POST['username'],
                    'password' => $_POST['password'],
                    'comment' => $_POST['comment'],
                ];
                insert($data['website_name'], $data['url'], $data['email'], $data['username'], $data['password'], $data['comment']);
                break;

            case 'delete':
                $delete_column = $_POST['delete_column'] ?? null;
                $delete_value = $_POST['delete_value'] ?? null;

                if (!$delete_column || !$delete_value) {
                    throw new Exception("Both column and value are required for deletion.");
                }

                delete($delete_column, $delete_value);
                break;

            default:
                echo "<p>Invalid operation.</p>";
        }
    } catch (Exception $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Password Manager</title>
</head>

<body>
    <h1>Password Manager</h1>


    <!-- All Stored Entries Section -->
    <h2>All Stored Entries</h2>
    <?php displayAllEntries(); ?>

    <!-- Search Results Section -->
    <h2>Search Results</h2>
    <?php
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['operation'] === 'search') {
        $search = $_POST['search_value'] ?? '';
        search($search);
    }
    ?>

    <!-- Forms Section -->
    <section>
        <!-- Search Form -->
        <h2>Search Entries</h2>
        <form method="POST">
            <input type="hidden" name="operation" value="search">
            <label for="search_column">Search Field:</label>
            <select id="search_column" name="search_column" required>
                <option value="website_name">Website Name</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
                <option value="username">Username</option>
            </select>
            <label for="search_value">Search Value:</label>
            <input type="text" id="search_value" name="search_value" placeholder="Enter value to search" required>
            <button type="submit">Search</button>
        </form>

        <!-- Update Form -->
        <h2>Update Entry</h2>
        <form method="POST">
            <input type="hidden" name="operation" value="update">
            <label for="match_column">Field to Match:</label>
            <select id="match_column" name="search_column" required>
                <option value="id">ID</option>
                <option value="website_name">Website Name</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
                <option value="username">Username</option>
            </select>
            <label for="match_value">Matching Value:</label>
            <input type="text" id="match_value" name="search_value" placeholder="Value to match" required>
            <label for="update_column">Field to Update:</label>
            <select id="update_column" name="update_column" required>
                <option value="website_name">Website Name</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
                <option value="username">Username</option>
                <option value="password">Password</option>
                <option value="comment">Comment</option>
            </select>
            <label for="new_value">New Value:</label>
            <input type="text" id="new_value" name="new_value" placeholder="Enter new value" required>
            <button type="submit">Update</button>
        </form>

        <!-- Insert Form -->
        <h2>Add New Entry</h2>
        <form method="POST">
            <input type="hidden" name="operation" value="insert">
            <label for="website_name">Website Name:</label>
            <input type="text" id="website_name" name="website_name" placeholder="Enter website name" required>
            <label for="url">URL:</label>
            <input type="text" id="url" name="url" placeholder="Enter URL" required>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" placeholder="Enter email" required>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter password" required>
            <label for="comment">Comment:</label>
            <textarea id="comment" name="comment" placeholder="Enter comments"></textarea>
            <button type="submit">Add Entry</button>
        </form>

        <!-- Delete Form -->
        <h2>Delete Entry</h2>
        <form method="POST">
            <input type="hidden" name="operation" value="delete">
            <label for="delete_column">Field to Match:</label>
            <select id="delete_column" name="delete_column" required>
                <option value="id">ID</option>
                <option value="website_name">Website Name</option>
                <option value="url">URL</option>
                <option value="email">Email</option>
                <option value="username">Username</option>
            </select>
            <label for="delete_value">Value to Match:</label>
            <input type="text" id="delete_value" name="delete_value" placeholder="Value to delete" required>
            <button type="submit">Delete</button>
        </form>
        <!-- Refresh Button -->
        <form method="GET">
            <button type="submit">Refresh Page</button>
        </form>
    </section>
</body>

</html>
