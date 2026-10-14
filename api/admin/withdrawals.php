<?php
header('Content-Type: application/json');

session_start();

// Oracle warnings must never break the JSON (they still go to the PHP error log)
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Connections: $conn_sen (factory_supplies_db) and $conn_oracle (V_WFSEARCH)
require_once __DIR__ . '/../connection/template_conn.php';
require_once __DIR__ . '/../connection/fsib_conn.php';

/* ==================== CONFIG ==================== */

// While testing, lookup errors are returned to the browser. Set to false when it works.
const DEBUG = true;

const WITHDRAWALS_TABLE = '[factory_supplies_db].[dbo].[wf_withdrawals]';
const MASTERLIST_TABLE  = '[factory_supplies_db].[dbo].[item_masterlist]';
const WF_VIEW           = 'V_WFSEARCH';
const ORACLE_CHUNK      = 500;  // WF keys per Oracle query (Oracle allows 1000 values in an IN list)
const SQL_CHUNK         = 500;  // item codes per SQL Server query (limit is 2100 parameters)

/* ==================== UTILS ==================== */

function respond(array $data, int $code = 200): void
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function dbFail(string $message = 'Database error.'): void
{
    error_log('withdrawals.php: ' . print_r(sqlsrv_errors(), true));
    respond(['status' => false, 'message' => $message], 500);
}

function validDate(string $value): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

function numOrNull($v): ?float
{
    return ($v === null || $v === '') ? null : (float)$v;
}

function clean($v)
{
    return is_string($v) ? trim($v) : $v;
}

// Same match on both sides: trimmed + upper-cased key and item name
function wfMatchKey($key, $itemName): string
{
    return strtoupper(trim((string)$key)) . '|' . strtoupper(trim((string)$itemName));
}

/* ==================== AUTH ==================== */

if (empty($_SESSION['logged_in']) || strtolower($_SESSION['role'] ?? '') !== 'admin') {
    respond(['status' => false, 'message' => 'Unauthorized.'], 403);
}

// Release the session lock so other requests are not blocked while the queries run
session_write_close();

/* ==================== ORACLE: WF DETAILS ==================== */

// Returns [map of "WFKEY|ITEMNAME" => details, error message or null]
function fetchWfDetails(array $wfkeys): array
{
    global $conn_oracle;

    if (!$wfkeys) {
        return [[], null];
    }
    if (!isset($conn_oracle) || !$conn_oracle) {
        return [[], 'Oracle connection failed.'];
    }

    // The view converts text dates without a format, so it relies on the session format
    $nls = oci_parse($conn_oracle, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY/MM/DD'");
    if ($nls) @oci_execute($nls);

    $map = [];

    foreach (array_chunk($wfkeys, ORACLE_CHUNK) as $chunk) {
        $names = [];
        foreach ($chunk as $i => $_) {
            $names[] = ':k' . $i;
        }

        $sql = 'SELECT C_WFKEY, C_HINBANNM, C_BUMONCD, C_KOUNYUCD, C_KOUNYUNM, C_FUTANBUMONCD, C_FUTANBUMONNM, C_HINBANCD, L_SURYO
                FROM "' . WF_VIEW . '"
                WHERE UPPER(TRIM(C_WFKEY)) IN (' . implode(',', $names) . ')';

        $stid = oci_parse($conn_oracle, $sql);
        if (!$stid) {
            return [$map, 'Could not parse the Oracle query.'];
        }
        foreach ($chunk as $i => $_) {
            oci_bind_by_name($stid, ':k' . $i, $chunk[$i]);
        }

        if (!@oci_execute($stid)) {
            $e = oci_error($stid);
            error_log('withdrawals.php (oracle): ' . ($e['message'] ?? 'Oracle error'));
            return [$map, $e['message'] ?? 'Oracle error'];
        }

        while ($r = oci_fetch_assoc($stid)) {
            $k = wfMatchKey($r['C_WFKEY'] ?? '', $r['C_HINBANNM'] ?? '');
            if (isset($map[$k])) continue; // first match wins if the view repeats a line
            $map[$k] = [
                'requested_section_code' => trim((string)($r['C_BUMONCD'] ?? '')),
                'supplier_code'          => trim((string)($r['C_KOUNYUCD'] ?? '')),
                'supplier_name'          => trim((string)($r['C_KOUNYUNM'] ?? '')),
                'end_use_section_code'   => trim((string)($r['C_FUTANBUMONCD'] ?? '')),
                'end_use_section'        => trim((string)($r['C_FUTANBUMONNM'] ?? '')),
                'item_code'              => trim((string)($r['C_HINBANCD'] ?? '')),
                'fsib_quantity'          => numOrNull($r['L_SURYO'] ?? null),
            ];
        }
        oci_free_statement($stid);
    }

    return [$map, null];
}

/* ==================== ITEM MASTERLIST: UNIT COSTS ==================== */

// Returns [map of "CODE|NAME" => unit costs, error message or null]
function fetchUnitCosts($conn, array $itemCodes): array
{
    if (!$itemCodes) {
        return [[], null];
    }

    $map = [];

    foreach (array_chunk($itemCodes, SQL_CHUNK) as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $sql = 'SELECT [product_code], [product_name], [unit_cost_jpy], [unit_cost_php]
                FROM ' . MASTERLIST_TABLE . '
                WHERE UPPER(LTRIM(RTRIM([product_code]))) IN (' . $placeholders . ')';

        $stmt = sqlsrv_query($conn, $sql, array_values($chunk));
        if ($stmt === false) {
            error_log('withdrawals.php (masterlist): ' . print_r(sqlsrv_errors(), true));
            return [$map, 'Could not read the item masterlist.'];
        }

        while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $k = wfMatchKey($r['product_code'], $r['product_name']);
            if (isset($map[$k])) continue; // first match wins
            $map[$k] = [
                'unit_cost_jpy' => numOrNull($r['unit_cost_jpy']),
                'unit_cost_php' => numOrNull($r['unit_cost_php']),
            ];
        }
        sqlsrv_free_stmt($stmt);
    }

    return [$map, null];
}

