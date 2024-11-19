<?php

require_once 'includes/config.php';

// Define encryption key and initialization vector
function initialize_encryption() {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );
        $db->exec("SET block_encryption_mode = 'aes-256-cbc';");
        $db->exec("SET @key_str = UNHEX(SHA2('mySuperSecretPassphrase', 256));");
        $db->exec("SET @init_vector = RANDOM_BYTES(16);");
    } catch (PDOException $e) {
        echo "<p>Error initializing encryption:</p>";
        echo "<p id='error'>" . $e->getMessage() . "</p>";
        exit;
    }
}

initialize_encryption();

function search($search) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        // Initialize encryption settings
        $db->exec("SET block_encryption_mode = 'aes-256-cbc';");
        $db->exec("SET @key_str = SHA2('mySuperSecretPassphrase', 256);");
        $db->exec("SET @init_vector = '1234567890ABCDEF';"); // Initialization vector as plain string

        // Query to search and decrypt passwords
        $query = "
            SELECT
                w.website_name AS site_name,
                w.url,
                u.email,
                c.username,
                CAST(AES_DECRYPT(c.password, @key_str, @init_vector) AS CHAR) AS decrypted_password,
                c.comment,
                c.created_at
            FROM credentials c
            JOIN websites w ON c.website_id = w.id
            JOIN users u ON c.user_id = u.id
            WHERE w.website_name LIKE :search
                OR w.url LIKE :search
                OR u.email LIKE :search
                OR c.username LIKE :search
        ";

        $statement = $db->prepare($query);
        $statement->execute(['search' => "%{$search}%"]);

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Check if results are empty
        if (empty($results)) {
            echo "<p>No matching results found.</p>";
        } else {
            // Display results in a table
            echo "<table border='1' cellpadding='5' cellspacing='0'>
                    <thead>
                        <tr>
                            <th>Website Name</th>
                            <th>URL</th>
                            <th>Email</th>
                            <th>Username</th>
                            <th>Decrypted Password</th>
                            <th>Comment</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>";
            foreach ($results as $row) {
                echo "<tr>
                        <td>" . htmlspecialchars($row['site_name'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['url'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['email'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['username'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['decrypted_password'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['comment'] ?? '') . "</td>
                        <td>" . htmlspecialchars($row['created_at'] ?? '') . "</td>
                    </tr>";
            }
            echo "</tbody>
                </table>";
        }
    } catch (PDOException $e) {
        echo "<p>Error in <code>search</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}


function insert($website_name, $url, $email, $username, $password, $comment) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        // Initialize encryption settings
        $db->exec("SET block_encryption_mode = 'aes-256-cbc';");
        $db->exec("SET @key_str = SHA2('mySuperSecretPassphrase', 256);");
        $db->exec("SET @init_vector = '1234567890ABCDEF';"); // Initialization vector as plain string

        // Retrieve or insert user_id
        $user_query = $db->prepare("SELECT id FROM users WHERE email = :email");
        $user_query->execute(['email' => $email]);
        $user_id = $user_query->fetchColumn();

        if (!$user_id) {
            $insert_user = $db->prepare("INSERT INTO users (first_name, last_name, email) VALUES ('Default', 'User', :email)");
            $insert_user->execute(['email' => $email]);
            $user_id = $db->lastInsertId();
        }

        // Retrieve or insert website_id
        $website_query = $db->prepare("SELECT id FROM websites WHERE website_name = :website_name AND url = :url");
        $website_query->execute(['website_name' => $website_name, 'url' => $url]);
        $website_id = $website_query->fetchColumn();

        if (!$website_id) {
            $insert_website = $db->prepare("INSERT INTO websites (website_name, url) VALUES (:website_name, :url)");
            $insert_website->execute(['website_name' => $website_name, 'url' => $url]);
            $website_id = $db->lastInsertId();
        }

        // Encrypt the password
        $encryption_query = $db->prepare("
            SELECT AES_ENCRYPT(:password, @key_str, @init_vector) AS encrypted_password
        ");
        $encryption_query->execute([':password' => $password]);
        $encrypted_password_result = $encryption_query->fetch(PDO::FETCH_ASSOC);
        $encrypted_password = $encrypted_password_result['encrypted_password'];

        if (!$encrypted_password) {
            throw new Exception("Password encryption failed.");
        }

        // Insert into credentials
        $query = "
            INSERT INTO credentials (user_id, website_id, username, password, comment)
            VALUES (:user_id, :website_id, :username, :password, :comment)
        ";

        $statement = $db->prepare($query);
        $statement->execute([
            'user_id' => $user_id,
            'website_id' => $website_id,
            'username' => $username,
            'password' => $encrypted_password,
            'comment' => $comment
        ]);

        echo "<p>Insert successful.</p>";

    } catch (PDOException $e) {
        echo "<p>Error in <code>insert</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    } catch (Exception $e) {
        echo "<p>Error in <code>insert</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}



function update($update_column, $new_value, $search_column, $search_value) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        // Allowed columns for safety
        $allowed_columns = ['id', 'website_name', 'url', 'email', 'username', 'password', 'comment'];
        if (!in_array($update_column, $allowed_columns, true) || !in_array($search_column, $allowed_columns, true)) {
            throw new Exception("Invalid column specified for update.");
        }

        // Build the query based on the search column
        if ($search_column === 'id') {
            // Update directly by ID
            $query = "
                UPDATE credentials
                SET {$update_column} = :new_value
                WHERE id = :search_value
            ";
        } else {
            // Update by other fields
            $query = "
                UPDATE credentials
                SET {$update_column} = :new_value
                WHERE {$search_column} = :search_value
            ";
        }

        $statement = $db->prepare($query);
        $statement->execute([
            'new_value' => $new_value,
            'search_value' => $search_value
        ]);

        echo "<p>Update successful. Displaying all entries:</p>";
        displayAllEntries();

    } catch (PDOException $e) {
        echo "<p>Error in <code>update</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
}




function delete($delete_column, $delete_value) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $allowed_columns = ['id', 'username', 'email', 'url', 'website_name'];
        if (!in_array($delete_column, $allowed_columns, true)) {
            throw new Exception("Invalid column specified for deletion.");
        }

        if ($delete_column === 'id') {
            // Delete directly by ID
            $query = "
                DELETE FROM credentials
                WHERE id = :delete_value
            ";
        } else {
            // Delete by other fields
            $query = "
                DELETE FROM credentials
                WHERE {$delete_column} = :delete_value
            ";
        }


        $statement = $db->prepare($query);
        $statement->execute(['delete_value' => $delete_value]);

        echo "<p>Delete successful. Displaying all entries:</p>";

    } catch (PDOException $e) {
        echo "<p>Error in <code>delete</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    } catch (Exception $e) {
        echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
        exit;
    }
}



