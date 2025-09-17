<?php
include 'conn.php';

$sql = "SELECT TOP 1 content FROM Notes";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(json_encode(['status' => 'error', 'message' => sqlsrv_errors()]));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
echo json_encode([
    'status' => 'success',
    'content' => $row['content'] ?? ''
]);
?>
