<?php
include 'plugins/navbar.php';
include 'plugins/sidebar/user_bar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"></div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="">Home</a></li>
            <li class="breadcrumb-item active">Note</li>
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
            <div class="card-header d-flex align-items-center">
<?php date_default_timezone_set('Asia/Manila'); ?>
<h3 class="card-title mb-0" hidden>
  <i class="nav-icon fas fa-book"></i>  
  <span id="username"><?= htmlspecialchars($_SESSION['username']); ?></span>
  <small class="text-muted" id="datetime">
    <?= date("Y-m-d H:i:s"); ?>
  </small>
</h3>




              <!-- push buttons to the very end -->
              <div class="ml-auto d-flex">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                  <i class="fas fa-minus"></i>
                </button>
                <button type="button" class="btn btn-tool" data-card-widget="maximize">
                  <i class="fas fa-expand"></i>
                </button>
              </div>
            </div>
            <div class="card-body">
              <!-- Page content goes here -->
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


<script>
  // ====================================Active Status====================================
document.addEventListener("DOMContentLoaded", () => {
  const username = "<?= htmlspecialchars($_SESSION['username']); ?>";
  const dateTime = "<?= date("Y-m-d H:i:s"); ?>"; // same value shown above

  fetch("../../process/log_activity.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json"
    },
    body: JSON.stringify({ username, date_time: dateTime })
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === "success") {
      console.log("✅ Activity logged:", data);
    } else {
      console.error("❌ Logging failed:", data);
    }
  })
  .catch(err => console.error("⚠️ Fetch error:", err));
});
</script>