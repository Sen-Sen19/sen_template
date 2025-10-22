<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conn.php';
date_default_timezone_set('Asia/Manila');

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');
$date = trim($input['date'] ?? '');

if (!$username || !$date) {
    echo json_encode(['status' => 'error', 'message' => 'Missing username or date']);
    exit;
}

// Clean date string: keep only YYYY-MM-DD
$dateOnly = substr(preg_replace('/[^0-9\-]/', '', $date), 0, 10);

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateOnly)) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
    exit;
}

// Query for time_in, time_out, remarks, status
$sql = "
    SELECT 
        CONVERT(varchar(8), time_in, 108) AS time_in,
        CONVERT(varchar(8), time_out, 108) AS time_out,
        remarks,
        status
    FROM [sen_template_db].[dbo].[worklog]
    WHERE name = ? AND date = ?
";

$params = [$username, $dateOnly];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    $errors = array_map(function($e){ return $e['message']; }, sqlsrv_errors());
    echo json_encode(['status' => 'error', 'message' => $errors]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($row) {
    echo json_encode([
        'status' => 'success',
        'time_in' => $row['time_in'] ?? '',
        'time_out' => $row['time_out'] ?? '',
        'remarks' => $row['remarks'] ?? '',
        'status_value' => $row['status'] ?? '' 
    ]);
} else {
    echo json_encode([
        'status' => 'empty',
        'time_in' => '',
        'time_out' => '',
        'remarks' => '',
        'status_value' => ''
    ]);
}
