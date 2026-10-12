<?php
session_start();
header('Content-Type: application/json');

// Never print PHP/Oracle warnings into the response, they break the JSON.
// They still go to the PHP error log.
ini_set('display_errors', '0');
ini_set('log_errors', '1');

/* ==================== CONFIG ==================== */

// While testing, the real Oracle error is returned. Set to false when it works.
const DEBUG = true;

const SOURCE_VIEW  = 'V_WFSEARCH';
const RATE_VIEW    = 'V_FALP_PO_LIST';
const MAX_PAGE     = 100;
const DEFAULT_PAGE = 15;

// Columns sent to the browser, in this order (JPY_PRICE is computed, not read from the view)
const OUTPUT_COLUMNS = [
    'C_WFKEY',
    'C_BUMONNM',
    'C_HINBANNM',
    'C_TANICD',
    'L_SURYO',
    'L_TANKA',
    'JPY_PRICE',
    'C_SINSEISHANM',
    'C_SEIKYUNO',     // not shown in the table, used by the details popup
];

const COMPUTED_COLUMNS = ['JPY_PRICE'];

/* ==================== UTILS ==================== */

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function ociFail($handle): void
{
    $error = oci_error($handle);
    error_log('fetch_requests.php: ' . ($error['message'] ?? 'Oracle error'));
    $message = 'Database error.';
    if (DEBUG) {
        $message = 'Database error: ' . ($error['message'] ?? 'Oracle error');
    }
    respond(['success' => false, 'message' => $message], 500);
}

/* ==================== AUTH ==================== */

if (empty($_SESSION['logged_in'])) {
    respond(['success' => false, 'message' => 'Unauthorized.'], 401);
}

// Matched against C_BUMONNM. It comes from the session only, never from the browser,
// so a user cannot request another account's data by editing the URL
$section = trim($_SESSION['account_name'] ?? '');
if ($section === '') {
    respond(['success' => false, 'message' => 'No account name is assigned to your account.'], 403);
}

// Release the session lock so other requests are not blocked while Oracle runs
session_write_close();

/* ==================== INPUT ==================== */

$dateFrom = trim($_GET['date_from'] ?? '');
$dateTo   = trim($_GET['date_to'] ?? '');
$seikyuNo = trim($_GET['c_seikyuno'] ?? '');
$offset   = max(0, (int)($_GET['offset'] ?? 0));
$limit    = min(MAX_PAGE, max(1, (int)($_GET['limit'] ?? DEFAULT_PAGE)));

if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
    respond(['success' => false, 'message' => 'date_from and date_to are required (YYYY-MM-DD).'], 422);
}

// DT_KENSHU_MAX is compared as text in the YYYY/MM/DD form (the way that worked).
// The end is "the day after date_to", compared with <, so the whole end day is included.
$dateFromOra = str_replace('-', '/', $dateFrom);
$dateToOra   = (new DateTime($dateTo))->modify('+1 day')->format('Y/m/d');

/* ==================== CONNECTION ==================== */

require __DIR__ . '/../connection/fsib_conn.php';

if (!isset($conn_oracle) || !$conn_oracle) {
    respond(['success' => false, 'message' => 'Oracle connection failed.'], 500);
}

/* ==================== SESSION DATE FORMAT ==================== */

