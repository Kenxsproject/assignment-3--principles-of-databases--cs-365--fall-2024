<?php
define("DBHOST", "your_host");
define("DBNAME", "your_db");
define("DBUSER", "your_user");
define("DBPASS", "your_password");

function search($search) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $select_query = "SELECT * FROM passwords WHERE url LIKE :search OR username LIKE :search";
        $statement = $db -> prepare($select_query);
        $statement -> execute(['search' => "%{$search}%"]);

        $results = $statement->fetchAll();

        if (count($results) == 0) {
            return 0;
        } else {
            echo "<table>\n";
            echo "  <thead>\n";
            echo "    <tr>\n";
            echo "      <th>Website Name</th>\n";
            echo "      <th>URL</th>\n";
            echo "      <th>Username</th>\n";
            echo "      <th>Email</th>\n";
            echo "      <th>Comment</th>\n";
            echo "    </tr>\n";
            echo "  </thead>\n";
            echo "  <tbody>\n";

            foreach ($results as $row) {
                echo "    <tr>\n";
                echo "      <td>" . htmlspecialchars($row['website_name']) . "</td>\n";
                echo "      <td>" . htmlspecialchars($row['url']) . "</td>\n";
                echo "      <td>" . htmlspecialchars($row['username']) . "</td>\n";
                echo "      <td>" . htmlspecialchars($row['email']) . "</td>\n";
                echo "      <td>" . htmlspecialchars($row['comment']) . "</td>\n";
                echo "    </tr>\n";
            }

            echo "  </tbody>\n";
            echo "</table>\n";
        }
    } catch(PDOException $e) {
        echo "<p>Error in <code>search</code>: " . $e->getMessage() . "</p>\n";
        exit;
    }
}

function insert($website_name, $url, $username, $email, $password, $comment) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $key = "your_encryption_key";
        $init_vector = random_bytes(16);

        $query = "INSERT INTO passwords (website_name, url, username, email, password, comment)
                  VALUES (:website_name, :url, :username, :email, AES_ENCRYPT(:password, :key, :init_vector), :comment)";
        $statement = $db->prepare($query);
        $statement->execute([
            'website_name' => $website_name,
            'url'          => $url,
            'username'     => $username,
            'email'        => $email,
            'password'     => $password,
            'key'          => $key,
            'init_vector'  => $init_vector,
            'comment'      => $comment
        ]);
    } catch(PDOException $e) {
        echo "<p>Error in <code>insert</code>: " . $e->getMessage() . "</p>\n";
        exit;
    }
}

function update($update_field, $new_value, $condition_field, $condition_value) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $query = "UPDATE passwords SET {$update_field} = :new_value WHERE {$condition_field} = :condition_value";
        $statement = $db->prepare($query);
        $statement->execute([
            'new_value'       => $new_value,
            'condition_value' => $condition_value
        ]);
    } catch(PDOException $e) {
        echo "<p>Error in <code>update</code>: " . $e->getMessage() . "</p>\n";
        exit;
    }
}

function delete($condition_field, $condition_value) {
    try {
        $db = new PDO(
            "mysql:host=" . DBHOST . "; dbname=" . DBNAME . ";charset=utf8",
            DBUSER,
            DBPASS
        );

        $query = "DELETE FROM passwords WHERE {$condition_field} = :condition_value";
        $statement = $db->prepare($query);
        $statement->execute(['condition_value' => $condition_value]);
    } catch(PDOException $e) {
        echo "<p>Error in <code>delete</code>: " . $e->getMessage() . "</p>\n";
        exit;
    }
}
?>
