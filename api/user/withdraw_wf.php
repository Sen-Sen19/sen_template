<?php
/**
 * |--------------------------------------------------------------------------
 * | Withdraw WF item
 * |--------------------------------------------------------------------------
 * | One row per WF KEY in factory_supplies_db.dbo.wf_withdrawals.
 * | First withdrawal inserts the row, later ones add to actual_quantity.
 * | total_php / total_jpy are computed columns, so they update by themselves.
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
$invoice  = trim($_POST['invoice_no'] ?? '');
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

/* ---------- Save (inside a transaction so two users cannot over-withdraw) ---------- */

if (!sqlsrv_begin_transaction($conn_sen)) {
    error_log(print_r(sqlsrv_errors(), true));
    respond(false, 'Could not save the withdrawal. Please try again.', 500);
}

// One row per WF KEY. Lock it (or the empty spot for it) while we check what was already withdrawn
$chk = sqlsrv_query(
    $conn_sen,
    "SELECT id,
            CAST(actual_quantity AS FLOAT) AS withdrawn,
            CAST(quantity AS FLOAT)        AS quantity
     FROM dbo.wf_withdrawals WITH (UPDLOCK, HOLDLOCK) WHERE wfkey = ?",
    [$wfkey]
);

if ($chk === false) {
    error_log(print_r(sqlsrv_errors(), true));
    sqlsrv_rollback($conn_sen);
    respond(false, 'Could not save the withdrawal. Please try again.', 500);
}

$existing  = sqlsrv_fetch_array($chk, SQLSRV_FETCH_ASSOC); // null when this WF KEY has no row yet
$withdrawn = $existing ? (float)$existing['withdrawn'] : 0.0;
$maxQty    = $existing ? (float)$existing['quantity']  : $quantity; // saved quantity wins over the browser's
$remaining = round($maxQty - $withdrawn, 3);

if ($remaining <= 0) {
    sqlsrv_rollback($conn_sen);
    respond(false, 'This item was already fully withdrawn.', 409, ['withdrawn' => $withdrawn, 'remaining' => 0]);
}

if ($actual > $remaining + 0.0005) {
    sqlsrv_rollback($conn_sen);
    respond(false, "Only {$remaining} left to withdraw ({$withdrawn} already withdrawn).", 409,
        ['withdrawn' => $withdrawn, 'remaining' => $remaining]);
}

if ($existing) {
    // Add to the same row. total_php / total_jpy are computed columns, so they follow automatically.
    // withdraw_by / ip_address / withdraw_datetime become those of the latest withdrawal.
    $stmt = sqlsrv_query(
        $conn_sen,
        "UPDATE dbo.wf_withdrawals
            SET actual_quantity   = actual_quantity + ?,
                withdraw_by       = ?,
                ip_address        = ?,
                withdraw_datetime = ?
          WHERE id = ?",
        [$actual, $withdrawBy, $ip, $withdrawAt, $existing['id']]
    );
    $rowId = $existing['id'];
} else {
    $stmt = sqlsrv_query(
        $conn_sen,
        "INSERT INTO dbo.wf_withdrawals
            (wfkey, invoice_no, requested_section_name, item_name, unit_code, quantity, actual_quantity,
             php, jpy, requested_user_name, withdraw_by, ip_address, withdraw_datetime)
         OUTPUT INSERTED.id
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
        [$wfkey, $invoice, $section, $item, $unit, $quantity, $actual,
         $php, $jpy, $reqUser, $withdrawBy, $ip, $withdrawAt]
    );
    $rowId = $stmt ? (sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['id'] ?? null) : null;
}

if ($stmt === false) {
    error_log(print_r(sqlsrv_errors(), true)); // keep DB details out of the browser
    sqlsrv_rollback($conn_sen);
    respond(false, 'Could not save the withdrawal. Please try again.', 500);
}

sqlsrv_commit($conn_sen);

$newWithdrawn = round($withdrawn + $actual, 3);
$newRemaining = round($maxQty - $newWithdrawn, 3);

respond(
    true,
    $newRemaining <= 0 ? 'Item fully withdrawn.' : "Withdrawal saved. {$newRemaining} remaining.",
    200,
    ['id' => $rowId, 'withdrawn' => $newWithdrawn, 'remaining' => max($newRemaining, 0)]
);