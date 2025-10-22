<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

include 'conn.php';
date_default_timezone_set('Asia/Manila');

$input = json_decode(file_get_contents('php://input'), true);

$username = trim($input['username'] ?? '');
$remarks  = isset($input['remarks']) ? trim($input['remarks']) : null;
$status   = isset($input['status']) ? trim($input['status']) : null;
$date     = trim($input['date'] ?? '');

if (!$username) {
    echo json_encode(['status' => 'error', 'message' => 'Missing username']);
    exit;
}

if ($date) {
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid date format']);
        exit;
    }
    $date = date('Y-m-d', $timestamp);
} else {
    $date = date('Y-m-d');
}

$checkSql = "SELECT time_in, time_out, remarks, status FROM [sen_template_db].[dbo].[worklog] WHERE name = ? AND date = ?";
$checkStmt = sqlsrv_query($conn, $checkSql, [$username, $date]);

if ($checkStmt === false) {
    echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
    exit;
}

$row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

if ($row) {

    $fields = [];
    $params = [];

    if ($remarks !== null) {
        $fields[] = "remarks = ?";
        $params[] = $remarks;
    }
    if ($status !== null) {
        $fields[] = "status = ?";
        $params[] = $status;
    }

    if (!empty($fields)) {
        $params[] = $username;
        $params[] = $date;
        $updateSql = "UPDATE [sen_template_db].[dbo].[worklog] SET " . implode(', ', $fields) . " WHERE name = ? AND date = ?";
        $updateStmt = sqlsrv_query($conn, $updateSql, $params);

        if ($updateStmt === false) {
            echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
            exit;
        }
    }

    echo json_encode([
        'status' => 'updated',
        'time_in' => $row['time_in'] ? $row['time_in']->format('H:i:s') : '',
        'time_out' => $row['time_out'] ? $row['time_out']->format('H:i:s') : '',
        'remarks' => $remarks ?? $row['remarks'],
        'status_value' => $status ?? $row['status']
    ]);
} else {

    $insertFields = ['name', 'date'];
    $insertValues = [$username, $date];
    $placeholders = ['?', '?'];

    if ($remarks !== null) {
        $insertFields[] = 'remarks';
        $insertValues[] = $remarks;
        $placeholders[] = '?';
    }
    if ($status !== null) {
        $insertFields[] = 'status';
        $insertValues[] = $status;
        $placeholders[] = '?';
    }


    $insertFields[] = 'time_in';
    $insertValues[] = null;
    $placeholders[] = '?';

    $insertFields[] = 'time_out';
    $insertValues[] = null;
    $placeholders[] = '?';

    $insertSql = "INSERT INTO [sen_template_db].[dbo].[worklog] (" . implode(',', $insertFields) . ") VALUES (" . implode(',', $placeholders) . ")";
    $insertStmt = sqlsrv_query($conn, $insertSql, $insertValues);

    if ($insertStmt === false) {
        echo json_encode(['status' => 'error', 'message' => sqlsrv_errors()]);
        exit;
    }

    echo json_encode([
        'status' => 'inserted',
        'time_in' => '',
        'time_out' => '',
        'remarks' => $remarks ?? '',
        'status_value' => $status ?? ''
    ]);
}
?>
