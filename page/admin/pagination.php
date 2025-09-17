<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>

<style>
#pagination {
  display: flex;
  align-items: center;
  gap: 6px;
}

#pagination button {
  border: none;
  background-color: #008b02;
  color: white;
  padding: 6px 10px;
  cursor: pointer;
  border-radius: 4px;
  font-size: 14px;
  min-width: 36px;
  transition: 0.2s ease;
}

#pagination button:hover:not([disabled]) {
  background-color: #006f02;
}

#pagination button:disabled {
  background-color: #b2ffb3;
  cursor: not-allowed;
}

#totalCount {
  margin-right: auto; /* pushes it to the left */
}

#pagination {
  margin-left: auto; /* pushes it to the right */
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
              <h3 class="card-title">
               <i class="nav-icon fas fa-forward"></i>
</i> Viewer
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
  style="height: 55vh; overflow: auto; display: inline-block; margin-top: 20px; border-top: 1px solid gray;">
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
<!-- ✅ Move pagination + total count into card-footer -->
<div class="card-footer d-flex justify-content-between align-items-center">
  <div id="totalCount">Total Records: 0</div>
  <div id="pagination"></div>
</div>



        </div>
      </div>
    </div>
  </section>
</div>


<script>
  const adminBody = document.getElementById('admin_body');
  const totalCount = document.getElementById('totalCount');
  const rowsPerPage = 20;
  let currentPage = 1;
  function loadPage(page) {
    currentPage = page;
    fetch(`../../process/fetch_employee_pagination.php?page=${page}&limit=${rowsPerPage}`)
      .then(response => response.json())
      .then(data => {
        if (data.error) {
          console.error('Error fetching data:', data.error);
          return;
        }
        adminBody.innerHTML = '';

        data.employees.forEach(row => {
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
        totalCount.innerHTML = `Total Records: ${data.totalRecords}`;
        handlePagination(data.totalRecords);
      })
      .catch(error => console.error('Error:', error));
  }
  function handlePagination(totalRecords) {
    const totalPages = Math.ceil(totalRecords / rowsPerPage);
    const paginationDiv = document.getElementById('pagination');
    paginationDiv.innerHTML = `
        <button ${currentPage === 1 ? 'disabled' : ''} onclick="loadPage(currentPage - 1)">«</button>
        <span>Page ${currentPage} of ${totalPages}</span>
        <button ${currentPage === totalPages ? 'disabled' : ''} onclick="loadPage(currentPage + 1)">»</button>`;
  }
  loadPage(currentPage);
</script>



<?php
include 'plugins/footer.php';
?>