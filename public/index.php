<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../src/Class/TeamRecordManager.php';
require __DIR__ . '/../src/Class/Type.php';

use Database\Connection;
use App\Class\TeamRecordManager;
use App\Class\Type;

try {
  $db = new Connection();
  $pdo = $db->getConnection();
} catch (\PDOException $e) {
  echo 'Connection failed: ' . $e->getMessage();
  die;
}

$recordManager = new TeamRecordManager($pdo);
$type = new Type($pdo);
$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  switch ($url) {
    case "/":
      $validationErrors =  $recordManager->validateRecords();
      if (!empty($validationErrors)) {
        http_response_code(400);
        echo json_encode(['error' => $validationErrors]);
        exit;
      }

      $validationErrors = $recordManager->validateImage();
      if (!empty($validationErrors)) {
        http_response_code(400);
        die(json_encode(['error' => $validationErrors]));
      }

      if (!empty($recordManager->emailExist($_POST['email']))) {
        http_response_code(400);
        die(json_encode(['error' => 'Email Already Exist. Choose Another one']));
      }

      $message = "Profile Created Successfully";
      $id = $recordManager->createRecord($_POST);
      die(json_encode(['success' => true, 'user_id' => $id, 'meesage' => $message]));

    case "/update":
      $validationErrors =  $recordManager->validateRecords("update");
      if (!empty($validationErrors)) {
        http_response_code(400);
        die(json_encode(['error' => $validationErrors]));
      }

      $validationErrors = $recordManager->validateImage();
      if (!empty($validationErrors)) {
        http_response_code(400);
        die(json_encode(['error' => $validationErrors]));
      }

      if (empty($recordManager->emailChanged($_POST['email'], $_POST['edit-id']))) {
        if (!empty($recordManager->emailExist($_POST['email']))) {
          http_response_code(400);
          die(json_encode(['error' => 'Email Already Exist. Choose Another one']));
        }
      }

      if (empty($recordManager->updateRecord($_POST))) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => "Internal Server Error"]));
      }

      http_response_code(202);
      $message = "Profile Updated Successfully";
      die(json_encode(['success' => true, 'message' => $message]));
  }
}

// for GET and Error404
switch ($url) {
    // to seed dummy data 
  case '/seed':
    $recordManager->seedRecords();
    die;
  case "/fetch":
    http_response_code(202);
    $records = $recordManager->readRecordAll();
    $data = [];
    foreach ($records as $record) {
      if (file_exists('uploads/profile/' . $record['id'] . '.jpg')) {
        $record['photo'] = $record['id'] . '.jpg';
      }
      $data[] = $record;
    }

    $message = "Profile Deleted Successfully";
    die(json_encode([
      'success' => true,
      "draw" => 1,
      "recordsTotal" => count($data) ?? 0,
      "recordsFiltered" => 57,
      'data' => $data
    ]));

  case "/fetchDesignation":
    if (!empty($_GET['id']) && preg_match('~^[1-9]\d*$~', $_GET['id'])) {
      $record = $recordManager->fetchDesignationByID($_GET['id']);
      die(json_encode(['success' => true, 'designations' => $record]));
    }

    http_response_code(400);
    die(json_encode(['error' => 'Missing ID parameter ' . $_GET['id']]));

  case "/fetchByID":

    if (!empty($_GET['id']) && preg_match('~^[1-9]\d*$~', $_GET['id'])) {
      $record = $recordManager->readRecordById($_GET['id']);
      $designations =  $recordManager->fetchDesignationByID($record[0]['role']);
      die(json_encode(['success' => true, 'data' => $record[0], 'designations' => $designations]));
    }

    http_response_code(400);
    die(json_encode(['error' => 'Missing ID parameter']));

  case "/delete":
    if (!empty($_GET['id']) && preg_match('~^[1-9]\d*$~', $_GET['id'])) {
      $validationErrors =  $recordManager->validId($_GET['id']);

      if (!empty($validationErrors)) {
        http_response_code(400);
        die(json_encode(['error' => $validationErrors]));
      }

      if (empty($recordManager->deleteRecordsById($_GET['id']))) {
        http_response_code(500);
        die(json_encode(['success' => false, 'message' => "Internal Server Error"]));
      }

      http_response_code(202);
      $message = "Profile Deleted Successfully";
      die(json_encode(['success' => true, 'message' => $message]));
    }
}

if ($url != '/') {
  echo 'ERror404 page 404! ';
  die;
}

$genders =  $type->readTable('gender');
$marital_status =  $type->readTable('marital_status');
$account_status =  $type->readTable('account_status');
$roles =  $type->readTable('role');
?>

