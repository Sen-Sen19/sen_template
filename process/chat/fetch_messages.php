<?php
header("Content-Type: application/json");
require_once "../conn.php";

session_start();

// Check if user is logged in
if (!isset($_SESSION['chat_user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    http_response_code(401);
    exit;
}

// Fetch last 100 messages
$sql = "
    SELECT TOP (1000) employee_id, full_name, message, datetime
    FROM [sen_template_db].[dbo].[messages]
    ORDER BY datetime ASC
";


$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Query failed"]);
    exit;
}

$messages = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $messages[] = [
        "full_name" => $row['full_name'],
        "message"   => $row['message'],
        "datetime"  => $row['datetime']->format('Y-m-d H:i:s')
    ];
}

echo json_encode($messages);
exit;
?>
