DROP DATABASE IF EXISTS student_passwords;

CREATE DATABASE student_passwords;

USE student_passwords;
-- Creating a user--
CREATE USER IF NOT EXISTS 'passwords_user'@'localhost';

-- Grant all privileges on the new database to the user--
GRANT ALL PRIVILEGES ON student_passwords.* TO 'passwords_user'@'localhost';

SET block_encryption_mode = 'aes-256-cbc';
SET @key_str = UNHEX(SHA2('mySuperSecretPassphrase', 512)); -- Generate a secure encryption key
SET @init_vector = RANDOM_BYTES(16); -- Initialization vector

-- Create tables--
CREATE TABLE websites (
    website_id INT AUTO_INCREMENT PRIMARY KEY,
    website_name VARCHAR(255) NOT NULL,
    url VARCHAR(255) NOT NULL
);

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE
);

CREATE TABLE passwords (
    password_id INT AUTO_INCREMENT PRIMARY KEY,
    password VARBINARY(255) NOT NULL,
    comment TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    website_id INT,
    user_id INT,
    FOREIGN KEY (website_id) REFERENCES websites(website_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
