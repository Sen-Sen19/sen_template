<?php
include 'conn.php';

if(!isset($_GET['file'])){
    die("No file specified.");
}

$fileName = $_GET['file'];

// Fetch file from database
$sql = "SELECT FileName, FileType, FileContent FROM [sen_template_db].[dbo].[files] WHERE FileName = ?";
$params = [$fileName];
$stmt = sqlsrv_query($conn, $sql, $params);

if($stmt && sqlsrv_has_rows($stmt)){
    $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    // Set headers
    header('Content-Description: File Transfer');
    header('Content-Type: ' . $row['FileType']);
    header('Content-Disposition: attachment; filename="' . $row['FileName'] . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');

    // Output file content
    echo $row['FileContent'];
    exit;
} else {
    die("File not found.");
}
?>
