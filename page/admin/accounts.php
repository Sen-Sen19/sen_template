<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>
<style>

  .modal-header.bg-dark {
    background-color: #000;
    border-top-left-radius: 4px;
    border-top-right-radius: 4px;
  }

  #addAccountForm .form-control {
    border-radius: 4px;
  }

  #addAccountForm label {
    font-weight: bold;
    color: #333;
  }


  #addAccountForm .btn-primary {
    background-color: #00650c !important;
    border-color: #00650c !important;
    color: #fff;

  }

  .modal-header .close {
    opacity: 1;
  }
</style>
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">Admin</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card card-gray-dark card-outline">
            <div class="card-header">
              <h3 class="card-title">
                <i class="nav-icon fas fa-user"></i> ADMIN
              </h3>
              <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>

            <div class="card-body">
              <div class="row mb-2"></div>

              <div class="row mt-1 align-items-center">
                <div class="col-md-2 d-flex justify-content-center">
                  <button class="btn btn-success custom-btn" id="openModalBtn" data-toggle="modal"
                    data-target="#addRecordModal"
                    style="height: 35px; width: 100%; background-color:#008b02; border-color:#008b02;">
                    <i class="fas fa-plus mr-2"></i>Add Account
                  </button>
                </div>
                <div class="col-md-2 d-flex justify-content-center">
                  <button class="btn btn-danger custom-btn" id="deleteBtn"
                    style="height: 35px; width: 100%; background-color:#3d3b3e; border-color:black;">
                    <i class="fas fa-trash mr-2"></i>Delete
                  </button>
                </div>
              </div>
            </div>

            <div id="accounts_table_res" class="table-responsive"
              style="height: 60vh; overflow: auto; display: inline-block; margin-top: 20px; border-top: 1px solid gray;">
              <table id="account" class="table table-sm table-head-fixed text-nowrap table-hover">
                <thead style="text-align: center;">
                  <tr>
                    <th>Employee ID</th>
                    <th>Full Name</th>
                    <th>User Name</th>
                    <th>Department</th>
                    <th>Password</th>
                    <th>Type</th>
                    <th>Select</th>
                  </tr>
                </thead>
                <tbody id="admin_body" style="text-align: center; padding:10px;">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>


<div class="modal fade" id="addRecordModal" tabindex="-1" role="dialog" aria-labelledby="addRecordModalLabel"
  aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">

      <div class="modal-header bg-dark text-white py-3">
        <h5 class="modal-title" id="addRecordModalLabel">Add Account</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="addAccountForm">

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="employeeId">Employee ID</label>
              <input type="text" class="form-control" id="employeeId" name="employeeId" required>
            </div>
            <div class="form-group col-md-4">
              <label for="fullName">Full Name</label>
              <input type="text" class="form-control" id="fullName" name="fullName" required>
            </div>
            <div class="form-group col-md-4">
              <label for="username">Username</label>
              <input type="text" class="form-control" id="username" name="username" required>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="department">Department</label>
              <input type="text" class="form-control" id="department" name="department" required>
            </div>
            <div class="form-group col-md-4">
              <label for="password">Password</label>
              <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="form-group col-md-4">
              <label for="type">Type</label>
              <select class="form-control" id="type" name="type" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>

          </div>

          <button type="submit" class="btn btn-primary btn-block mt-3">Save Account</button>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const adminBody = document.getElementById('admin_body');
    const deleteBtn = document.getElementById('deleteBtn');
    const addRecordModal = new bootstrap.Modal(document.getElementById('addRecordModal'));
    const employeeIdField = document.getElementById('employeeId');
    const fullNameField = document.getElementById('fullName');
    const usernameField = document.getElementById('username');
    const departmentField = document.getElementById('department');
    const passwordField = document.getElementById('password');
    const typeField = document.getElementById('type');
    document.getElementById('openModalBtn').addEventListener('click', () => {
      employeeIdField.value = '';
      fullNameField.value = '';
      usernameField.value = '';
      departmentField.value = '';
      passwordField.value = '';
      typeField.value = 'user';
      employeeIdField.readOnly = false;
      fullNameField.readOnly = false;
      addRecordModal.show();
    });

    document.getElementById('addAccountForm').addEventListener('submit', (event) => {
      event.preventDefault();
      const formData = new FormData(event.target);
      const url = employeeIdField.readOnly ? '../../process/account_update.php' : '../../process/account_add.php';

      fetch(url, {
        method: 'POST',
        body: formData,
      })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            Swal.fire({
              icon: 'success',
              title: 'Success',
              text: 'Account saved successfully!',
              timer: 1000,
              showConfirmButton: false,
            }).then(() => {
              addRecordModal.hide();
              location.reload();
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'An error occurred: ' + data.error,
              timer: 1000,
              showConfirmButton: false,
            });
          }
        })
        .catch(error => {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred: ' + error,
          });
        });
    });
    fetch('../../process/account_view.php')
  .then(response => response.json())
  .then(data => {
    if (data.error) {
      console.error('Error fetching data:', data.error);
      return;
    }
    data.forEach(row => {
      const tr = document.createElement('tr');
      const maskedPassword =  '•'.repeat(row.password.length);  
      tr.innerHTML = ` 
                <td>${row.employee_id}</td>
                <td>${row.full_name}</td>
                <td>${row.username}</td>
                <td>${row.department}</td>
                <td>${maskedPassword}</td>  <!-- Display masked password -->
                <td>${row.role}</td>
                <td><input type="checkbox" class="select-checkbox" data-employee-id="${row.employee_id}"></td>
            `;
      tr.addEventListener('click', () => {
        employeeIdField.value = row.employee_id;
        fullNameField.value = row.full_name;
        usernameField.value = row.username;
        departmentField.value = row.department;
        passwordField.value = row.password;  
        typeField.value = row.role;

        employeeIdField.readOnly = true;
        fullNameField.readOnly = true;
        addRecordModal.show();
      });
      adminBody.appendChild(tr);
    });
  })
  .catch(error => console.error('Error:', error));
    deleteBtn.addEventListener('click', () => {
      const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
      const selectedIds = [];
      selectedCheckboxes.forEach(checkbox => {
        selectedIds.push(checkbox.dataset.employeeId);
      });

      if (selectedIds.length === 0) {
        Swal.fire({
          icon: 'warning',
          title: 'No Account Selected',
          text: 'Please select at least one account to delete.',
        });
        return;
      }
      Swal.fire({
        title: 'Are you sure?',
        text: 'You are about to delete the selected accounts. This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'No, keep them',
      }).then((result) => {
        if (result.isConfirmed) {

          fetch('../../process/account_delete.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ ids: selectedIds }),
          })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                Swal.fire({
                  icon: 'success',
                  title: 'Deleted',
                  text: 'Selected accounts have been deleted.',
                  timer: 1000,
                  showConfirmButton: false,
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  icon: 'error',
                  title: 'Error',
                  text: 'An error occurred while deleting accounts.',
                });
              }
            })
            .catch(error => {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'An error occurred: ' + error,
              });
            });
        }
      });
    });
  });
</script>


<?php
include 'plugins/footer.php';
?>