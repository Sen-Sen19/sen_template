<?php
include '../conn.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['datetime'], $data['message'], $data['message_id'])) {
    echo json_encode(["status" => "error", "message" => "Missing parameters"]);
    exit;
}

$datetime = $data['datetime'];
$newMessage = $data['message'];
$messageId = $data['message_id'];

// Trim milliseconds for SQL comparison
$datetimeTrimmed = substr($datetime, 0, 19);

// 1️⃣ Get the original message
$sql = "SELECT message, message_history FROM [sen_template_db].[dbo].[messages] 
        WHERE message_id = ? AND CONVERT(VARCHAR(19), datetime, 120) = ?";
$params = [$messageId, $datetimeTrimmed];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$row) {
    echo json_encode(["status" => "error", "message" => "Message not found"]);
    exit;
}

// Check if already edited
if (!empty($row['message_history'])) {
    echo json_encode(["status" => "error", "message" => "Message already edited"]);
    exit;
}

// 2️⃣ Update message, move old message to history, mark edited
// Only include `edited` column if it exists
$checkColumn = sqlsrv_query($conn, "
    SELECT COLUMN_NAME 
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_NAME = 'messages' AND COLUMN_NAME = 'edited'
");
$hasEditedColumn = sqlsrv_fetch_array($checkColumn, SQLSRV_FETCH_ASSOC);

if ($hasEditedColumn) {
    $sqlUpdate = "UPDATE [sen_template_db].[dbo].[messages] 
                  SET message = ?, message_history = ?, edited = 1
                  WHERE message_id = ? AND CONVERT(VARCHAR(19), datetime, 120) = ?";
} else {
    $sqlUpdate = "UPDATE [sen_template_db].[dbo].[messages] 
                  SET message = ?, message_history = ?
                  WHERE message_id = ? AND CONVERT(VARCHAR(19), datetime, 120) = ?";
}

$paramsUpdate = [$newMessage, $row['message'], $messageId, $datetimeTrimmed];
$stmtUpdate = sqlsrv_query($conn, $sqlUpdate, $paramsUpdate);

if ($stmtUpdate === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo json_encode(["status" => "success"]);
?>
