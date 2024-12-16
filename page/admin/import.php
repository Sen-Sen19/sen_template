<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">Import</li>
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
                <i class="nav-icon fas fa-user"></i> Search
              </h3>
            </div>
            <div class="card-body">
              <div class="row mt-1 align-items-center">
                <div class="col-md-2">
                  <button type="button" id="importBtn" class="btn btn-success btn-block" 
                          style="height: 35px; background-color: #008b02; border-color: #008b02;">
                    <i class="fas fa-download"></i> Import
                  </button>
                  <!-- Hidden file input for CSV selection -->
                  <input type="file" id="csvFileInput" name="csvFile" class="form-control" accept=".csv" style="display: none;" required>
                </div>
              </div>
            </div>

            <div id="accounts_table_res" class="table-responsive"
              style="height: 56vh; overflow: auto; display: inline-block; margin-top: 20px; border-top: 1px solid gray;">
              <table id="account" class="table table-sm table-head-fixed text-nowrap table-hover">
                <thead style="text-align: center;">
                  <tr>
                    <th>Employee ID</th>
                    <th>User Name</th>
                    <th>Full Name</th>
                    <th>Department</th>
                    <th>Type</th>
                  </tr>
                </thead>
                <tbody id="admin_body" style="text-align: center; padding:10px;">
                </tbody>
              </table>
            </div>
            <div id="totalCount" style="text-align: left; margin:10px;">
              Total Records: 0
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const adminBody = document.getElementById('admin_body');
  const totalCount = document.getElementById('totalCount');


  fetch('../../process/fetch_employee.php')
    .then(response => response.json())
    .then(data => {
      if (data.error) {
        console.error('Error fetching data:', data.error);
        return;
      }
      data.forEach(row => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td>${row.EmployeeNo}</td>
          <td>${row.Username}</td>
          <td>${row.FullName}</td>
          <td>${row.Section}</td>
          <td>${row.UserType}</td>
      `;
        adminBody.appendChild(tr);
      });
      totalCount.textContent = `Total Records: ${data.length}`;
    })
    .catch(error => console.error('Error:', error));

  document.getElementById('importBtn').addEventListener('click', () => {
    document.getElementById('csvFileInput').click();  
  });


  document.getElementById('csvFileInput').addEventListener('change', (event) => {
    const fileInput = event.target;
    const file = fileInput.files[0];

    if (file) {

      const formData = new FormData();
      formData.append('csvFile', file);

      fetch('../../process/import_employee.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(result => {
        if (result.success) {
          Swal.fire({
            icon: 'success',
            title: 'Import Successful',
            text: result.message || 'Data has been successfully imported!',
            timer: 1000,  
            showConfirmButton: false 
          }).then(() => {
            location.reload();  
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Import Failed',
            text: result.message || 'There was an error importing the data.',
            timer: 1000,
            showConfirmButton: false 
          }).then(() => {
            location.reload(); 
          });
        }
      })
      .catch(error => {
        Swal.fire({
          icon: 'error',
          title: 'Error',
          text: 'Something went wrong. Please try again later.',
          timer: 1000,  
          showConfirmButton: false  
        }).then(() => {
          location.reload();  
        });
      });
    } else {
      Swal.fire({
        icon: 'warning',
        title: 'No File Selected',
        text: 'Please select a CSV file to import.',
        timer: 1000,  
        showConfirmButton: false  
      });
    }
  });
</script>


<?php include 'plugins/footer.php'; ?>
