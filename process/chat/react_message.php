<?php
header("Content-Type: application/json");
require "../conn.php";
session_start();

if (!isset($_SESSION['chat_user'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$message_id = $data['message_id'] ?? null;
$emoji = $data['emoji'] ?? null;

if (!$message_id || !$emoji) {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
    exit;
}

$user = $_SESSION['chat_user'];
$full_name = $user['full_name'];

// Get existing reactions
$sql = "SELECT react FROM [sen_template_db].[dbo].[messages] WHERE message_id = ?";
$stmt = sqlsrv_query($conn, $sql, [$message_id]);
$reactions = [];

if ($stmt && ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC))) {
    if ($row['react']) {
        $reactions = json_decode($row['react'], true);
        if (!is_array($reactions)) $reactions = [];
    }
}

// STEP 1: CHECK if user clicked same emoji (BEFORE REMOVING ANYTHING)
$clickedSameEmoji = false;
foreach ($reactions as $r) {
    if ($r['emoji'] === $emoji && in_array($full_name, $r['users'])) {
        $clickedSameEmoji = true;
        break;
    }
}

// STEP 2: Remove user's old reaction
foreach ($reactions as &$r) {
    $r['users'] = array_values(array_filter($r['users'], fn($u) => $u !== $full_name));
}
unset($r);

// STEP 3: If user clicked SAME emoji → DO NOT re-add (toggle off)
if (!$clickedSameEmoji) {
    $added = false;

    // Add to existing emoji reaction if exists
    foreach ($reactions as &$r) {
        if ($r['emoji'] === $emoji) {
            $r['users'][] = $full_name;
            $added = true;
            break;
        }
    }
    unset($r);

    // Otherwise create new reaction
    if (!$added) {
        $reactions[] = ["emoji" => $emoji, "users" => [$full_name]];
    }
}

// STEP 4: Cleanup
$reactions = array_values(array_filter($reactions, fn($r) => count($r['users']) > 0));

// Save
$react_json = json_encode($reactions, JSON_UNESCAPED_UNICODE);
$sqlUpdate = "UPDATE [sen_template_db].[dbo].[messages] SET react = ? WHERE message_id = ?";
$stmtUpdate = sqlsrv_query($conn, $sqlUpdate, [$react_json, $message_id]);

if ($stmtUpdate === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

echo json_encode(["status" => "success", "reactions" => $reactions]);
exit;
?>
