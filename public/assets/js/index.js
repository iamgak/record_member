$(document).ready(function () {
    let modal = document.getElementById("createFormModal");
    let btn = document.getElementById("showCreateForm");
    let span = document.getElementsByClassName("close-button")[0];
    let btn_close = document.querySelector('#close-btn')

    btn.onclick = function () {
        modal.style.display = "block";
    }

    btn_close.onclick = function () {
        modal.style.display = "none";
    }
    span.onclick = function () {
        modal.style.display = "none";
    }

    window.onclick = function (event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    }

    $('form[data-action="create"]').submit(function (event) {
        event.preventDefault();
        var formdata = new FormData(this);
        formdata.append('action', 'create');

        $.ajax({
            type: 'POST',
            url: '/',
            data: formdata,
            cache: false,
            processData: false,
            contentType: false,
            success: function (response) {
                var data = JSON.parse(response);

                if (data.error) {
                    alert(data.error);
                } else {
                    let obj = Object.fromEntries(formdata.entries());
                    var parentId = obj['parent_id'];
                    var name = obj['name'];

                    if (parentId) {
                        var parentField = $('.records-list').find(`[data-id='${parentId}']`);
                        if (parentField.length > 0) {
                            var childList = parentField.find('> ul');
                            if (childList.length === 0) {
                                childList = $('<ul>');
                                parentField.append(childList);
                            }

                            childList.append($('<li>').attr('data-id', data.id).append('<h3>' + name + '</h3>'));
                        }
                    } else {
                        $('.records-list').append($('<li>').attr('data-id', data.id).append('<h3>' + name + '</h3>'));
                        modal.style.display = "none";
                    }
                    $('#parent_id').append(`<option value="${data.id}" data-id="${parentId}">${name}</option>`);
                    modal.style.display = "none";
                    $('form[data-action="create"]').trigger('reset');

                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
                alert("Internal Server Error");
            }
        });
    });
});
