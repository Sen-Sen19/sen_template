<?php
/**
 * |--------------------------------------------------------------------------
 * | Withdraw WF item
 * |--------------------------------------------------------------------------
 * | Saves one withdrawal into factory_supplies_db.dbo.wf_withdrawals.
 * | total_php / total_jpy are computed columns, so they are not inserted.
 * |--------------------------------------------------------------------------
 */

date_default_timezone_set('Asia/Manila');

session_start();
header('Content-Type: application/json; charset=utf-8');

function respond(bool $success, string $message, int $code = 200, array $extra = []): void
{
    http_response_code($code);
    echo json_encode(['success' => $success, 'message' => $message] + $extra);
    exit;
}

// Convert "1,250.50" / "  10 " to a float, or null when it is not a number
function num($v): ?float
{
    $v = str_replace(',', '', trim((string)$v));
    return is_numeric($v) ? (float)$v : null;
}

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    respond(false, 'Session expired. Please log in again.', 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Invalid request method.', 405);
}

require_once __DIR__ . '/../connection/template_conn.php';

/* ---------- Input ---------- */

$wfkey    = trim($_POST['wfkey'] ?? '');
$section  = trim($_POST['requested_section_name'] ?? '');
$item     = trim($_POST['item_name'] ?? '');
$unit     = trim($_POST['unit_code'] ?? '');
$quantity = num($_POST['quantity'] ?? '');
$actual   = num($_POST['actual_quantity'] ?? '');
$php      = num($_POST['php'] ?? '') ?? 0.0;
$jpy      = num($_POST['jpy'] ?? '') ?? 0.0;
$reqUser  = trim($_POST['requested_user_name'] ?? '');

// withdraw_by = full name of the logged-in user
// TODO: set $_SESSION['full_name'] at login (or change the key below to the one you already use)
$withdrawBy = trim((string)($_SESSION['full_name'] ?? $_SESSION['name'] ?? ''));
$ip         = $_SERVER['REMOTE_ADDR'] ?? null;

// Manila time, set here so it does not depend on the SQL Server's own clock/timezone
$withdrawAt = date('Y-m-d H:i:s');

/* ---------- Validation ---------- */

if ($wfkey === '' || $item === '')      respond(false, 'WF KEY and item name are required.', 422);
if ($quantity === null || $quantity <= 0) respond(false, 'Invalid quantity.', 422);
if ($actual === null)                   respond(false, 'Enter the actual quantity.', 422);
if ($actual <= 0)                       respond(false, 'Actual quantity must be greater than 0.', 422);
if ($actual > $quantity)                respond(false, 'Actual quantity cannot exceed the requested quantity.', 422);
if ($withdrawBy === '')                 respond(false, 'Could not find the logged-in user\'s full name.', 401);

/* ---------- Insert ---------- */

$sql = "INSERT INTO dbo.wf_withdrawals
            (wfkey, requested_section_name, item_name, unit_code, quantity, actual_quantity,
             php, jpy, requested_user_name, withdraw_by, ip_address, withdraw_datetime)
        OUTPUT INSERTED.id
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = sqlsrv_query($conn_sen, $sql, [
    $wfkey, $section, $item, $unit, $quantity, $actual,
    $php, $jpy, $reqUser, $withdrawBy, $ip, $withdrawAt
]);

if ($stmt === false) {
    error_log(print_r(sqlsrv_errors(), true)); // keep DB details out of the browser
    respond(false, 'Could not save the withdrawal. Please try again.', 500);
}

$row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
respond(true, 'Withdrawal saved.', 200, ['id' => $row['id'] ?? null]);