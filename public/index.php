<?php
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
require_once '../classes/RecordManager.php';

$recordManager = new TeamRecordManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($url == "/") {
        $validationErrors =  $recordManager->validateRecords();

        if (!empty($validationErrors)) {
            http_response_code(400);
            echo json_encode(['errors' => $validationErrors]);
            exit;
        }

        // var_dump($_FILES);
        $validationErrors = $recordManager->validateImage();

        if (!empty($validationErrors)) {
            http_response_code(400);
            die(json_encode(['errors' => $validationErrors]));
        }

        $message = "Profile Created Successfully";
        $id = $recordManager->createRecord($_POST);
        die(json_encode(['success' => true, 'user_id' => $id, 'meesage' => $message]));
    } elseif ($url == "/update") {
        $validationErrors =  $recordManager->validateRecords("update");

        if (!empty($validationErrors)) {
            http_response_code(400);
            die(json_encode(['errors' => $validationErrors]));
        }

        $validationErrors = $recordManager->validateImage();
        if (!empty($validationErrors)) {
            http_response_code(400);
            die(json_encode(['errors' => $validationErrors]));
        }

        if (empty($recordManager->updateRecord($_POST))) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => "Internal Server Error"]));
        }

        http_response_code(202);
        $message = "Profile Updated Successfully";
        die(json_encode(['success' => true, 'message' => $message]));
    } elseif (preg_match("~^/delete/(\d+)/$~", $url, $matches)) {
        $validationErrors =  $recordManager->validId($matches[1]);

        if (!empty($validationErrors)) {
            http_response_code(400);
            die(json_encode(['errors' => $validationErrors]));
        }

        if (empty($recordManager->deleteRecordsById($matches[1]))) {
            http_response_code(500);
            die(json_encode(['success' => false, 'message' => "Internal Server Error"]));
        }

        http_response_code(202);
        $message = "Profile Deleted Successfully";
        die(json_encode(['success' => true, 'message' => $message]));
    }
}

if ($url != "/") {
    // $recordManager->seedRecords();
    echo "error404 Page";
    die;
}


// Display the main interface
$limit = 10;
$offset = 0;
if (!empty($_GET)) {
    if (!empty($_GET['limit']) && preg_match('~^\d+$~', $_GET['limit'])) {
        $limit = trim($_GET['limit']);
    }
    if (!empty($_GET['page']) && preg_match('~^\d+$~', $_GET['page'])) {
        $offset = (trim($_GET['page']) - 1)*$limit;
    }
}
$records = $recordManager->readRecords($limit ,$offset);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Member List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .popup {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgb(0, 0, 0);
            background-color: rgba(0, 0, 0, 0.4);
        }

        .popup-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <h2>Team Member List</h2>
    <button onclick="showPopup(this,'add')">Add New Entry</button>
    <table>
        <tr>
            <th>Name</th>
            <th>Mobile</th>
            <th>Email</th>
            <th>Role</th>
            <th>Designation</th>
            <th>Photo</th>
            <th>Status</th>
            <th>DOB</th>
            <th>Actions</th>
        </tr>
        <?php
        if (!empty($records)) {
            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>" . $record['name'] . "</td>";
                echo "<td>" . $record['mobile'] . "</td>";
                echo "<td>" . $record['email'] . "</td>";
                echo "<td>" . $record['role'] . "</td>";
                echo "<td>" . $record['designation'] . "</td>";
                echo "<td><img src='uploads/" . (file_exists('uploads/' . $record['id'] . '.jpg') ? $record['id'] : 'DUMMY') . ".jpg' width='50'></td>";
                echo "<td>" . $record['active'] . "</td>";
                echo "<td>" . $record['dob'] . "</td>";
                echo "<td class='actions'  data-id = '" . $record['id'] . "'>";
                echo "<button onclick='showPopup(this,\"edit\", " . $record['id'] . ")'>Edit</button>";
                echo "<button onclick='deleteTeamMember(" . $record['id'] . ")'>Delete</button>";
                echo "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No records found</td></tr>";
        }
        ?>
    </table>

    <div id="popup" class="popup">
        <div class="popup-content">
            <span class="close" onclick="closePopup()">&times;</span>
            <h2 id="popupTitle">Add New Entry</h2>
            <form id="teamForm" action="handle_form.php" method="post" enctype="multipart/form-data">
                <input type="hidden" id="action" name="action">
                <input type="hidden" id="id" name="id">
                <label for="name">Name:</label><br>
                <input type="text" id="name" name="name" required><br>
                <label for="mobile">Mobile:</label><br>
                <input type="text" id="mobile" name="mobile" required><br>
                <label for="email">Email:</label><br>
                <input type="email" id="email" name="email" required><br>
                <label for="role">Role:</label><br>
                <input type="text" id="role" name="role" required><br>
                <label for="designation">Designation:</label><br>
                <input type="text" id="designation" name="designation" required><br>
                <label for="upload">Image:</label><br>
                <input type="file" id="upload" name="upload"><br>
                <label for="status">Married:</label><br>
                <input type="text" id="status" name="married" required><br>
                <label for="dob">DOB:</label><br>
                <input type="date" id="dob" name="dob" required><br>
                <button type="submit">Save</button>
            </form>
        </div>
    </div>

    <script>
        function showPopup(curr, action, id = null) {
            document.getElementById('popup').style.display = 'block';
            document.getElementById('action').value = action;
            document.getElementById('id').value = id;
            if (action === 'edit') {
                console.log(curr, curr.parentNode.getAttribute('data-id'))
                id = curr.parentNode.getAttribute('data-id')
                location = '/update/' + id
                document.getElementById('popupTitle').innerText = 'Edit Entry';

                // fetch('get_team_member.php?id=' + id)
                //     .then(response => response.json())
                //     .then(data => {
                //         document.getElementById('name').value = data.name;
                //         document.getElementById('mobile').value = data.mobile;
                //         document.getElementById('email').value = data.email;
                //         document.getElementById('role').value = data.role;
                //         document.getElementById('designation').value = data.designation;
                //         document.getElementById('status').value = data.status;
                //         document.getElementById('dob').value = data.dob;
                //     });
            } else {
                document.getElementById('popupTitle').innerText = 'Add New Entry';
                document.getElementById('teamForm').reset();
            }
        }

        function closePopup() {
            document.getElementById('popup').style.display = 'none';
        }

        // Handle form submission
        document.getElementById('teamForm').addEventListener('submit', function(event) {
            event.preventDefault();
            const formData = new FormData(this);
            fetch('/', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        closePopup();
                        // location.reload();
                    } else {
                        alert(data.message);
                    }
                });
        });

        function deleteTeamMember(id) {
            if (confirm('Are you sure you want to delete this entry?')) {
                fetch(`/delete/${id}/`, {
                        method: 'POST'
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log(data)
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else if (data.errors) {
                            alert(data.errors);
                        }
                    })
                    .catch((error) => {
                        console.log(error)
                        alert("Internal server error")
                    });
            }
        }
    </script>

</body>

</html>