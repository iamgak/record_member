let table = new DataTable('#example', {
    ajax: {
        url: "/fetch",
        type: 'GET'
    },
    columns: [
        { data: 'name' },
        { data: 'mobile' },
        { data: 'email' },
        { data: 'role' },
        { data: 'designation' },
        {
            data: 'photo',
            render: function (data, type, row) {
                if (row['photo']) { // If there's an image URL
                    return '<img src="uploads/profile/' + row['id'] + '.jpg" alt="User Photo" style="height: 30px; width: 30px;">';
                } else { // If no URL provided, use a generic placeholder
                    return '<img src="uploads/default/genericIcon.png" alt="Generic Photo" style="height: 30px; width: 30px;">';
                }
            }
            , "width": "5%"
        },
        {
            data: 'account_status',
            render: function (data, type, row) {
                if (row['account_status'] === 1) {
                    return '<span class="badge bg-success">Active</span>';
                } else {
                    return '<span class="badge bg-danger">Inactive</span><br>';
                }
            },
            "width": "10%"
        },
        {
            data: 'action',
            render: function (id, type, row) {
                return `<button 
                            type="button" 
                            class="btn btn-outline-success me-2"
                            data-bs-toggle="modal"
                            data-bs-whatever = "${row['id']}"
                            data-id = "${row['id']}"
                            onclick  = "fillModalValue(${row['id']})"
                            data-bs-target="#EditModal">
                                <img src="uploads/default/edit.png" alt="" style="height: 16px; width: 16px;">
                        </button>
                        <button 
                            type="button" 
                            data-bs-whatever = "${row['id']}"
                            data-id = "${row['id']}"
                            class="btn btn-outline-danger"
                            data-bs-toggle="modal"
                            data-bs-target="#DeleteModal">
                                <img src="uploads/default/delete.png" alt="" style="height: 16px; width: 16px;">
                        </button>`;
            },
            "width": "5%"
        }

    ]
});

function fillModalValue(id) {
    fetch(`/fetchByID?id=${id}`)
        .then(response => response.json())
        .then(data => {

            document.querySelector('#edit-name').value = data.data.name;
            document.querySelector('#edit-mobileNo').value = data.data.mobile;
            document.querySelector('#edit-emailId').value = data.data.email;
            document.querySelector('#edit-address').value = data.data.address;
            document.querySelector('#edit-dob').value = data.data.dob;
            setIndex('edit-gender', data.data.gender);
            setIndex('edit-role', data.data.role);
            setIndex('edit-account_status', data.data.account_status);
            document.querySelector('#edit-id').value = data.data.id;
            const radioButtons = document.querySelectorAll('#edit-marital_status input[name="marital_status"]');
            radioButtons.forEach(radio => {
                if (radio.value == data.data.marital_status) {
                    radio.checked = true;
                    console.log(radioButtons, radio)
                }
            });

            let selectElement = document.querySelector('#edit-designation')
            let option = document.createElement('option');
            option.innerHTML = "Please Select Designation";
            selectElement.appendChild(option);
            data.designations.forEach(designation => {
                option = document.createElement('option');
                // console.log(selectElement,designation.id, data.data.designation)
                option.value = designation.id;
                option.innerHTML = designation.name;
                selectElement.appendChild(option);
            });

            setIndex('edit-designation', data.data.designation);
            
        })
        .catch(error => console.error('Error fetching data:', error));
}

function setIndex(name, val) {
    let a = document.querySelector(`#${name}`);
    for (let i = 0; i < a.options.length; i++) {
        let option = a.options[i];
        // console.log(name,val)
        if (option.value == val) {
            a.selectedIndex = i
            console.log(a,name,val,i)
            break
        }
    }
}

var myModal = document.getElementById('DeleteModal')
myModal.addEventListener('show.bs.modal', function (event) {
    let button = event.relatedTarget // Button that triggered the modal
    let id = button.getAttribute('data-id') // Extract info from custom attribute
    var displayElement = myModal.querySelector('#deleteButton')
    if (displayElement) {
        displayElement.value = id;
    }
})

function fetchDesignation(curr) {
    let id = curr.value
    fetch(`/fetchDesignation?id=${id}`)
        .then(response => response.json())
        .then(data => {
            console.log(data)
            let selectElement = curr.closest('form').querySelector('select[name="designation"]')
            if (data.success) {
                selectElement.options.length = 0
                if (data.designations) {
                    let option = document.createElement('option');
                    option.innerHTML = "Please Select Designation";
                    selectElement.appendChild(option);
                    data.designations.forEach(designation => {
                        option = document.createElement('option');
                        option.value = designation.id;
                        option.innerHTML = designation.name;
                        selectElement.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.innerHTML = 'Please Select Correct Role';
                    selectElement.appendChild(option);
                }
            }
        })
        .catch((error) => {
            alert('Internal Server Error. Try Again Later!!!')
            console.log(error)
        })
}