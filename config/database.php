<?php
$host = "localhost";
$dbname = "iamgak";
$username = "iamgak";
$password = 'Pa$$word123';


try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if the member table exists
    $table_name = 'assign_mem';
    $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
    if ($stmt->rowCount() == 0) {
        // Create the member table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            parent_id INT DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);

        // Insert some sample data
        $sql = "INSERT INTO $table_name (name, parent_id) VALUES
            ('Prem ', NULL),
            ('Rabindra', 1),
            ('Subash', 1),
            ('Lal', 2)";
        $pdo->exec($sql);
    }
        
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
