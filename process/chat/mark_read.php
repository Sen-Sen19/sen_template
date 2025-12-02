<?php
header("Content-Type: application/json");
require "../conn.php";
session_start();

$user_id = $_SESSION['chat_user']['employee_id'] ?? null;
$data = json_decode(file_get_contents("php://input"), true);
$messageIds = $data['messageIds'] ?? [];

if (!$user_id || empty($messageIds)) {
    echo json_encode(["status" => "error", "message" => "Missing user or message IDs"]);
    exit;
}

foreach ($messageIds as $msgId) {
    if ($msgId == null) continue;

    // Check if this user already marked as read
    $checkStmt = sqlsrv_query($conn, "
        SELECT 1 FROM message_reads WHERE message_id = ? AND employee_id = ?
    ", [$msgId, $user_id]);

    $alreadyRead = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if (!$alreadyRead) {
        // Insert read record with timestamp
        $insertStmt = sqlsrv_query($conn, "
            INSERT INTO message_reads (message_id, employee_id, read_at)
            VALUES (?, ?, GETDATE())
        ", [$msgId, $user_id]);

        if ($insertStmt === false) {
            error_log("Failed to insert read: " . print_r(sqlsrv_errors(), true));
        }
    }
}

echo json_encode(["status" => "success"]);
exit;
?>
