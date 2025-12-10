<?php
header("Content-Type: application/json");
require "../conn.php";
session_start();

if (!isset($_SESSION['chat_user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user = $_SESSION['chat_user'];
$full_name   = $user['full_name'];
$employee_id = $user['employee_id'];
$ip_address  = $_SERVER['REMOTE_ADDR'];

// --- get input ---
$message     = trim($_POST['message'] ?? '');
$reply_to_id = $_POST['reply_to_id'] ?? 0;

// --- check attachments safely ---
$hasFiles = false;
if (!empty($_FILES['attachments'])) {
    $names = $_FILES['attachments']['name'];
    if (is_array($names)) {
        $hasFiles = count(array_filter($names)) > 0;
    } else {
        $hasFiles = !empty($names);
    }
}

// --- prevent empty send ---
if ($message === "" && !$hasFiles && empty($reply_to_id)) {
    echo json_encode(["status" => "error", "message" => "Missing message or attachments"]);
    exit;
}

// --- get reply text if replying ---
$reply_text = null;
if ($reply_to_id && $reply_to_id != 0) {
    $sqlReply = "SELECT message FROM [sen_template_db].[dbo].[messages] WHERE message_id = ?";
    $stmtReply = sqlsrv_query($conn, $sqlReply, [$reply_to_id]);
    if ($stmtReply && ($row = sqlsrv_fetch_array($stmtReply, SQLSRV_FETCH_ASSOC))) {
        $reply_text = $row['message'];
    } else {
        $reply_to_id = 0;
    }
}

// --- insert message function ---
function insertMessage($conn, $employee_id, $full_name, $message, $reply_text, $reply_to_id, $ip_address, $attachmentContent=null, $attachmentName=null, $attachmentType=null) {
    // Use CONVERT for attachments to avoid varchar -> varbinary conversion issues
    $sql = "INSERT INTO [sen_template_db].[dbo].[messages]
            (employee_id, full_name, message, datetime, reply, reply_to_id, ip_address, attachment, attachment_name, attachment_type)
            VALUES (?, ?, ?, GETDATE(), ?, ?, ?, CONVERT(VARBINARY(MAX), ?), ?, ?)";

    $params = [
        $employee_id,
        $full_name,
        $message,
        $reply_text,
        $reply_to_id,
        $ip_address,
        $attachmentContent,  // raw file content or null
        $attachmentName,     // filename or null
        $attachmentType      // MIME type or null
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);
    if (!$stmt) {
        die(json_encode(["status" => "error", "sqlsrv_error" => sqlsrv_errors()]));
    }

    $sqlId = "SELECT SCOPE_IDENTITY() AS msgId";
    $idStmt = sqlsrv_query($conn, $sqlId);
    $idRow = sqlsrv_fetch_array($idStmt, SQLSRV_FETCH_ASSOC);
    return $idRow['msgId'] ?? null;
}


// --- insert main message if exists ---
$responseIds = [];
if ($message !== "") {
    $msgId = insertMessage($conn, $employee_id, $full_name, $message, $reply_text, $reply_to_id, $ip_address);
    if ($msgId) $responseIds[] = $msgId;
}

// --- insert attachments ---
if ($hasFiles) {
    foreach ($_FILES['attachments']['tmp_name'] as $i => $tmpName) {
        if (!$tmpName) continue;
        $attachmentContent = file_get_contents($tmpName);
        $attachmentName = $_FILES['attachments']['name'][$i];
        $attachmentType = $_FILES['attachments']['type'][$i];

        $msgId = insertMessage($conn, $employee_id, $full_name, "", $reply_text, $reply_to_id, $ip_address, $attachmentContent, $attachmentName, $attachmentType);
        if ($msgId) $responseIds[] = $msgId;
    }
}

echo json_encode(["status" => "success", "message_ids" => $responseIds]);
exit;
