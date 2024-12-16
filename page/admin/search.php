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
            <li class="breadcrumb-item active">Search</li>
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
                <div class="col-md-4 d-flex justify-content-center">
                  <div class="input-group" style="height: 35px; width: 100%;">
                    <input type="text" class="form-control" id="searchBox" placeholder="Search..."
                      style="height: 35px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <button class="btn btn-success" id="openModalBtn" data-toggle="modal" data-target="#addRecordModal"
                      style="height: 35px; border-top-left-radius: 0; border-bottom-left-radius: 0; background-color: #008b02; border-color: #008b02;">
                      <i class="fas fa-search"></i>
                    </button>
                  </div>
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
            <div id="totalCount" style="text-align: left; margin:10px ;">
  Total Records: 0
</div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<script>
 const adminBody = document.getElementById('admin_body');
const accountsTableRes = document.getElementById('accounts_table_res');
const totalCountElement = document.getElementById('totalCount');  // Total count element
let offset = 0;
const limit = 20;
let loading = false;

function fetchEmployeeData(search = '') {
  loading = true;

  fetch(`../../process/fetch_employee_search.php?offset=${offset}&limit=${limit}&search=${encodeURIComponent(search)}`)
    .then(response => response.json())
    .then(data => {
      if (offset === 0) adminBody.innerHTML = '';

      // If no data found
      if (data.data.length === 0 && offset === 0) {
        adminBody.innerHTML = '<tr><td colspan="5">No results found</td></tr>';
        return;
      }

      // Append fetched data to the table
      data.data.forEach(row => {
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

      // Update total count
      totalCountElement.textContent = `Total Records: ${data.totalCount}`;

      offset += limit;
      loading = false;
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      loading = false;
    });
}

accountsTableRes.addEventListener('scroll', () => {
  if (accountsTableRes.scrollTop + accountsTableRes.clientHeight >= accountsTableRes.scrollHeight - 20) {
    if (!loading) loadMoreData();
  }
});

function loadMoreData() {
  fetchEmployeeData();
}

loadMoreData();

document.getElementById('openModalBtn').addEventListener('click', () => {
  const searchTerm = document.getElementById('searchBox').value.trim();
  offset = 0;
  fetchEmployeeData(searchTerm);
});

</script>

<?php
include 'plugins/footer.php';
?>