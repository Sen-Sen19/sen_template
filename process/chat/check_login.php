<?php
header("Content-Type: application/json");
require_once "../conn.php";

session_start();

$id = trim($_POST['idno'] ?? '');
if ($id === '') {
    echo json_encode(["status" => "error", "message" => "ID required"]);
    exit;
}

// Fetch employee data including image
$sql = "SELECT employee_id, full_name, img FROM account WHERE employee_id = ?";
$stmt = sqlsrv_query($conn, $sql, [$id]);

if ($stmt === false || !sqlsrv_has_rows($stmt)) {
    echo json_encode(["status" => "notfound"]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

// Convert VARBINARY image to base64
$imgBase64 = null;
if (!empty($row['img'])) {
    $imgBase64 = "data:image/jpeg;base64," . base64_encode($row['img']);
}

// Store in session
$_SESSION['chat_user'] = [
    "employee_id" => $row['employee_id'],
    "full_name"   => $row['full_name'],
    "img"         => $imgBase64
];

echo json_encode([
    "status"      => "success",
    "employee_id" => $row['employee_id'],
    "full_name"   => $row['full_name'],
    "img"         => $imgBase64
]);
exit;
?>
