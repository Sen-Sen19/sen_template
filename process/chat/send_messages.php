<?php
header("Content-Type: application/json");
require_once "../conn.php"; // your database connection
session_start();

// Check if user is logged in
if (!isset($_SESSION['chat_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    http_response_code(400);
    exit;
}

// Get POSTed JSON data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['message'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    http_response_code(400);
    exit;
}

$message = trim($input['message']);
$employee_id = $_SESSION['chat_user']['employee_id'];
$full_name = $_SESSION['chat_user']['full_name'];

// Insert into SQL Server
$sql = "INSERT INTO [sen_template_db].[dbo].[messages] (employee_id, full_name, message, datetime) VALUES (?, ?, ?, GETDATE())";
$params = [$employee_id, $full_name, $message];

$stmt = sqlsrv_query($conn, $sql, $params);


if ($stmt === false) {
    $errors = sqlsrv_errors();
    echo json_encode(['status' => 'error', 'message' => 'Failed to send message', 'errors' => $errors]);
    http_response_code(500);
    exit;
}

echo json_encode(['status' => 'success']);
exit;
?>
