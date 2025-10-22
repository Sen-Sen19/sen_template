<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conn.php';
date_default_timezone_set('Asia/Manila');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$date_time = trim($input['date_time'] ?? '');
$action = trim($input['action'] ?? '');

if (!$username || !$date_time || !$action) {
    echo json_encode(['status' => 'error', 'message' => 'Missing username, date_time or action']);
    exit;
}

$timestamp = strtotime($date_time);
if ($timestamp === false) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date_time format']);
    exit;
}
$dateOnly = date('Y-m-d', $timestamp);
$timeOnly = date('H:i:s', $timestamp);

// Check if row exists
$checkSql = "SELECT * FROM [sen_template_db].[dbo].[worklog] WHERE name = ? AND date = ?";
$checkStmt = sqlsrv_query($conn, $checkSql, [$username, $dateOnly]);

if ($checkStmt === false) {
    echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
    exit;
}

if ($row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
    // Row exists → update
    if ($action === "time_in") {
        $updateSql = "UPDATE [sen_template_db].[dbo].[worklog] SET time_in = ? WHERE name = ? AND date = ?";
        $params = [$timeOnly, $username, $dateOnly];
    } else {
        $updateSql = "UPDATE [sen_template_db].[dbo].[worklog] SET time_out = ? WHERE name = ? AND date = ?";
        $params = [$timeOnly, $username, $dateOnly];
    }
    $stmt = sqlsrv_query($conn, $updateSql, $params);
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
        exit;
    }
    echo json_encode(['status' => 'updated']);
} else {
    // Row doesn't exist → insert
    $time_in = ($action === "time_in") ? $timeOnly : null;
    $time_out = ($action === "time_out") ? $timeOnly : null;

    $insertSql = "INSERT INTO [sen_template_db].[dbo].[worklog] (name, date, time_in, time_out) VALUES (?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, [$username, $dateOnly, $time_in, $time_out]);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
        exit;
    }
    echo json_encode(['status' => 'inserted']);
}
