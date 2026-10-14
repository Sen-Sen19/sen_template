<?php
/**
 * |--------------------------------------------------------------------------
 * | Withdraw status
 * |--------------------------------------------------------------------------
 * | Returns how much of a WF KEY was already withdrawn (sum of all rows in
 * | dbo.wf_withdrawals). Used by the Requested Item modal.
 * |--------------------------------------------------------------------------
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Session expired. Please log in again.']);
    exit;
}

require_once __DIR__ . '/../connection/template_conn.php';

$wfkey = trim($_GET['wfkey'] ?? '');
if ($wfkey === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'WF KEY is required.']);
    exit;
}

$stmt = sqlsrv_query(
    $conn_sen,
    "SELECT CAST(ISNULL(SUM(actual_quantity), 0) AS FLOAT) AS withdrawn, COUNT(*) AS entries
     FROM dbo.wf_withdrawals WHERE wfkey = ?",
    [$wfkey]
);

if ($stmt === false) {
    error_log(print_r(sqlsrv_errors(), true));
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not check previous withdrawals.']);
    exit;
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
echo json_encode([
    'success'   => true,
    'withdrawn' => (float)($row['withdrawn'] ?? 0),
    'entries'   => (int)($row['entries'] ?? 0),
]);