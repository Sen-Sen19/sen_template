<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>
<script src="plugins/js/qrcode.min.js"></script>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">QR Code</li>
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
                <i class="nav-icon fas fa-user"></i> QR Code
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
            </div>

            <div id="accounts_table_res" class="table-responsive"
              style="height: 59vh; overflow: auto; display: inline-block; margin-top: 20px; border-top: 1px solid gray;">
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


            <div id="qrModal" class="modal" tabindex="-1" role="dialog">
              <div class="modal-dialog" role="document" style="background-color: #343a40;">
                <div class="modal-content">
                  <div class="modal-header" style="background-color: #343a40; color: white;">
                    <h5 class="modal-title">QR Code</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="row">
                      <div class="col-md-6"
                        style="padding-right: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                        <div id="qrCodeContainer" style="max-width: 100%;"></div>
                        <button id="downloadBtn" class="btn btn-success mt-3" style="width: 120%;">Download QR
                          Code</button>
                      </div>
                      <div class="col-md-6"
                        style="display: flex; flex-direction: column; justify-content: space-between; padding-left: 40px;">
                        <h5>Employee Details</h5>
                        <p><strong>Employee ID:</strong> <span id="empId"></span></p>
                        <p><strong>User Name:</strong> <span id="empUsername"></span></p>
                        <p><strong>Full Name:</strong> <span id="empFullName"></span></p>
                        <p><strong>Department:</strong> <span id="empDept"></span></p>
                        <p><strong>Type:</strong> <span id="empType"></span></p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

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
        tr.addEventListener('click', () => {
          openModal(row);
        });
        adminBody.appendChild(tr);
      });

      totalCount.textContent = `Total Records: ${data.length}`;
    })
    .catch(error => console.error('Error:', error));


  function openModal(row) {
    document.getElementById('empId').textContent = row.EmployeeNo;
    document.getElementById('empUsername').textContent = row.Username;
    document.getElementById('empFullName').textContent = row.FullName;
    document.getElementById('empDept').textContent = row.Section;
    document.getElementById('empType').textContent = row.UserType;

   
    const qrContainer = document.getElementById('qrCodeContainer');
    qrContainer.innerHTML = '';  
    new QRCode(qrContainer, JSON.stringify(row)); 
    const downloadBtn = document.getElementById('downloadBtn');
    downloadBtn.addEventListener('click', () => downloadQRCode(qrContainer));


    $('#qrModal').modal('show');
  }


  function downloadQRCode(qrContainer) {
    const canvas = qrContainer.querySelector('canvas');
    const imageUrl = canvas.toDataURL('image/png'); 
    const link = document.createElement('a');
    link.href = imageUrl;
    link.download = 'qr_code.png'; 
    link.click(); 
  }
</script>

<?php
include 'plugins/footer.php';
?>