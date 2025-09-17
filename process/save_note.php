<?php
include 'conn.php';

$content = $_POST['content'] ?? '';

if ($content === '') {
    echo json_encode(['status' => 'error', 'message' => 'Empty content']);
    exit;
}

// Check if table has at least one row
$checkSql = "SELECT COUNT(*) AS cnt FROM Notes";
$checkStmt = sqlsrv_query($conn, $checkSql);
$row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

if ($row['cnt'] == 0) {
    // Insert if empty
    $sql = "INSERT INTO Notes (content) VALUES (?)";
} else {
    // Update if row exists
    $sql = "UPDATE Notes SET content = ?";
}

$params = array($content);
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
    exit;
}

echo json_encode(['status' => 'success']);
?>