// V_FALP_PO_LIST / V_WFSEARCH hold dates as text like 2026/04/06 and the views convert them
// without a format, so they rely on the session's date format. Without this line Oracle falls
// back to its default format and raises ORA-01861.
$nls = oci_parse($conn_oracle, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY/MM/DD'");
if (!$nls || !@oci_execute($nls)) {
    error_log('fetch_wf.php: could not set NLS_DATE_FORMAT');
}

/* ==================== COLUMNS ==================== */

// Check that the view still has every column we use
$view = SOURCE_VIEW;
$stid = oci_parse($conn_oracle, 'SELECT column_name FROM user_tab_columns WHERE table_name = :table_name');
oci_bind_by_name($stid, ':table_name', $view);
if (!oci_execute($stid)) ociFail($stid);

$viewColumns = [];
while ($row = oci_fetch_assoc($stid)) {
    $viewColumns[] = $row['COLUMN_NAME'];
}
oci_free_statement($stid);

// Filter columns and the join keys are required too, even though they are not all shown
$needed = array_merge(
    array_diff(OUTPUT_COLUMNS, COMPUTED_COLUMNS),
    ['DT_KENSHU_MAX', 'C_BUMONNM', 'C_SEIKYUNO', 'C_HINBANNM', 'L_TANKA']
);
foreach ($needed as $required) {
    if (!in_array($required, $viewColumns, true)) {
        respond(['success' => false, 'message' => "Column $required does not exist in " . SOURCE_VIEW . '.'], 500);
    }
}

$columns = OUTPUT_COLUMNS;

// Real view columns to read: the output ones, plus C_SEIKYUNO / C_HINBANNM / L_TANKA for the JPY lookup
$readColumns = array_values(array_unique(array_merge(
    array_diff(OUTPUT_COLUMNS, COMPUTED_COLUMNS),
    ['C_SEIKYUNO', 'C_HINBANNM', 'L_TANKA']
)));
$selectList = implode(', ', array_map(fn($c) => 'w."' . $c . '"', $readColumns));

// Exchange rate from V_FALP_PO_LIST, matched on invoice no. + item name with both sides
// trimmed and upper-cased (the same match as the PO page). A scalar subquery returns one
// value per request row, so a repeated PO line can never duplicate a row here.
//
// V_FALP_PO_LIST raised ORA-01861 when queried without a TRANS_DATE range (your PO page always
// filters on it with TO_DATE), so the same kind of range is added here. It is the requested
// date range padded by 90 days each side, since the PO is posted around the receiving date.
// The dates are rebuilt from validated input, so they are safe to put in the SQL text.
$poFrom = (new DateTime($dateFrom))->modify('-90 days')->format('Y/m/d');
$poTo   = (new DateTime($dateTo))->modify('+91 days')->format('Y/m/d');

$rateSelect = '(SELECT MAX(p."EXCHANGE_RATE")
                FROM "' . RATE_VIEW . '" p
                WHERE p."TRANS_DATE" >= TO_DATE(\'' . $poFrom . '\', \'YYYY/MM/DD\')
                  AND p."TRANS_DATE" <  TO_DATE(\'' . $poTo . '\', \'YYYY/MM/DD\')
                  AND UPPER(TRIM(p."INVOICE_NO")) = UPPER(TRIM(w."C_SEIKYUNO"))
                  AND UPPER(TRIM(p."ITEM_NAME"))  = UPPER(TRIM(w."C_HINBANNM"))) AS "EXCHANGE_RATE"';

/* ==================== WHERE (shared by count and page) ==================== */

$conditions = [
    'DT_KENSHU_MAX >= :date_from',
    'DT_KENSHU_MAX < :date_to',
    'UPPER(TRIM(C_BUMONNM)) = UPPER(:section)',
];
if ($seikyuNo !== '') {
    $conditions[] = 'UPPER(C_SEIKYUNO) LIKE UPPER(:c_seikyuno)';
}
$whereClause = implode(' AND ', $conditions);

function bindSharedParams($stid, string &$from, string &$to, string &$section, string $seikyuNo, string &$seikyuLike): void
{
    oci_bind_by_name($stid, ':date_from', $from);
    oci_bind_by_name($stid, ':date_to', $to);
    oci_bind_by_name($stid, ':section', $section);
    if ($seikyuNo !== '') {
        oci_bind_by_name($stid, ':c_seikyuno', $seikyuLike);
    }
}

$seikyuLike = '%' . $seikyuNo . '%';

/* ==================== TOTAL ==================== */

$countStid = oci_parse($conn_oracle, 'SELECT COUNT(*) AS TOTAL FROM "' . SOURCE_VIEW . '" WHERE ' . $whereClause);
bindSharedParams($countStid, $dateFromOra, $dateToOra, $section, $seikyuNo, $seikyuLike);
if (!oci_execute($countStid)) ociFail($countStid);

$countRow = oci_fetch_assoc($countStid);
$total    = (int)($countRow['TOTAL'] ?? 0);
oci_free_statement($countStid);

/* ==================== ONE PAGE ==================== */

function runPage($conn, string $selectItems, string $whereClause, int $offset, int $limit,
                 string &$from, string &$to, string &$section, string $seikyuNo, string &$seikyuLike)
{
    $sql = 'SELECT ' . $selectItems . ' FROM "' . SOURCE_VIEW . '" w
            WHERE ' . $whereClause . '
            ORDER BY DT_KENSHU_MAX DESC
            OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY';

    $stid = oci_parse($conn, $sql);
    if (!$stid) return [null, 'Could not parse the query.'];

    bindSharedParams($stid, $from, $to, $section, $seikyuNo, $seikyuLike);
    oci_bind_by_name($stid, ':offset', $offset);
    oci_bind_by_name($stid, ':limit', $limit);

    if (!@oci_execute($stid)) {
        $e = oci_error($stid);
        return [null, $e['message'] ?? 'Oracle error'];
    }
    return [$stid, null];
}

$rateError = null;
$hasRate   = true;

// First try with the exchange rate. If that fails, load the table without it
// so the page still works (JPY stays blank) and report why.
[$stid, $err] = runPage($conn_oracle, $selectList . ', ' . $rateSelect, $whereClause, $offset, $limit,
                        $dateFromOra, $dateToOra, $section, $seikyuNo, $seikyuLike);

if (!$stid) {
    $rateError = $err;
    $hasRate   = false;
    error_log('fetch_wf.php (rate subquery failed): ' . $err);

    [$stid, $err] = runPage($conn_oracle, $selectList, $whereClause, $offset, $limit,
                            $dateFromOra, $dateToOra, $section, $seikyuNo, $seikyuLike);
    if (!$stid) {
        error_log('fetch_wf.php: ' . $err);
        respond(['success' => false, 'message' => DEBUG ? 'Database error: ' . $err : 'Database error.'], 500);
    }
}

$rows = [];
while ($row = oci_fetch_assoc($stid)) {
    foreach ($row as $key => $value) {
        if (is_object($value) && method_exists($value, 'load')) {
            $row[$key] = $value->load();
        }
    }

    $rate  = $row['EXCHANGE_RATE'] ?? null;
    $tanka = $row['L_TANKA'] ?? null;

    $row['JPY_PRICE'] = (is_numeric($rate) && is_numeric($tanka))
        ? round((float)$tanka * (float)$rate, 2)
        : '';

    // Keep only the columns we promised the browser
    $rows[] = array_intersect_key($row, array_flip(OUTPUT_COLUMNS));
}
oci_free_statement($stid);
oci_close($conn_oracle);

$out = [
    'success' => true,
    'columns' => $columns,
    'data'    => $rows,
    'total'   => $total,
];

if (DEBUG) {
    // Visible in the browser's Network tab; shows why JPY is blank
    $out['rate_debug'] = [
        'rate_query_ok' => $hasRate,
        'error'         => $rateError,
    ];
}

respond($out);