<?php

namespace App\Class;

use PDO;

class Type
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function readTable(string $table_name): array
    {
        // Sanitize the table name to prevent SQL injection
        $table_name = filter_var($table_name, FILTER_SANITIZE_STRING);

        $sql = "SELECT * FROM `atl_type_$table_name`";
        $stmt = $this->pdo->prepare($sql);

        try {
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            // Handle the exception (e.g., log the error, rethrow, etc.)
            error_log('Database error: ' . $e->getMessage());
            return [];
        }
    }
}
