<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
set_time_limit(120);

function respond(bool $status, array $extra = []): void
{
    echo json_encode(['status' => $status] + $extra);
    exit;
}

/* ==================== AUTH ==================== */

if (empty($_SESSION['logged_in']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    respond(false, ['message' => 'Not signed in.']);
}
if (strtolower(trim($_SESSION['role'] ?? '')) === 'user') {
    http_response_code(403);
    respond(false, ['message' => 'Not allowed.']);
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, ['message' => 'Invalid request.']);
}

require_once __DIR__ . '/../connection/template_conn.php';

/* ==================== INPUT ==================== */

$body   = json_decode(file_get_contents('php://input'), true);
$rows   = $body['rows'] ?? null;
$dryRun = !empty($body['dry_run']);

if (!is_array($rows) || !$rows) {
    respond(false, ['message' => 'The file has no data rows.']);
}
if (count($rows) > 20000) {
    respond(false, ['message' => 'Too many rows. Limit is 20,000 per import.']);
}

// Uploader and datetime are always resolved here, never taken from the client
$addedBy = trim($_SESSION['full_name'] ?? '') ?: (string)($_SESSION['id_no'] ?? '');
$now     = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d H:i:s');

$table = '[factory_supplies_db].[dbo].[item_masterlist]';

/* ==================== HELPERS ==================== */

// Item key = product code + product name, case-insensitive
function keyOf(string $code, string $name): string
{
    return mb_strtoupper(trim($code)) . '||' . mb_strtoupper(trim($name));
}

function parseCost($v): array
{
    $v = str_replace(',', '', trim((string)$v));
    if ($v === '') return [true, null];
    if (!is_numeric($v) || (float)$v < 0) return [false, null];
    return [true, (float)$v];
}

function sameCost($a, $b): bool
{
    if ($a === null || $b === null) return $a === $b;
    return abs($a - $b) < 0.00005;
}

/* ==================== VALIDATE THE FILE ==================== */

$file      = [];  // key => row
$invalid   = [];
$repeated  = 0;
$conflicts = [];  // repeated rows whose costs differ from the first one

foreach ($rows as $r) {
    $rowNo = (int)($r['row'] ?? 0);
    $code  = trim((string)($r['product_code'] ?? ''));
    $name  = trim((string)($r['product_name'] ?? ''));

    // Product Name may be blank: the masterlist already holds items without a name,
    // so a file exported from this page must be able to go back in
    if ($code === '') {
        $invalid[] = ['row' => $rowNo, 'reason' => 'Product Code is required'];
        continue;
    }

    [$okJpy, $jpy] = parseCost($r['unit_cost_jpy'] ?? '');
    [$okPhp, $php] = parseCost($r['unit_cost_php'] ?? '');
    if (!$okJpy || !$okPhp) {
        $invalid[] = ['row' => $rowNo, 'reason' => 'Unit Cost must be a non-negative number'];
        continue;
    }

    $key = keyOf($code, $name);
    if (isset($file[$key])) {
        // Repeated in the file: the first one wins, but warn if the costs disagree
        $repeated++;
        if (!sameCost($file[$key]['jpy'], $jpy) || !sameCost($file[$key]['php'], $php)) {
            $conflicts[] = [
                'row'    => $rowNo,
                'reason' => $code . ' appears again with different costs (row ' . $file[$key]['row'] . ' was used)',
            ];
        }
        continue;
    }

    $file[$key] = ['row' => $rowNo, 'code' => $code, 'name' => $name, 'jpy' => $jpy, 'php' => $php];
}

// Bad rows are skipped and reported; the valid rows still import
if (!$file) {
    respond(false, ['message' => 'No valid rows found. Nothing was changed.', 'invalid' => $invalid]);
}

/* ==================== CURRENT TABLE ==================== */

$stmt = sqlsrv_query(
    $conn_sen,
    "SELECT [product_code], [product_name], [unit_cost_jpy], [unit_cost_php] FROM $table"
);
if (!$stmt) {
    respond(false, ['message' => 'Could not read the masterlist.']);
}

