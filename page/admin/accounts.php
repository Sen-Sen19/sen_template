<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>
<?php include '../../chat.php'; ?>

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
  #imageDropArea.dragover {
  border-color: #00650c;
  background-color: rgba(0, 128, 0, 0.05);
  transition: 0.2s;
}

</style>

<div class="content-wrapper">
  <div class="content-header"></div>

  <section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-sm-12">
          <div class="card card-gray-dark card-outline">
            <div class="card-header">
                         <?php include '../../dist/include/active_status.php'; ?>

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
                     <th>Active</th>
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

          <!-- 🖼 Profile Image Section -->
          <div class="form-group text-center">

            <div id="imageDropArea"
              style="border: 2px dashed #ccc; border-radius: 8px; padding: 10px; cursor: pointer; display: inline-block;">
              <img id="imagePreview" src="../../dist/img/office-man.png"
                style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
              <input type="file" id="profileImage" name="profileImage" accept="image/*" style="display: none;">
            </div>
          </div>

          <!-- 🧾 Account Info Fields -->
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
const dropArea = document.getElementById('imageDropArea');
const profileImage = document.getElementById('profileImage');
const imagePreview = document.getElementById('imagePreview');

// ========== IMAGE UPLOAD & DRAG AREA ==========
dropArea.addEventListener('click', () => profileImage.click());
dropArea.addEventListener('dragover', (e) => {
  e.preventDefault();
  dropArea.classList.add('dragover');
});
dropArea.addEventListener('dragleave', () => dropArea.classList.remove('dragover'));
dropArea.addEventListener('drop', (e) => {
  e.preventDefault();
  dropArea.classList.remove('dragover');
  const file = e.dataTransfer.files[0];
  if (file && file.type.startsWith('image/')) {
    profileImage.files = e.dataTransfer.files;
    previewImage();
  }
});
profileImage.addEventListener('change', previewImage);
function previewImage() {
  const file = profileImage.files[0];
  if (!file) return;
  const reader = new FileReader();
  reader.onload = (e) => (imagePreview.src = e.target.result);
  reader.readAsDataURL(file);
}

document.addEventListener('DOMContentLoaded', () => {
  const adminBody = document.getElementById('admin_body');
  const deleteBtn = document.getElementById('deleteBtn');
  const addRecordModal = $('#addRecordModal');

  // Form fields
  const employeeIdField = document.getElementById('employeeId');
  const fullNameField   = document.getElementById('fullName');
  const usernameField   = document.getElementById('username');
  const departmentField = document.getElementById('department');
  const passwordField   = document.getElementById('password');
  const typeField       = document.getElementById('type');

  let isEditing = false; // 🧠 track mode (Add vs Edit)

  // ========== OPEN ADD MODAL ==========
  document.getElementById('openModalBtn').addEventListener('click', () => {
    isEditing = false;

    // 🧼 Reset form fields
    employeeIdField.value = '';
    fullNameField.value = '';
    usernameField.value = '';
    departmentField.value = '';
    passwordField.value = '';
    typeField.value = 'user';
    employeeIdField.readOnly = false;
    fullNameField.readOnly = false;

    // 🧼 Reset image
    imagePreview.src = "../../dist/img/office-man.png";
    profileImage.value = '';

    // ✅ Update modal title
    document.getElementById('addRecordModalLabel').innerText = "Add Account";

    $('#addRecordModal').modal('show');
  });

  // ========== SUBMIT ADD/UPDATE ==========
  document.getElementById('addAccountForm').addEventListener('submit', (event) => {
    event.preventDefault();
    const formData = new FormData(event.target);
    const url = isEditing
      ? '../../process/account_update.php'
      : '../../process/account_add.php';

    fetch(url, { method: 'POST', body: formData })
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
            $('#addRecordModal').modal('hide');
            loadAccounts();
          });
        } else {
        Swal.fire({
  icon: 'error',
  title: 'Error',
  text: data.message || 'An unknown error occurred.',
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

  // ========== LOAD ACCOUNTS ==========
  function loadAccounts() {
    Promise.all([
      fetch('../../process/account_view.php').then(r => r.json()),
      fetch('../../process/active_view.php').then(r => r.json())
    ])
    .then(([accounts, activeUsers]) => {
      adminBody.innerHTML = "";
      accounts.forEach(row => {
        const tr = document.createElement('tr');
        const maskedPassword = '•'.repeat(row.password.length);
        let isActive = false;

        activeUsers.forEach(active => {
          const activeFlag = active.is_active === true || active.is_active === "true" || active.is_active == 1;
          if (active.username.trim().toLowerCase() === row.username.trim().toLowerCase() && activeFlag) {
            isActive = true;
          }
        });

        const circleColor = isActive ? "green" : "gray";

        tr.innerHTML = `
          <td>${row.employee_id}</td>
          <td>${row.full_name}</td>
          <td>${row.username}</td>
          <td>${row.department}</td>
          <td>${maskedPassword}</td>
          <td>${row.role}</td>
          <td>
            <span class="status-circle" style="height:12px;width:12px;background:${circleColor};
              border-radius:50%;display:inline-block;"></span>
          </td>
          <td><input type="checkbox" class="select-checkbox" data-employee-id="${row.employee_id}"></td>
        `;

        // prevent checkbox click from triggering edit
        tr.querySelector('.select-checkbox').addEventListener('click', e => e.stopPropagation());

        // 🧠 Row click to edit
        tr.addEventListener('click', () => {
          isEditing = true;

          // 🧼 Reset previous file
          profileImage.value = "";

          // 🖼 Set image preview
          imagePreview.src = (row.img && row.img.startsWith('data:image'))
            ? row.img
            : "../../dist/img/office-man.png";

          // ✅ Populate fields BEFORE modal show
          employeeIdField.value = row.employee_id || '';
          fullNameField.value   = row.full_name || '';
          usernameField.value   = row.username || '';
          departmentField.value = row.department || '';
          passwordField.value   = row.password || '';
          typeField.value       = row.role || 'user';

          employeeIdField.readOnly = true;
          fullNameField.readOnly   = true;

          // ✅ Update modal title
          document.getElementById('addRecordModalLabel').innerText = "Edit Account";

          $('#addRecordModal').modal('show');
        });

        adminBody.appendChild(tr);
      });
    })
    .catch(err => console.error("⚠️ Fetch error:", err));
  }


  // ================== DELETE SELECTED ==================
  deleteBtn.addEventListener('click', () => {
    const selectedCheckboxes = document.querySelectorAll('.select-checkbox:checked');
    const selectedIds = Array.from(selectedCheckboxes).map(cb => cb.dataset.employeeId);

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
              }).then(() => loadAccounts());
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

  loadAccounts();
  setInterval(loadAccounts, 60000);
});
</script>


<?php
include 'plugins/footer.php';
?>