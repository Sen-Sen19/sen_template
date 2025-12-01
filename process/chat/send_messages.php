<?php
header("Content-Type: application/json");
require "../conn.php";
session_start();

if (!isset($_SESSION['chat_user'])) {
    echo json_encode(["status"=>"error","message"=>"Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$message     = trim($data['message'] ?? '');
$reply_to_id = $data['reply_to_id'] ?? null;

$user = $_SESSION['chat_user'];
$full_name   = $user['full_name'];
$employee_id = $user['employee_id'];
$ip_address  = $_SERVER['REMOTE_ADDR'];

// Validate
if (!$message) {
    echo json_encode(["status"=>"error","message"=>"Missing message"]);
    exit;
}

// If replying, get original message text for reference
$reply_text = null;
if ($reply_to_id && $reply_to_id != 0) {
    $sqlReply = "SELECT message, full_name FROM [sen_template_db].[dbo].[messages] WHERE message_id = ?";
    $stmtReply = sqlsrv_query($conn, $sqlReply, [$reply_to_id]);
    if ($stmtReply && ($row = sqlsrv_fetch_array($stmtReply, SQLSRV_FETCH_ASSOC))) {
        $reply_text = $row['message'];
        $reply_full_name = $row['full_name'];
    } else {
        $reply_to_id = null;
    }
}

// Insert new message
$sql = "INSERT INTO [sen_template_db].[dbo].[messages] 
        (employee_id, full_name, message, datetime, reply, reply_to_id, ip_address)
        VALUES (?, ?, ?, GETDATE(), ?, ?, ?)";
$params = [$employee_id, $full_name, $message, $reply_text, $reply_to_id, $ip_address];

$stmt = sqlsrv_query($conn, $sql, $params);
if ($stmt === false) {
    echo json_encode(["status"=>"error","message"=>sqlsrv_errors()]);
    exit;
}

echo json_encode(["status"=>"success"]);
exit;
?>
