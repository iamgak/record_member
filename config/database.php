<?php
require_once '../define.php';
$host = HOSTNAME;
$dbname = DB_NAME;
$username = USER_NAME;
$password = PASSW;

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $table_name = 'atl_team_members';
    $stmt = $pdo->query("SHOW TABLES LIKE '$table_name'");
    if ($stmt->rowCount() == 0) {
        $sql = "CREATE TABLE `$table_name` (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    mobile VARCHAR(15) NOT NULL,
                    email VARCHAR(100) NOT NULL,
                    dob DATE NOT NULL, 
                    address TEXT NOT NULL,
                    designation INT NOT NULL,
                    role INT NOT NULL,
                    gender TINYINT(1) NOT NULL,
                    marital_status TINYINT(2) DEFAULT 1,
                    account_status TINYINT(2) DEFAULT 1,
                    is_deleted TINYINT(1) DEFAULT 0,
                    version INT DEFAULT 1,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    updated_at DATETIME DEFAULT NULL
                );";
        $pdo->exec($sql);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
