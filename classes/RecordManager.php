<?php
class RecordManager
{
    private $pdo, $table_name;

    public function __construct()
    {
        require_once '../config/database.php';
        $this->pdo = $pdo;
        $this->table_name = 'assign_mem';
    }

    public function createRecord($data)
    {
        $sql = "INSERT INTO " . $this->table_name . " (name, parent_id) VALUES (:name, :parent_id)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        if (empty($data['parent_id'])) {
            $stmt->bindValue(':parent_id', null, \PDO::PARAM_INT);
        } else {
            $stmt->bindParam(':parent_id', $data['parent_id']);
        }
        $stmt->execute();
        return $this->pdo->lastInsertId();
    }

    public function readRecords($parentId = null)
    {
        $sql = "SELECT * FROM " . $this->table_name;
        if ($parentId !== null) {
            $sql .= " WHERE parent_id = :parent_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':parent_id', $parentId);
        } else {
            $stmt = $this->pdo->prepare($sql);
        }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}