$existing = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $code = (string)$r['product_code'];
    $name = (string)($r['product_name'] ?? '');
    $key  = keyOf($code, $name);
    if (isset($existing[$key])) continue;

    $existing[$key] = [
        'code' => $code,
        'name' => $name,
        'jpy'  => $r['unit_cost_jpy'] === null ? null : (float)$r['unit_cost_jpy'],
        'php'  => $r['unit_cost_php'] === null ? null : (float)$r['unit_cost_php'],
    ];
}
sqlsrv_free_stmt($stmt);

/* ==================== DIFF (add / update / duplicate, never delete) ==================== */

$toAdd = $toUpdate = [];
$duplicates = 0;

foreach ($file as $key => $f) {
    if (!isset($existing[$key])) {
        $toAdd[] = $f;
        continue;
    }

    $e = $existing[$key];

    // A blank cost in the file keeps the current value instead of erasing it
    $newJpy = $f['jpy'] ?? $e['jpy'];
    $newPhp = $f['php'] ?? $e['php'];

    if (sameCost($e['jpy'], $newJpy) && sameCost($e['php'], $newPhp)) {
        $duplicates++;
        continue;
    }

    $toUpdate[] = [
        'code' => $e['code'],   // matched by the stored code/name, which are not changed
        'name' => $e['name'],
        'jpy'  => $newJpy,
        'php'  => $newPhp,
    ];
}

$summary = [
    'added'      => count($toAdd),
    'updated'    => count($toUpdate),
    'duplicates' => $duplicates,
    'repeated'   => $repeated,
    'skipped'    => count($invalid),
    'skipped_rows' => $invalid,
    'warnings'   => $conflicts,
];

// Preview only: nothing is saved
if ($dryRun) {
    respond(true, $summary);
}

if (!$toAdd && !$toUpdate) {
    respond(true, $summary);
}

/* ==================== APPLY (ONE TRANSACTION) ==================== */

if (!sqlsrv_begin_transaction($conn_sen)) {
    respond(false, ['message' => 'Could not start the import.']);
}

function fail($conn, string $msg): void
{
    sqlsrv_rollback($conn);
    respond(false, ['message' => $msg . ' Nothing was changed.']);
}

// 1) Update costs of existing items
$uCode = $uName = $uJpy = $uPhp = null;
$upd = sqlsrv_prepare(
    $conn_sen,
    "UPDATE $table
     SET [unit_cost_jpy] = ?, [unit_cost_php] = ?, [added_by] = ?, [datetime_added] = ?
     WHERE [product_code] = ? AND ISNULL([product_name], '') = ?",
    [&$uJpy, &$uPhp, &$addedBy, &$now, &$uCode, &$uName]
);
if (!$upd) fail($conn_sen, 'Could not prepare the update step.');

foreach ($toUpdate as $f) {
    $uCode = $f['code'];
    $uName = $f['name'];
    $uJpy  = $f['jpy'];
    $uPhp  = $f['php'];
    if (!sqlsrv_execute($upd)) fail($conn_sen, 'Update failed for ' . $f['code'] . '.');
}

// 2) Insert new items
$iCode = $iName = $iJpy = $iPhp = null;
$ins = sqlsrv_prepare(
    $conn_sen,
    "INSERT INTO $table
        ([product_code], [product_name], [added_by], [datetime_added], [unit_cost_jpy], [unit_cost_php])
     VALUES (?, ?, ?, ?, ?, ?)",
    [&$iCode, &$iName, &$addedBy, &$now, &$iJpy, &$iPhp]
);
if (!$ins) fail($conn_sen, 'Could not prepare the insert step.');

foreach ($toAdd as $f) {
    $iCode = $f['code'];
    $iName = $f['name'] === '' ? null : $f['name']; // blank name is stored as NULL
    $iJpy  = $f['jpy'];
    $iPhp  = $f['php'];
    if (!sqlsrv_execute($ins)) fail($conn_sen, 'Insert failed for ' . $f['code'] . '.');
}

sqlsrv_commit($conn_sen);

respond(true, $summary);