/* ==================== LIST ==================== */

function listWithdrawals($conn): void
{
    $from    = trim($_GET['from'] ?? '');
    $to      = trim($_GET['to'] ?? '');
    $account = trim($_GET['account'] ?? '');

    if (!validDate($from) || !validDate($to)) {
        respond(['status' => false, 'message' => 'Pick a valid start and end date.'], 422);
    }
    if ($from > $to) {
        respond(['status' => false, 'message' => 'The start date must be before the end date.'], 422);
    }

    // "To" is inclusive: everything before the start of the next day
    $end = (new DateTime($to))->modify('+1 day')->format('Y-m-d');

    $where  = ['[withdraw_datetime] >= ?', '[withdraw_datetime] < ?'];
    $params = [$from, $end];

    if ($account !== '') {
        $where[]  = '[requested_section_name] = ?';
        $params[] = $account;
    }

    $sql = 'SELECT [id], [wfkey], [requested_section_name], [item_name], [unit_code],
                   [quantity], [actual_quantity], [php], [jpy], [total_php], [total_jpy],
                   [requested_user_name], [withdraw_datetime], [invoice_no]
            FROM ' . WITHDRAWALS_TABLE . '
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY [withdraw_datetime] DESC, [id] DESC';

    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) dbFail();

    $data = [];
    while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $data[] = [
            'id'                     => $r['id'],
            'wfkey'                  => clean($r['wfkey']),
            'requested_section_name' => clean($r['requested_section_name']),
            'item_name'              => clean($r['item_name']),
            'unit_code'              => clean($r['unit_code']),
            'quantity'               => numOrNull($r['quantity']),
            'actual_quantity'        => numOrNull($r['actual_quantity']),
            'php'                    => numOrNull($r['php']),
            'jpy'                    => numOrNull($r['jpy']),
            'total_php'              => numOrNull($r['total_php']),
            'total_jpy'              => numOrNull($r['total_jpy']),
            'requested_user_name'    => clean($r['requested_user_name']),
            'withdraw_datetime'      => $r['withdraw_datetime'] instanceof DateTime ? $r['withdraw_datetime']->format('Y-m-d H:i') : $r['withdraw_datetime'],
            'invoice_no'             => clean($r['invoice_no']),
            // filled from Oracle below
            'requested_section_code' => '',
            'supplier_code'          => '',
            'supplier_name'          => '',
            'end_use_section_code'   => '',
            'end_use_section'        => '',
            'item_code'              => '',
            'fsib_quantity'          => null,
            // filled from the item masterlist below
            'unit_cost_jpy'          => null,
            'unit_cost_php'          => null,
        ];
    }
    sqlsrv_free_stmt($stmt);

    // Join with V_WFSEARCH on WF key + item name (done in PHP because it is a separate connection)
    $wfkeys = [];
    foreach ($data as $row) {
        $k = strtoupper(trim((string)$row['wfkey']));
        if ($k !== '') $wfkeys[$k] = true;
    }
    [$details, $lookupError] = fetchWfDetails(array_keys($wfkeys));

    foreach ($data as &$row) {
        $d = $details[wfMatchKey($row['wfkey'], $row['item_name'])] ?? null;
        if ($d) {
            $row = array_merge($row, $d);
        }
    }
    unset($row);

    // Unit costs from the item masterlist: item code (from Oracle) + item name
    $itemCodes = [];
    foreach ($data as $row) {
        $c = strtoupper(trim((string)$row['item_code']));
        if ($c !== '') $itemCodes[$c] = true;
    }
    [$costs, $costError] = fetchUnitCosts($conn, array_keys($itemCodes));

    foreach ($data as &$row) {
        $c = $costs[wfMatchKey($row['item_code'], $row['item_name'])] ?? null;
        if ($c) {
            $row = array_merge($row, $c);
        }
    }
    unset($row);

    $out = ['status' => true, 'total' => count($data), 'data' => $data];
    if (DEBUG) {
        $out['lookup_error']      = $lookupError; // visible in the Network tab
        $out['cost_lookup_error'] = $costError;
    }
    respond($out);
}

/* ==================== ROUTER ==================== */

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list': listWithdrawals($conn_sen); break;
    default:     respond(['status' => false, 'message' => 'Unknown action.'], 400);
}