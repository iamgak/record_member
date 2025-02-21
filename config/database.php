<?php
$host = "localhost";
$dbname = "todo";
$username = "todo";
$password = 'password';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the member table exists
    $table_name = 'team_members';
    $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
    if ($stmt->rowCount() == 0) {
        // Create the member table
        $sql = "CREATE TABLE IF NOT EXISTS team_members (
                id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(100) NOT NULL,
                mobile VARCHAR(15) NOT NULL,
                email VARCHAR(100) UNIQUE NOT NULL,
                role VARCHAR(50) NOT NULL,
                designation VARCHAR(50) NOT NULL,
                photo TINYINT(1) DEFAULT 0,
                active TINYINT(1) DEFAULT 1,
                married TINYINT(1) DEFAULT 1,
                dob DATE NOT NULL, 
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME DEFAULT NULL);";

        $pdo->exec($sql);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
