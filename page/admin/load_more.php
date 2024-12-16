<?php include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';?>
<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">Load More</li>
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
                <i class="nav-icon fas fa-user"></i> Load More
              </h3>
            </div>
            <div id="accounts_table_res" class="table-responsive"
              style="height: 58vh; overflow: auto; display: inline-block; margin-top: 20px; border-top: 1px solid gray;">
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
            <div class="text-center mt-3">
              <button id="load_more_button" class="btn" style="background-color:#008b02; color: white;" onclick="loadMoreData()">Load More</button>
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
const loadMoreButton = document.getElementById('load_more_button');
const totalCountElement = document.getElementById('totalCount');
let offset = 0;
const limit = 20; 
let loading = false;

function loadMoreData() {
  if (loading) return; 
  loading = true;
  loadMoreButton.disabled = true;

  fetch(`../../process/fetch_employee_load_more.php?offset=${offset}&limit=${limit}`)
    .then(response => response.json())
    .then(data => {
      if (data.data.length === 0) {
        console.log('No more data to load.');
        loadMoreButton.disabled = true;
        return;
      }


      totalCountElement.innerHTML = `Total Records: ${data.totalCount}`;


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

      offset += limit; 
      loading = false;
      loadMoreButton.disabled = false; 
    })
    .catch(error => {
      console.error('Error fetching data:', error);
      loading = false;
      loadMoreButton.disabled = false;
    });
}

accountsTableRes.addEventListener('scroll', () => {
  if (
    accountsTableRes.scrollTop + accountsTableRes.clientHeight >=
    accountsTableRes.scrollHeight - 20
  ) {
    loadMoreData(); 
  }
});

loadMoreData();

</script>


<?php
include 'plugins/footer.php';
?>
