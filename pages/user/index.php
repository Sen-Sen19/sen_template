<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    // Redirect to login if not authenticated
    header('Location: /sen_template/index.php');
    exit;
}
$page_title = 'User Dashboard';
$page_css   = '../../reusable/main.css';
include('../../reusable/header.php');
include('../../reusable/navbar.php');
include('../../reusable/topbar.php');
?>
    <!-- User Dashboard Content -->
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?>!</h1>
        <p>This is your user dashboard.</p>
        <p>Employee ID: <?php echo htmlspecialchars($_SESSION['id_no'] ?? ''); ?></p>
        <p>Role: <?php echo htmlspecialchars($_SESSION['role'] ?? ''); ?></p>
    </div>
<?php include('../../reusable/footer.php'); ?>