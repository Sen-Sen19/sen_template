<style>
.main-sidebar {
  background-color: #000 !important;
  border-right: 4px solid green;
}
  

  .nav-link.active {
    background-color: #d8d8d8 !important; 
    color: #000 !important; 
  }
  

  .nav-link.active .nav-icon {
    color: #000 !important;
  }
</style>

<aside class="main-sidebar sidebar-dark-primary elevation-4">
  <div class="sidebar">

    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image">
        <img src="../../dist/img/bubonic-plague.png" class="img-circle elevation-2" alt="User Image" style="background-color: green;">
      </div>
      <div class="info">
        <a href="http://172.25.114.229/sen/sen.php" class="d-block" style="text-transform: uppercase;">
          <?=htmlspecialchars($_SESSION['username']);?>
        </a>
      </div>
    </div>
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        
        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/accounts.php") { ?>
          <a href="accounts.php" class="nav-link active">
          <?php } else { ?>
          <a href="accounts.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-user-cog"></i>
            <p>Account Management</p>
          </a>
        </li>


        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/viewer.php") { ?>
          <a href="viewer.php" class="nav-link active">
          <?php } else { ?>
          <a href="viewer.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-table"></i>
            <p>Viewer</p>
          </a>
        </li>

        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/search.php") { ?>
          <a href="search.php" class="nav-link active">
          <?php } else { ?>
          <a href="search.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-search"></i>
            <p>Seach</p>
          </a>
        </li>


        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/pagination.php") { ?>
          <a href="pagination.php" class="nav-link active">
          <?php } else { ?>
          <a href="pagination.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-forward"></i>
            <p>Pagination</p>
          </a>
        </li>




        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/load_more.php") { ?>
          <a href="load_more.php" class="nav-link active">
          <?php } else { ?>
          <a href="load_more.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-chevron-circle-down"></i>
            <p>Load More</p>
          </a>
        </li>



        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/graph.php") { ?>
          <a href="graph.php" class="nav-link active">
          <?php } else { ?>
          <a href="graph.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-chart-bar"></i>
            <p>Graph</p>
          </a>
        </li>



        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/import.php") { ?>
          <a href="import.php" class="nav-link active">
          <?php } else { ?>
          <a href="import.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-download"></i>
            <p>Import</p>
          </a>
        </li>



        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/export.php") { ?>
          <a href="export.php" class="nav-link active">
          <?php } else { ?>
          <a href="export.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-upload"></i>
            <p>Export</p>
          </a>
        </li>



        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/sort.php") { ?>
          <a href="sort.php" class="nav-link active">
          <?php } else { ?>
          <a href="sort.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-sort"></i>
            <p>Sort</p>
          </a>
        </li>



        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/qr.php") { ?>
          <a href="qr.php" class="nav-link active">
          <?php } else { ?>
          <a href="qr.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-qrcode"></i>
            <p>QR Code</p>
          </a>
        </li>

        

        <li class="nav-item">
          <?php if ($_SERVER['REQUEST_URI'] == "/sen_template/page/admin/modal.php") { ?>
          <a href="modal.php" class="nav-link active">
          <?php } else { ?>
          <a href="modal.php" class="nav-link">
          <?php } ?>
            <i class="nav-icon fas fa-window-maximize"></i>
            <p>Modal</p>
          </a>
        </li>
        
        


        <?php include 'logout.php'; ?>
      </ul>
    </nav>
  </div>
</aside>
