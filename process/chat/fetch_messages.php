<?php
header("Content-Type: application/json");
require "../conn.php";
session_start();

if (!isset($_SESSION['chat_user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    http_response_code(401);
    exit;
}

// Fetch messages
$sql = "
    SELECT TOP (1000)
        m.message_id,
        m.employee_id,
        m.full_name,
        m.message,
        m.datetime,
        m.reply_to_id,
        m.react,
        m.message_history,
        m.attachment,
        m.attachment_name,
        m.attachment_type,
        r.full_name AS reply_full_name,
        r.message AS reply_message
    FROM [sen_template_db].[dbo].[messages] m
    LEFT JOIN [sen_template_db].[dbo].[messages] r
        ON m.reply_to_id = r.message_id
    ORDER BY m.datetime ASC
";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Query failed"]);
    exit;
}

$messages = [];

while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $datetimeStr = $row['datetime'] instanceof DateTime 
        ? $row['datetime']->format('Y-m-d H:i:s')
        : date('Y-m-d H:i:s');

    // Decode reactions JSON
    $reactions = [];
    if (!empty($row['react'])) {
        $decoded = json_decode($row['react'], true);
        if (is_array($decoded)) {
            foreach ($decoded as $r) {
                $reactions[] = [
                    "emoji" => $r['emoji'] ?? "",
                    "users" => $r['users'] ?? []
                ];
            }
        }
    }

    // Handle attachment
    $attachmentBase64 = null;
    if (!empty($row['attachment'])) {
        $fileData = is_resource($row['attachment']) ? stream_get_contents($row['attachment']) : $row['attachment'];
        
        // Determine MIME type
        $mimeType = $row['attachment_type'] ?? null;
        if (!$mimeType) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_buffer($finfo, $fileData);
            finfo_close($finfo);
            if (!$mimeType) $mimeType = 'application/octet-stream';
        }

        // Convert to data URL
        $attachmentBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($fileData);
    }

    $messages[] = [
        "id"               => $row['message_id'],
        "employee_id"      => $row['employee_id'],
        "full_name"        => $row['full_name'],
        "message"          => $row['message'],
        "datetime"         => $datetimeStr,
        "reply_to_id"      => $row['reply_to_id'],
        "reply_full_name"  => $row['reply_full_name'] ?? null,
        "reply_message"    => $row['reply_message'] ?? null,
        "reactions"        => $reactions,
        "message_history"  => $row['message_history'] ?? null,
        "edited"           => !empty($row['message_history']),
        "attachment"       => $attachmentBase64,
        "attachment_name"  => $row['attachment_name'] ?? null,
        "attachment_type"  => $row['attachment_type'] ?? null
    ];
}

echo json_encode($messages);
exit;
?>
