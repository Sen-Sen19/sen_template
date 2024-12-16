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
            <li class="breadcrumb-item active">Sort</li>
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
                <i class="nav-icon fas fa-user"></i> Sort
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
                    <th class="sortable" data-column="EmployeeNo">Employee ID <i class="nav-icon fas fa-sort"></i></th>
                    <th class="sortable" data-column="Username">User Name <i class="nav-icon fas fa-sort"></i></th>
                    <th class="sortable" data-column="FullName">Full Name <i class="nav-icon fas fa-sort"></i></th>
                    <th class="sortable" data-column="Section">Department <i class="nav-icon fas fa-sort"></i></th>
                    <th class="sortable" data-column="UserType">Type <i class="nav-icon fas fa-sort"></i></th>
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
const headers = document.querySelectorAll('.sortable');
let sortOrder = {
  EmployeeNo: 'desc', 
  Username: 'asc',
  FullName: 'asc',
  Section: 'asc',
  UserType: 'asc'
};

headers.forEach(header => {
  header.addEventListener('click', () => {
    const column = header.getAttribute('data-column');
    const isNumeric = column === 'EmployeeNo'; 
    const rows = Array.from(adminBody.rows);
    sortOrder[column] = sortOrder[column] === 'asc' ? 'desc' : 'asc';
    headers.forEach(th => {
      const icon = th.querySelector('i');
      icon.classList.remove('fa-sort-up', 'fa-sort-down');
      icon.classList.add('fa-sort');
    });
    const icon = header.querySelector('i');
    if (sortOrder[column] === 'asc') {
      icon.classList.remove('fa-sort');
      icon.classList.add('fa-sort-up');
    } else {
      icon.classList.remove('fa-sort');
      icon.classList.add('fa-sort-down');
    }
    rows.sort((a, b) => {
      const cellA = a.cells[header.cellIndex].textContent.trim();
      const cellB = b.cells[header.cellIndex].textContent.trim();

      if (isNumeric) {
     
        return sortOrder[column] === 'asc' ? parseInt(cellA) - parseInt(cellB) : parseInt(cellB) - parseInt(cellA);
      } else {
      
        return sortOrder[column] === 'asc' ? cellA.localeCompare(cellB) : cellB.localeCompare(cellA);
      }
    });

    rows.forEach(row => adminBody.appendChild(row));
  });
});
</script>

<?php
include 'plugins/footer.php';
?>
