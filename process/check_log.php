<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conn.php'; // make sure this path is correct

$input = json_decode(file_get_contents('php://input'), true);
$username = trim($input['username'] ?? '');

if (!$username) {
    echo json_encode(['status' => 'error', 'message' => 'Missing username']);
    exit;
}

$today = date('Y-m-d');

$sql = "SELECT TOP 1 time_in, time_out, date
        FROM [sen_template_db].[dbo].[worklog]
        WHERE name = ? AND date = ?";
$params = [$username, $today];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt === false) {
    echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if (!$row) {
    echo json_encode(['status' => 'none']);
    exit;
}

// ✅ Convert SQLSRV DateTime objects to string (HH:MM:SS)
$timeIn = isset($row['time_in']) && $row['time_in'] instanceof DateTime
    ? $row['time_in']->format('H:i:s')
    : $row['time_in'];

$timeOut = isset($row['time_out']) && $row['time_out'] instanceof DateTime
    ? $row['time_out']->format('H:i:s')
    : $row['time_out'];

if ($timeIn && !$timeOut) {
    echo json_encode([
        'status' => 'timed_in',
        'time_in' => $timeIn
    ]);
} elseif ($timeIn && $timeOut) {
    echo json_encode([
        'status' => 'timed_out',
        'time_in' => $timeIn,
        'time_out' => $timeOut
    ]);
} else {
    echo json_encode(['status' => 'none']);
}