//shortcut to display all info in the table without having to keep rewriting it.
function displayAllEntries() {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . ";dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $db->exec("SET block_encryption_mode = 'aes-256-cbc';");
        $db->exec("SET @key_str = UNHEX(SHA2('mySuperSecretPassphrase', 256));");
        $db->exec("SET @init_vector = RANDOM_BYTES(16);");

        $query = "
            SELECT
                c.id,
                u.first_name AS user_first_name,
                u.last_name AS user_last_name,
                u.email AS user_email,
                w.website_name,
                w.url,
                c.username,
                c.password AS encrypted_password,
                CAST(AES_DECRYPT(c.password, @key_str, @init_vector) AS CHAR) AS decrypted_password,
                c.comment,
                c.created_at
            FROM credentials c
            JOIN users u ON c.user_id = u.id
            JOIN websites w ON c.website_id = w.id
        ";

        $statement = $db->prepare($query);
        $statement->execute();
        $entries = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($entries)) {
            echo "<p>No entries found.</p>";
        } else {
            echo "<table border='1' cellpadding='5' cellspacing='0'>\n";
            echo "  <thead>\n";
            echo "    <tr>\n";
            echo "      <th>ID</th>\n";
            echo "      <th>User Name</th>\n";
            echo "      <th>User Email</th>\n";
            echo "      <th>Website Name</th>\n";
            echo "      <th>URL</th>\n";
            echo "      <th>Username</th>\n";
            echo "      <th>Encrypted Password</th>\n"; // Added encrypted password
            echo "      <th>Decrypted Password</th>\n";
            echo "      <th>Comment</th>\n";
            echo "      <th>Created At</th>\n";
            echo "    </tr>\n";
            echo "  </thead>\n";
            echo "  <tbody>\n";

            foreach ($entries as $entry) {

                echo "    <tr>\n";
                echo "      <td>" . htmlspecialchars($entry['id'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars(($entry['user_first_name'] ?? '') . ' ' . ($entry['user_last_name'] ?? '')) . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['user_email'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['website_name'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['url'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['username'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['encrypted_password'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['decrypted_password'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['comment'] ?? '') . "</td>\n";
                echo "      <td>" . htmlspecialchars($entry['created_at'] ?? '') . "</td>\n";
                echo "    </tr>\n";
            }

            echo "  </tbody>\n";
            echo "</table>\n";
        }
    } catch (PDOException $e) {
        echo "<p>Error in <code>displayAllEntries</code>:</p>";
        echo "<p id='error'>" . htmlspecialchars($e->getMessage() ?? '') . "</p>";
        exit;
    }
}
