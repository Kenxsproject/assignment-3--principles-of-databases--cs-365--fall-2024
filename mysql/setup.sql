
DROP DATABASE IF EXISTS student_passwords;


CREATE DATABASE student_passwords;
USE student_passwords;


CREATE USER IF NOT EXISTS 'passwords_user'@'localhost';


GRANT ALL PRIVILEGES ON student_passwords.* TO 'passwords_user'@'localhost';

SET block_encryption_mode = 'aes-256-cbc';
SET @key_str = UNHEX(SHA2('mySuperSecretPassphrase', 256));
SET @init_vector = '1234567890ABCDEF';

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS websites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    website_name VARCHAR(255) NOT NULL,
    url VARCHAR(255) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE credentials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    website_id INT NOT NULL,
    username VARCHAR(150),
    password VARBINARY(255),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (website_id) REFERENCES websites(id) ON DELETE CASCADE
);


INSERT INTO users (first_name, last_name, email)
VALUES
    ('Ken', 'David', 'kenD@gmail.com'),
    ('Thomas', 'Smith', 'Tom.smith@gmail.com'),
    ('Bruce', 'Wayne', 'worldsfinest@gmail.com'),
    ('Jason', 'Todd', 'SecondSon@gmail.com'),
    ('Dick', 'Grayson', 'FlyingGrayson@gmail.com'),
    ('Tim', 'Drake', 'TheDetective@gmail.com'),
    ('Damian', 'Wayne', 'DemonsChild@gmail.com'),
    ('Clark', 'Kent', 'ManOfSteel@gmail.com'),
    ('Hal', 'Jordan', 'BestPilot@gmail.com'),
    ('Barry', 'Allen', 'StarLabsBA@gmail.com');

INSERT INTO websites (website_name, url)
VALUES
    ('Google', 'https://google.com'),
    ('Facebook', 'https://facebook.com'),
    ('SnapChat', 'http://web.snapchat.com/'),
    ('Discord', 'https://discord.com/login'),
    ('Instagram', 'https://www.instagram.com/'),
    ('Twitter(X)', 'https://x.com/login'),
    ('FasFa', 'https://studentaid.gov/'),
    ('WashingtonPost', 'http://www.washingtonpost.com/'),
    ('Youtube', 'http://www.youtube.com/'),
    ('TikTok', 'http://www.tiktok.com/');

INSERT INTO credentials (user_id, website_id, username, password, comment)
VALUES
    (1, 1, 'KenD', AES_ENCRYPT('EzP@sswrd', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'Google account pass'),
    (2, 2, 'Tomsmith', AES_ENCRYPT('MyF@cePass', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'Facebook account password'),
    (3, 3, 'NotBatman', AES_ENCRYPT('NotB@tm@n', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'SnapChat account'),
    (4, 4, 'NotRedHood', AES_ENCRYPT('JokersDeath', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'Discord account'),
    (5, 5, 'NotNightWing', AES_ENCRYPT('FlyingGrayson', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'Instagram account'),
    (6, 6, 'NotRedRobin', AES_ENCRYPT('TheDrake', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'Twitter account'),
    (7, 7, 'NotRobin', AES_ENCRYPT('B@tSon', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'FasFa account'),
    (8, 8, 'NotSuperman', AES_ENCRYPT('Smallville', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'WashingtonPost account'),
    (9, 9, 'NotGreenLantern', AES_ENCRYPT('Willpower', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'YouTube account'),
    (10, 10, 'NotTheFlash', AES_ENCRYPT('SpeedForce', UNHEX(SHA2('mySuperSecretPassphrase', 256)), '1234567890ABCDEF'), 'TikTok account');
