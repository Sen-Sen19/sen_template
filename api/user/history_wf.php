<?php
/**
 * |--------------------------------------------------------------------------
 * | Withdrawal history
 * |--------------------------------------------------------------------------
 * | Rows of factory_supplies_db.dbo.wf_withdrawals for the logged-in user's
 * | section, newest first. Optional search on WF KEY / invoice no.
 * |
 * | One row per WF KEY; actual_quantity grows with every withdrawal, and
 * | withdraw_by / withdraw_datetime are those of the latest one.
 * | is_partial is true while actual_quantity is still below quantity.
 * |--------------------------------------------------------------------------
 */

session_start();
header('Content-Type: application/json; charset=utf-8');

const MAX_PAGE     = 100;
const DEFAULT_PAGE = 15;
const MAX_SEARCH   = 60;

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    respond(['success' => false, 'message' => 'Unauthorized.'], 401);
}

// From the session only, so another section's history cannot be requested by editing the URL
$section = trim($_SESSION['account_name'] ?? '');
if ($section === '') {
    respond(['success' => false, 'message' => 'No account name is assigned to your account.'], 403);
}

session_write_close();

require_once __DIR__ . '/../connection/template_conn.php';

/* ---------- Input ---------- */

$q      = trim($_GET['q'] ?? '');
$offset = max(0, (int)($_GET['offset'] ?? 0));
$limit  = min(MAX_PAGE, max(1, (int)($_GET['limit'] ?? DEFAULT_PAGE)));

if (mb_strlen($q) > MAX_SEARCH) {
    respond(['success' => false, 'message' => 'Search text is too long.'], 422);
}

$sectionWhere = 'UPPER(LTRIM(RTRIM(requested_section_name))) = UPPER(?)';

$searchSql    = '';
$searchParams = [];
if ($q !== '') {
    // "!" is the LIKE escape character, so %, _ and [ typed by the user are matched literally
    $like         = '%' . preg_replace('/[!%_\[]/', '!$0', $q) . '%';
    $searchSql    = " AND (wfkey LIKE ? ESCAPE '!' OR invoice_no LIKE ? ESCAPE '!')";
    $searchParams = [$like, $like];
}

function fail($msg = 'Could not load the history.'): void
{
    error_log('history_wf.php: ' . print_r(sqlsrv_errors(), true));
    respond(['success' => false, 'message' => $msg], 500);
}

/* ---------- Total ---------- */

$countStmt = sqlsrv_query(
    $conn_sen,
    "SELECT COUNT(*) AS total FROM dbo.wf_withdrawals WHERE $sectionWhere$searchSql",
    array_merge([$section], $searchParams)
);
if ($countStmt === false) fail();

$total = (int)(sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC)['total'] ?? 0);

/* ---------- One page ---------- */

$sql = "SELECT id, wfkey, invoice_no, requested_section_name, item_name, unit_code,
               CAST(quantity AS FLOAT)        AS quantity,
               CAST(actual_quantity AS FLOAT) AS actual_quantity,
               CAST(php AS FLOAT)             AS php,
               CAST(jpy AS FLOAT)             AS jpy,
               CAST(total_php AS FLOAT)       AS total_php,
               CAST(total_jpy AS FLOAT)       AS total_jpy,
               requested_user_name, withdraw_by,
               CONVERT(VARCHAR(19), withdraw_datetime, 120) AS withdraw_datetime
        FROM dbo.wf_withdrawals
        WHERE $sectionWhere$searchSql
        ORDER BY withdraw_datetime DESC, id DESC
        OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

$stmt = sqlsrv_query($conn_sen, $sql, array_merge([$section], $searchParams, [$offset, $limit]));
if ($stmt === false) fail();

$rows = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    // Not fully withdrawn yet = actual quantity is still below the requested quantity
    $r['total_withdrawn'] = $r['actual_quantity'];
    $r['is_partial']      = round((float)$r['actual_quantity'], 3) < round((float)$r['quantity'], 3);
    $rows[] = $r;
}

respond(['success' => true, 'data' => $rows, 'total' => $total]);