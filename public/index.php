<?php
require_once '../classes/RecordManager.php';

$recordManager = new RecordManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
        die(json_encode(['error' => 'Empty Name Field']));
    }

    //just in case parent should be compulsary
    // if (empty($_POST['parent_id'])) {
    //     die(json_encode(['error' => 'Empty Parent ID Field']));
    // }

    if (!empty($_POST['parent_id'])  && !preg_match('/\d+/', $_POST['parent_id'])) {
        die(json_encode(['error' => 'Incorrect Parent ID Value']));
    }

    switch ($_POST['action']) {
        case 'create':
            $recordId = $recordManager->createRecord($_POST);
            die(json_encode(['id' => $recordId]));
        default:
            die(json_encode(['error' => 'Incorrect Request']));
    }
}

// Display the main interface
$records = $recordManager->readRecords();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Records</title>
    <link rel="stylesheet" href="./assets/css/style.css">
</head>

<body>
    <div class="container">
        <h1>Member Records</h1>

        <div id="createFormModal" class="modal">
            <div class="modal-content">
                <span class="close-button">&times;</span>
                <form data-action="create">
                    <label for="parent_id">Parent</label>
                    <select name="parent_id" id="parent_id">
                        <option value="">Select Parent</option>
                        <?php foreach ($records as $record) { ?>
                            <option value="<?php echo $record['id']; ?>"><?php echo $record['name']; ?></option>
                        <?php } ?>
                    </select>
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" required>
                    <button type="submit">Save Changes</button>
                    <button type="button" id="close-btn">Close</button>
                </form>
            </div>
        </div>


        <ul class="records-list">
        </ul>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let list = <?php echo json_encode($records); ?>;

            list.forEach((record) => {
                createRecord(record);
            });
        });

        function createRecord(record) {
            let records_ul = document.querySelector('.records-list');
            if (record.parent_id) {
                let parentNode = document.querySelector(`[data-id="${record.parent_id}"]`);
                if (parentNode) {
                    let ul = parentNode.querySelector('ul');
                    if (!ul) {
                        ul = document.createElement('ul');
                        parentNode.appendChild(ul);
                    }
                    ul.appendChild(createListItem(record));
                } else {
                    records_ul.appendChild(createListItem(record));
                }
            } else {
                records_ul.appendChild(createListItem(record));
            }
        }

        function createListItem(record) {
            let li = document.createElement('li');
            li.dataset.id = record.id;
            li.innerHTML = `<h3>${record.name}</h3>`;
            return li;
        }
    </script>
    <button type="button" id="showCreateForm">Add Members</button>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="./assets/js/index.js"></script>
</body>

</html>