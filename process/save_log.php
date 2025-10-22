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

// ===== Flexible Manila time parsing =====
// Try seconds first, then fallback to minute-only format
$dt = DateTime::createFromFormat('Y-m-d H:i:s', $date_time, new DateTimeZone('Asia/Manila'));
if (!$dt) {
    $dt = DateTime::createFromFormat('Y-m-d H:i', $date_time, new DateTimeZone('Asia/Manila'));
}
if (!$dt) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date_time format']);
    exit;
}

$dateOnly = $dt->format('Y-m-d');
$timeOnly = $dt->format('H:i:s');

// ===== Check if row exists =====
$checkSql = "SELECT * FROM [sen_template_db].[dbo].[worklog] WHERE name = ? AND date = ?";
$checkStmt = sqlsrv_query($conn, $checkSql, [$username, $dateOnly]);

if ($checkStmt === false) {
    echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
    exit;
}

if ($row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
    // ===== Update existing row =====
    if ($action === "time_in") {
        $updateSql = "UPDATE [sen_template_db].[dbo].[worklog] SET time_in = ? WHERE name = ? AND date = ?";
        $params = [$timeOnly, $username, $dateOnly];
    } else { // time_out
        $status = $row['time_in'] ? 'Present' : null; // mark Present only if time_in exists
        $updateSql = "UPDATE [sen_template_db].[dbo].[worklog] SET time_out = ?, status = ? WHERE name = ? AND date = ?";
        $params = [$timeOnly, $status, $username, $dateOnly];
    }

    $stmt = sqlsrv_query($conn, $updateSql, $params);
    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
        exit;
    }
    echo json_encode(['status' => 'updated']);

} else {
    // ===== Insert new row =====
    $time_in = ($action === "time_in") ? $timeOnly : null;
    $time_out = ($action === "time_out") ? $timeOnly : null;
    $status = ($action === "time_out" && $time_in) ? 'Present' : null;

    $insertSql = "INSERT INTO [sen_template_db].[dbo].[worklog] (name, date, time_in, time_out, status) VALUES (?, ?, ?, ?, ?)";
    $stmt = sqlsrv_query($conn, $insertSql, [$username, $dateOnly, $time_in, $time_out, $status]);

    if ($stmt === false) {
        echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
        exit;
    }
    echo json_encode(['status' => 'inserted']);
}
