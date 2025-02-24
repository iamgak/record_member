<?php

namespace App\Class;

use PDO;

class TeamRecordManager
{
    private $pdo, $table_name, $allowed_file_max_size, $allowed_mime_types;

    public function __construct($pdo)
    {
        // require_once '../config/database.php';
        $this->pdo = $pdo;
        $this->table_name = 'atl_team_members';
        $this->allowed_file_max_size = 1 * 1024;
        $this->allowed_mime_types = 'jpg|jpeg';
    }

    public function createRecord(array $data): int
    {
        $sql = "INSERT INTO {$this->table_name} (name, mobile, email, role, designation, gender, address, marital_status, dob, account_status) VALUES (:name, :mobile, :email, :role, :designation, :gender, :address, :marital_status, :dob, :account_status)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':designation', $data['designation']);
        $stmt->bindParam(':marital_status', $data['marital_status']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':account_status', $data['account_status']);
        $stmt->execute();

        $id = (int)$this->pdo->lastInsertId();

        if (!empty($_FILES['upload'])) {
            $target_dir = "uploads/profile/";
            $target_file = $target_dir . $id . '.jpg';
            move_uploaded_file($_FILES['upload']['tmp_name'], $target_file);
        }
        return $id;
    }

    public function updateRecord(array $data): int
    {
        $sql = "UPDATE $this->table_name SET name = :name, mobile = :mobile, address = :address, email = :email, gender = :gender, role = :role, designation = :designation, marital_status = :marital_status, dob = :dob , updated_at = NOW(), account_status = :account_status, version = version + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':gender', $data['gender']);
        $stmt->bindParam(':designation', $data['designation']);
        $stmt->bindParam(':marital_status', $data['marital_status']);
        $stmt->bindParam(':account_status', $data['account_status']);
        $stmt->bindParam(':address', $data['address']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':id', $data['edit-id']);
        $stmt->execute();
        if (!empty($_FILES['upload'])) {
            $target_dir = "uploads/profile/";
            $target_file = $target_dir . $data['edit-id'] . '.jpg';
            move_uploaded_file($_FILES['upload']['tmp_name'], $target_file);
        }

        return $stmt->rowCount();
    }

    public function validId($id)
    {
        if (empty($id) || !preg_match('/^\d+$/', $id)) {
            return 'Invalid Profile Id';
        }

        $sql = "SELECT 1 FROM $this->table_name WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return empty($stmt->fetch()) ? 'Invalid Profile Id' : '';
    }

    public function deleteRecordsById($id)
    {
        $sql = "DELETE FROM {$this->table_name} WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount();
    }

