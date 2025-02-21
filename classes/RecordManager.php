<?php
class TeamRecordManager
{
    private $pdo, $table_name, $allowed_file_max_size, $allowed_mime_types;

    public function __construct()
    {
        require_once '../config/database.php';
        $this->pdo = $pdo;
        $this->table_name = 'team_members';
        $this->allowed_file_max_size = 1 * 1024;
        $this->allowed_mime_types = 'jpg|jpeg';
    }

    public function createRecord(array $data): int
    {
        $sql = "INSERT INTO {$this->table_name} (name, mobile, email, role, designation, married, dob) VALUES (:name, :mobile, :email, :role, :designation, :married, :dob)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':designation', $data['designation']);
        $stmt->bindParam(':married', $data['married']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->execute();

        $id = (int)$this->pdo->lastInsertId();

        if (!empty($_FILES['upload'])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . $id . '.jpg';
            echo $target_file;
            move_uploaded_file($_FILES['upload']['tmp_name'], $target_file);
        }
        return $id;
    }

    public function updateRecord(array $data): int
    {
        $sql = "UPDATE $this->table_name SET name = :name, mobile = :mobile, email = :email, role = :role, designation = :designation, married = :married, dob = :dob , updated_at = NOW(), version = version + 1 WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':name', $data['name']);
        $stmt->bindParam(':mobile', $data['mobile']);
        $stmt->bindParam(':email', $data['email']);
        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':designation', $data['designation']);
        $stmt->bindParam(':married', $data['married']);
        $stmt->bindParam(':dob', $data['dob']);
        $stmt->bindParam(':id', $data['id']);

        // echo $stmt->queryString;
        // var_dump($data);
        $stmt->execute();
        if (!empty($_FILES['upload'])) {
            $target_dir = "uploads/";
            $target_file = $target_dir . $data['id'] . '.jpg';
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
        $sql = "UPDATE {$this->table_name} SET active = :active WHERE id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id, 'active' => $active]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function readRecords($limit, $offset): array
    {
        $sql = "SELECT * FROM $this->table_name LIMIT $limit OFFSET $offset";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function seedRecords(): void
    {
        $data = [
            [
                'name' => 'John Doe',
                'mobile' => '1234567890',
                'email' => 'john.doe@example.com',
                'role' => 'Developer',
                'designation' => 'Software Engineer',
                'married' => 1,
                'dob' => '1985-06-15'
            ],
            [
                'name' => 'Jane Smith',
                'mobile' => '0987654321',
                'email' => 'jane.smith@example.com',
                'role' => 'Designer',
                'designation' => 'UI/UX Designer',
                'married' => 0,
                'dob' => '1990-03-22'
            ],
            [
                'name' => 'Bob Johnson',
                'mobile' => '5678901234',
                'email' => 'bob.johnson@example.com',
                'role' => 'Manager',
                'designation' => 'Project Manager',
                'married' => 1,
                'dob' => '1975-11-08'
            ],
            [
                'name' => 'Alice Williams',
                'mobile' => '4321098765',
                'email' => 'alice.williams@example.com',
                'role' => 'Tester',
                'designation' => 'Quality Assurance Analyst',
                'active' => 0,
                'married' => 0,
                'dob' => '1992-09-01'
            ],
            [
                'name' => 'Tom Davis',
                'mobile' => '9012345678',
                'email' => 'tom.davis@example.com',
                'role' => 'Analyst',
                'designation' => 'Business Analyst',
                'married' => 1,
                'dob' => '1980-04-30'
            ]
        ];

        foreach ($data as $record) {
            $this->createRecord($record);
        }

        echo "Dummy data seeded successfully.\n";
    }

    public function validateRecords($type = ""): array
    {
        $validationErrors = [];

        $requiredFields = ['name', 'mobile', 'email', 'role', 'designation', 'married', 'dob'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                $validationErrors[$field] = "Empty $field Field";
            }
        }

        if (!empty($_POST['mobile']) && !preg_match('/^\d{10}$/', $_POST['mobile'])) {
            $validationErrors['mobile'] = 'Invalid mobile number';
        }

        if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $validationErrors['email'] = 'Invalid email address';
        }

        if (!empty($_POST['dob']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $_POST['dob'])) {
            $validationErrors['dob'] = 'Invalid date of birth format (YYYY-MM-YYYY)';
        }

        if (!empty($_POST['married']) && !in_array($_POST['married'], [0, 1])) {
            $validationErrors['married'] = 'Married field must be 0 or 1';
        }

        if (empty($validationErrors)) {
            if ($type == "update") {
                $sql = "SELECT email FROM $this->table_name WHERE id = :id";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute(['id' => $_POST['id']]);
                if (empty($stmt->rowCount())) {
                    $validationErrors['id'] = "Invalid user id";
                    return $validationErrors;
                }

                $email = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['email'];
                if ($email == $_POST['email']) {
                    return $validationErrors;
                }
            }

            $sql = "SELECT 1 FROM $this->table_name WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $_POST['email']]);
            if (!empty($stmt->rowCount())) {
                $validationErrors['email'] = "Already Registered Email. Choose Another one";
            }
        }

        return $validationErrors;
    }

    public function validateImage()
    {
        $validationErrors = [];
        if (isset($_FILES['upload']) && $_FILES['upload']['error'] == UPLOAD_ERR_OK) {
            $file = $_FILES['upload'];
            if (!preg_match("~^".$this->allowed_mime_types."$~", mime_content_type($file['tmp_name']))) {
                $validationErrors['upload'] = 'Image should be in ' . mime_content_type($file['tmp_name']) . $this->allowed_mime_types;
            } else if ($this->allowed_file_max_size >= filesize($file['tmp_name'])) {
                $validationErrors['upload'] = 'Image should be less than ' . $this->allowed_file_max_size;
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            if (!preg_match("~^".$this->allowed_mime_types."$~", finfo_file($finfo, $file['tmp_name']))) {
                $validationErrors['upload'] = 'Image should be in ' . $this->allowed_mime_types;
            }
        }

        return $validationErrors;
    }
}
