<?php
include 'conn.php';

if(!isset($_POST['file'])){
    die("No file specified.");
}

$fileName = $_POST['file'];

// Delete from database
$sql = "DELETE FROM [sen_template_db].[dbo].[files] WHERE FileName = ?";
$params = [$fileName];
$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt){
    echo "File '$fileName' deleted successfully!";
} else {
    echo "Error deleting file: " . print_r(sqlsrv_errors(), true);
}
?>