    public function deactivateRecordsById(int $id, bool $active): array
    {
        $sql = "UPDATE {$this->table_name} SET account_status = :account_status WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'account_status' => $active]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function readRecordById($id): array
    {
        $sql = "SELECT * FROM $this->table_name WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readRecordAll(): array
    {
        $sql = "SELECT a.id, a.name, email, a.mobile, d.name AS designation, r.name AS role, account_status 
                FROM `$this->table_name` AS a
                INNER JOIN atl_type_role AS r ON r.id = `a`.`role`
                INNER JOIN atl_type_designation AS d ON d.id = `a`.`designation`
                ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function seedRecords(): void
    {
        $data = [
            [
                'name' => 'Bob Johnson',
                'account_status' => 1,
                'mobile' => '5678901234',
                'email' => 'bob.johnson@example.com',
                'role' => 2,
                'designation' => 1,
                'gender' => 1,
                'marital_status' => 1,
                'address' => 'Warsaw',
                'dob' => '1975-11-08'
            ],
            [
                'name' => 'Alice Williams',
                'mobile' => '4321098765',
                'email' => 'alice.williams@example.com',
                'role' => 2,
                'account_status' => 2,
                'designation' => 1,
                'gender' => 2,
                'marital_status' => 1,
                'dob' => '1992-09-01',
                'address' => 'Delhi',

            ],
            [
                'name' => 'Tom Davis',
                'mobile' => '9012345678',
                'email' => 'tom.davis@example.com',
                'gender' => 1,
                'role' => 2,
                'designation' => 2,
                'account_status' => 2,
                'marital_status' => 1,
                'dob' => '1980-04-30',
                'address' => 'Berlin',

            ],
            [
                'name' => 'John Doe',
                'mobile' => '1234567890',
                'email' => 'john.doe@example.com',
                'address' => 'Warsaw',
                'role' => 1,
                'designation' => 1,
                'gender' => 1,
                'marital_status' => 1,
                'account_status' => 1,
                'dob' => '1985-06-15',
                'id' => 1
            ],
            [
                'name' => 'Jane Doe',
                'mobile' => '9876543210',
                'email' => 'jane.doe@example.com',
                'address' => 'New York',
                'role' => 2,
                'designation' => 3,
                'gender' => 2,
                'marital_status' => 1,
                'account_status' => 1,
                'dob' => '1990-03-20',
                'id' => 2
            ],
            [
                'name' => 'Bob Smith',
                'mobile' => '5551234567',
                'email' => 'bob.smith@example.com',
                'address' => 'London',
                'role' => 2,
                'designation' => 2,
                'gender' => 1,
                'account_status' => 1,
                'marital_status' => 2,
                'dob' => '1980-09-10',
                'id' => 3
            ]
        ];

        $stmt = $this->pdo->query("SHOW TABLES LIKE '$this->table_name'");
        if ($stmt->rowCount() == 0) {
            $sql = "CREATE TABLE `$this->table_name` (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(100) NOT NULL,
                        mobile VARCHAR(15) NOT NULL,
                        email VARCHAR(100) UNIQUE NOT NULL,
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
            $this->pdo->exec($sql);
        }
        foreach ($data as $record) {
            $this->createRecord($record);
        }

        echo "Dummy data seeded successfully.\n";
    }

    public function validateRecords($type = "")
    {
        $validationErrors = [];

        $requiredFields = ['name', 'mobile', 'email', 'gender', 'role', 'designation', 'dob', 'address', 'account_status', 'marital_status'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                return "Empty $field Field";
            }
        }

        if (!preg_match('/^\d{10}$/', $_POST['mobile'])) {
            return 'Invalid mobile number';
        }

        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email address';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
            return 'Invalid date of birth format (YYYY-MM-YYYY)';
        }

        if (!$this->validDataSelected('atl_type_marital_status', $_POST['marital_status'])) {
            return 'Marital status must be from dropdown';
        }

        if (!$this->validDataSelected('atl_type_account_status', $_POST['account_status'])) {
            return 'Account status must be from dropdown';
        }

        if (!$this->validDataSelected('atl_type_role', $_POST['role'])) {
            return 'Role from the dropdown only';
        }

        if (!$this->validDataSelected('atl_type_designation', $_POST['designation'])) {
            return 'Designation must be from dropdown';
        }
        
        if (!$this->validDataSelected('atl_type_gender', $_POST['gender'])) {
            return 'Gender must be from dropdown';
        }

        // if (!in_array($_POST['designation'], [1, 2, 3, 4, 5, 6])) {
        //     return 'Select Designation from dropdown only';
        // }

        // if (!in_array($_POST['gender'], [1, 2, 3])) {
        //     return 'Select Gender from dropdown only';
        // }


        // if (!in_array($_POST['role'], [1, 2, 3])) {
        //     return 'Select Gender from dropdown only';
        // }

        if (!$this->validDesignation($_POST['role'], $_POST['designation'])) {
            return 'Incorrect Designation Selected';
        }

        return;
    }

    public function emailExist($email)
    {
        $sql = "SELECT 1 FROM $this->table_name WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        return $stmt->rowCount() ? true : false;
    }


    public function validDataSelected($table_name, $id)
    {
        $sql = "SELECT 1 FROM `$table_name` WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() ? true : false;
    }

    public function validDesignation($role_id, $designation_id)
    {
        $sql = "SELECT 1 FROM `atl_type_designation` WHERE id = :id AND role_id = :role_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['role_id' => $role_id, 'id' => $designation_id]);
        return $stmt->rowCount() ? true : false;
    }

    public function emailChanged($email, $id)
    {
        $sql = "SELECT 1 FROM $this->table_name WHERE email = :email AND id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email, 'id' => $id]);
        return $stmt->rowCount() ? true : false;
    }

    public function fetchDesignationByID($id)
    {
        $sql = "SELECT id, name FROM `atl_type_designation` WHERE role_id = $id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function validateImage()
    {
        if (isset($_FILES['upload']) && $_FILES['upload']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['upload'];
            if (!preg_match("~^" . $this->allowed_mime_types . "$~", mime_content_type($file['tmp_name']))) {
                return 'Uploaded file should be image and jpg format only';
            } else if ($this->allowed_file_max_size >= filesize($file['tmp_name'])) {
                return 'Uploaded file should be image and less than ' . $this->allowed_file_max_size;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if (!preg_match("~^" . $this->allowed_mime_types . "$~", finfo_file($finfo, $file['tmp_name']))) {
                return 'Uploaded file should be image and jpg format only';
            }
        }

        return;
    }
}