<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dasboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous" />
  <link href="DataTables/datatables.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>

<body>
  <header>
    <!-- Header content here -->
    <div id="topBar">
      <h4>Team</h4>
      <div class="btn-group" role="group" aria-label="Basic example">
        <button type="button" id="button1" class="btn btn-sucess me-2" data-bs-toggle="modal"
          data-bs-target="#exampleModal" data-bs-whatever="@fat">
          + Add
        </button>
        <button type="button" id="button2" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal"
          data-bs-whatever="@fat">
          <span id="filterSign"> 🔗 </span>
          Filter
        </button>
      </div>
    </div>

    <nav aria-label="breadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item">Dashbaord</li>
        <li class="breadcrumb-item active" aria-current="page">All Team</li>
      </ol>
    </nav>
  </header>

  <main>
    <!-- Main content here -->
    <section id="dataTableContainer">
      <div class="card">
        <div class="card-body">
          <table id="example" class="display nowrap" style="width: 100%">
            <thead>
              <tr>
                <th>Name</th>
                <th>Mobile</th>
                <th>Email</th>
                <th>Role</th>
                <th>Designation</th>
                <th>Photo</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </section>
    <!-- Modal popup-->
    <section id="modalFormForAdd">
      <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">Create New Account</h5>
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form class="myForm" action="/">
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="name" class="col-form-label">Full Name:</label>
                    <span class="text-danger">*</span>
                    <input type="text" class="form-control" id="name" name="name" required />
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="mobile" class="col-form-label">Mobile:</label>
                    <span class="text-danger">*</span>
                    <input type="text" class="form-control" id="mobile" name="mobile" required />
                  </div>
                </div>
                <div class="row">
                  <div class="col-md-6 mb-3">
                    <label for="email" class="col-form-label">Email:</label>
                    <span class="text-danger">*</span>
                    <input type="email" class="form-control" name="email" id="email" required />
                  </div>
                  <div class="col-md-6 mb-3">
                    <label for="inputAddress" class="col-form-label">Address:</label>
                    <span class="text-danger">*</span>
                    <input type="text" class="form-control" id="inputAddress" name="address" required />
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col">
                    <label for="role" class="form-label">Role<span class="required">*</span></label>
                    <select id="role" class="form-select" onchange="fetchDesignation(this)" name="role">
                      <option value="">Select Role</option>

                      <?php foreach ($roles as $role) { ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>

                  <div class="col">
                    <label for="designation" class="form-label">Designation<span class="required">*</span></label>
                    <select id="designation" class="form-select" name="designation">
                      <option value="">Select Role First</option>


                    </select>
                  </div>

                  <div class="col">
                    <label for="gender" class="form-label">Gender<span class="required">*</span></label>
                    <select id="gender" class="form-select" name="gender">
                      <option value="">Select Gender</option>
                      <?php foreach ($genders as $gender) { ?>
                        <option value="<?php echo $gender['id']; ?>"><?php echo $gender['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col">
                    <label for="formFile" class="form-label">Upload Image</label>
                    <input class="form-control" type="file" id="formFile" name="upload" />
                  </div>
                  <div class="col">
                    <label for="inputStatus" class="form-label">Status</label>
                    <select id="inputStatus" class="form-select" name="account_status">
                      <option value="">Select Status</option>

                      <?php foreach ($account_status as $status) { ?>
                        <option value="<?php echo $status['id']; ?>"><?php echo $status['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col">
                    <label for="marital-status" class="form-label">Marital Status</label>
                    <div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="married" name="marital_status" value="1">
                        <label class="form-check-label" for="married">Married</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="unmarried" name="marital_status" value="2"
                          checked>
                        <label class="form-check-label" for="unmarried">Unmarried</label>
                      </div>
                    </div>
                  </div>
                  <div class="col">
                    <label for="dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob" id="dob">
                  </div>
                </div>

              </form>
            </div>
            <div class="modal-footer">
              <!-- <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button> -->
              <button type="button" class="btn btn-primary" onclick="">Create Profile</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="modalFormForEdit">
      <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                Edit Account
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form class="myForm" action="/update">
                <div class="row mb-3">
                  <div class="col">
                    <label for="edit-name" class="form-label">Full Name<span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="Enter Full name"
                      aria-label="Enter Full Name" aria-required="true" id="edit-name" />
                  </div>
                  <div class="col">
                    <label for="mobileNo" class="form-label">Mobile No<span class="required">*</span></label>
                    <input id="edit-mobileNo" type="text" name="mobile" class="form-control"
                      placeholder="Enter Mobile No" aria-label="Enter Mobile No" aria-required="true" />
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col">
                    <label for="edit-email" class="form-label">Email Id<span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" placeholder="Enter Email Id"
                      aria-label="Enter Email Id" aria-required="true" id="edit-emailId" />
                  </div>
                  <div class="col">
                    <label for="edit-address" class="form-label">Address<span class="required">*</span></label>
                    <input type="text" name="address" class="form-control" id="edit-address" placeholder="Enter Address"
                      aria-label="Enter Address" aria-required="true" />
                  </div>
                </div>
                <div class="row mb-3">

                  <div class="col">
                    <label for="edit-role" class="form-label">Role<span class="required">*</span></label>
                    <select id="edit-role" class="form-select" name="role" onchange="fetchDesignation(this)">
                      <option value="">Select Role</option>
                      <?php foreach ($roles as $role) { ?>
                        <option value="<?php echo $role['id']; ?>"><?php echo $role['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>

                  <div class="col">
                    <label for="edit-designation" class="form-label">Designation<span class="required">*</span></label>
                    <select id="edit-designation" class="form-select"  name="designation">
                    </select>
                  </div>

                  <div class="col">
                    <label for="edit-gender" class="form-label">Gender<span class="required">*</span></label>
                    <select id="edit-gender" class="form-select" name="gender">
                      <option value="">Select Gender</option>

                      <?php foreach ($genders as $gender) { ?>
                        <option value="<?php echo $gender['id']; ?>"><?php echo $gender['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col">
                    <label for="formFile" class="form-label">Upload Logo</label>
                    <input class="form-control" type="file" id="formFile" name="upload" />
                  </div>
                  <div class="col">
                    <label for="edit-account_status" class="form-label">Status</label>
                    <select id="edit-account_status" class="form-select" name="account_status">
                      <option value="">Select Status</option>
                      <?php foreach ($account_status as $status) { ?>
                        <option value="<?php echo $status['id']; ?>"><?php echo $status['name']; ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <div class="col" id="edit-marital_status">
                    <label for="marital-status" class="form-label">Marital Status</label>
                    <div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="married" name="marital_status" value="1">
                        <label class="form-check-label" for="married">Married</label>
                      </div>
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" id="unmarried" name="marital_status" value="2"
                          checked>
                        <label class="form-check-label" for="unmarried">Unmarried</label>
                      </div>
                    </div>
                  </div>
                  <input type="text" id="edit-id" name="edit-id" hidden>
                  <div class="col">
                    <label for="edit-dob" class="form-label">Date of Birth</label>
                    <input type="date" class="form-control" name="dob" id="edit-dob">
                  </div>
                </div>

              </form>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-dark" name="edit_id" id="editButton">Edit Profile</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section id="modalFormForDelete">
      <div class="modal fade" id="DeleteModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="exampleModalLabel">
                Are You Sure for Delete This Record
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                aria-label="Close"></button>
            </div>
            <div class="modal-body">
              <form class="d-flex justify-content-between">
                <button type="button" class="btn btn-danger" id="deleteButton"
                  onclick="deleteRecord(this)">Delete</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal" aria-label="Cancel">
                  Cancel
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
    <script>
      function deleteRecord(curr) {
        http: //localhost:8080/
          console.log(curr.value);
        fetch(`/delete?id=${curr.value}`)
        .then((res) => {
          if (res.ok) {
            location.reload()
          } else {
            alert("Error deleting record");
          }
        })
        .catch((error) => {
          alert("Error deleting record");
          console.log(error)
        });
      }
    </script>

  </main>

  <footer>
    <!-- Footer content here -->
    <p>&copy; 2025 My Website</p>
  </footer>
  <script>
    const form = document.querySelectorAll('.myForm');
    console.log(form)
    form.forEach(f => {

      console.log(f, f.closest('.modal-content').querySelector('.modal-footer .btn-primary'))
      f.closest('.modal-content').querySelector('.modal-footer button').addEventListener('click', async (event) => {
        const formData = new FormData(f);
        event.preventDefault();
        fetch(f.action, {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            console.log(data)
            if (data.error) {
              alert(data.error)
            } else {
              f.reset()
              location.reload()
            }
          })
          .catch((error) => {
            console.error('Error saving team member:', error);
            alert('Error saving team member:', error);
          })
      });
    })
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM"
    crossorigin="anonymous"></script>
  <script src="DataTables/datatables.min.js"></script>
  <script src="assets/js/script.js"></script>
</body>

</html>