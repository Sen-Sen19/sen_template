<?php date_default_timezone_set('Asia/Manila'); ?>
<h3 class="card-title mb-0" hidden>
  <i class="nav-icon fas fa-book"></i>  
  <span id="username"><?= htmlspecialchars($_SESSION['username']); ?></span>
  <small class="text-muted" id="datetime">
    <?= date("Y-m-d H:i:s"); ?>
  </small>
</h3>

<script>
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
    if (["success", "inserted", "updated"].includes(data.status)) {
      console.log("✅ Activity logged:", data);
    } else {
      console.error("❌ Logging failed:", data);
    }
  })
  .catch(err => console.error("⚠️ Fetch error:", err));
});
</script>
