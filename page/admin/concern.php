<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/admin_bar.php';
?>
<style>
/* Overlay that sits only inside content-wrapper */
#wipOverlay {
  position: absolute;
  inset: 0; /* shorthand: top:0; left:0; right:0; bottom:0 */
  background: rgba(255, 255, 255, 0.3);
  backdrop-filter: blur(5px);
  -webkit-backdrop-filter: blur(5px);
  z-index: 10;
  display: flex;
  justify-content: center;
  align-items: center;
  flex-direction: column;
}

#wipText {
  font-size: 2rem;
  font-weight: bold;
  color: #333;
  text-shadow: 1px 1px 4px rgba(0,0,0,0.3);
  text-align: center;
}

</style>

<div class="content-wrapper" style="position: relative;">
  <!-- WIP Overlay -->
  <div id="wipOverlay">
   <div id="wipText">
  🚧 Under Maintenance 🚧

</div>

  </div>

  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">Concerns</li>
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
                <i class="nav-icon fas fa-concierge-bell"></i> Concerns
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
              <!-- Your content -->
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php
include 'plugins/footer.php';
?>
