<?php
header('Content-Type: application/json');
require __DIR__ . '/../connection/fsib_conn1.php';

if (!isset($conn_oracle) || !$conn_oracle) {
    echo json_encode(['success' => false, 'message' => 'Oracle connection failed.']);
    exit;
}

$table   = 'V_FALP_PO_LIST';
$wfTable = 'V_WFSEARCH';

// PO's own display columns
$displayColumns = ['INVOICE_NO', 'ITEM_ID', 'ITEM_NAME', 'PURCHQTY', 'LINE_AMOUNT_MST', 'LINE_AMOUNT', 'LINE_DEPARTMENT'];

// The extra WF columns you want alongside the PO columns.
// Add more here as needed — everything downstream picks this up automatically.
$wfDisplayColumns = [
    'C_WFKEY', 'C_KIGYONO', 'C_BUMONNM', 'C_SINSEISHANM', 'C_FUTANBUMONNM',
    'C_TANICD', 'C_KOUNYUNM', 'C_SHOBUNRUINMSTD', 'C_SEIKYUNO'
];

$dateFromRaw = $_GET['date_from'] ?? '';
$dateToRaw   = $_GET['date_to'] ?? '';
$invoiceNo   = $_GET['invoice_no'] ?? '';
$itemName    = trim($_GET['item_name'] ?? '');
$offset      = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
$limit       = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;

if ($dateFromRaw === '' || $dateToRaw === '') {
    echo json_encode(['success' => false, 'message' => 'date_from and date_to are required.']);
    exit;
}

/* ---------------------------------------------------------
   FIX #1: DATE AS VALIDATED LITERAL, NOT A BIND
   Confirmed: this view throws ORA-01861 on a bound date even for a
   plain COUNT with no ORDER BY. A literal with the exact same value
   works. Regex + checkdate() ensure nothing but a real YYYY-MM-DD
   date can ever reach the SQL string.
--------------------------------------------------------- */
function validateDate($raw) {
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
        return null;
    }
    [$y, $m, $d] = array_map('intval', explode('-', $raw));
    if (!checkdate($m, $d, $y)) {
        return null;
    }
    return str_replace('-', '/', $raw);
}

$dateFromOra = validateDate($dateFromRaw);
$dateToOra   = validateDate($dateToRaw);

if ($dateFromOra === null || $dateToOra === null) {
    echo json_encode(['success' => false, 'message' => 'Invalid date format. Expected YYYY-MM-DD.']);
    exit;
}

function oracle_error_message($resource) {
    $e = oci_error($resource);
    return $e['message'] ?? 'Unknown Oracle error';
}

/* ---------------------------------------------------------
   FIX #2: EXPLICIT NON-VIRTUAL COLUMN LIST, NOT SELECT *
   Confirmed separately: COUNT(*) on this filtered WHERE succeeds, but
   SELECT * with ORDER BY/OFFSET-FETCH on the identical WHERE fails.
   Excluding virtual columns from the SELECT list sidesteps that.

   FIX #3: narrow the safe list down to just the columns the UI
   actually displays, instead of every non-virtual column on the view.

   Same safe-column lookup is reused below for V_WFSEARCH's extra columns.
--------------------------------------------------------- */
function getSafeColumns($conn, $table) {
    $sql = "SELECT column_name, virtual_column
            FROM user_tab_cols
            WHERE table_name = :tname
              AND hidden_column = 'NO'
            ORDER BY column_id";
    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ':tname', $table);
    if (!oci_execute($stid)) {
        return ['ok' => false, 'message' => oracle_error_message($stid)];
    }
    $safe = [];
    $virtual = [];
    while ($row = oci_fetch_assoc($stid)) {
        if (($row['VIRTUAL_COLUMN'] ?? 'NO') === 'YES') {
            $virtual[] = $row['COLUMN_NAME'];
        } else {
            $safe[] = $row['COLUMN_NAME'];
        }
    }
    oci_free_statement($stid);
    return ['ok' => true, 'safe' => $safe, 'virtual' => $virtual];
}

$poCols = getSafeColumns($conn_oracle, $table);
if (!$poCols['ok']) {
    echo json_encode(['success' => false, 'message' => $poCols['message']]);
    exit;
}
$wfCols = getSafeColumns($conn_oracle, $wfTable);
if (!$wfCols['ok']) {
    echo json_encode(['success' => false, 'message' => $wfCols['message']]);
    exit;
}

// Only keep the columns we actually want to display, in our own order,
// but still guarded by each view's real (non-virtual) column list.
$safeDisplayColumns = array_values(array_intersect($displayColumns, $poCols['safe']));
if (empty($safeDisplayColumns)) {
    echo json_encode(['success' => false, 'message' => 'None of the requested display columns were found on V_FALP_PO_LIST.']);
    exit;
}

$safeWfColumns = array_values(array_intersect($wfDisplayColumns, $wfCols['safe']));
if (empty($safeWfColumns)) {
    echo json_encode(['success' => false, 'message' => 'None of the requested display columns were found on V_WFSEARCH.']);
    exit;
}

$poSelectList = implode(', ', array_map(fn($c) => 'PO."' . $c . '"', $safeDisplayColumns));

/* ---------------------------------------------------------
   WHERE CLAUSE
   Same EXISTS filter as before — it only decides which PO rows
   qualify, it does not affect row count or introduce duplicates.
--------------------------------------------------------- */
$quotedTable   = '"' . $table . '"';
$quotedWfTable = '"' . $wfTable . '"';

$conditions = [
    "PO.TRANS_DATE >= TO_DATE('$dateFromOra', 'YYYY/MM/DD')",
    "PO.TRANS_DATE <  TO_DATE('$dateToOra', 'YYYY/MM/DD') + 1",
    "EXISTS (
        SELECT 1 FROM $quotedWfTable WFX
        WHERE WFX.C_SEIKYUNO  = PO.INVOICE_NO
          AND WFX.C_HINBANNM  = PO.ITEM_NAME
    )"
];
if ($invoiceNo !== '') {
    $conditions[] = 'UPPER(PO.INVOICE_NO) LIKE UPPER(:invoice_no)';
}
if ($itemName !== '') {
    $conditions[] = "PO.INVOICE_NO IN (
        SELECT P2.INVOICE_NO FROM $quotedTable P2
        WHERE UPPER(P2.ITEM_NAME) LIKE UPPER(:item_name)
    )";
}
$whereClause = implode(' AND ', $conditions);

function bindSharedParams($stid, $invoiceNo, $itemName) {
    if ($invoiceNo !== '') {
        $invoiceNoLike = '%' . $invoiceNo . '%';
        oci_bind_by_name($stid, ':invoice_no', $invoiceNoLike);
    }
    if ($itemName !== '') {
        $itemNameLike = '%' . $itemName . '%';
        oci_bind_by_name($stid, ':item_name', $itemNameLike);
    }
}
/* ---------------------------------------------------------
   WF DISPLAY COLUMNS: pulled via a LEFT JOIN to a DEDUPED
   V_WFSEARCH subquery, not a raw join.
   A raw JOIN WF ON C_SEIKYUNO/C_HINBANNM can match more than one
   WF row per PO row and duplicate that PO row once per match. The
   subquery collapses WF down to exactly one row per
   (C_SEIKYUNO, C_HINBANNM) first — via ROW_NUMBER(), newest
   DT_WF_COMPLETION wins, in-progress/NULL rows sort last — so the
   join afterward can add AT MOST one WF row per PO row.
   LEFT JOIN (not JOIN) so this never filters rows; EXISTS above
   already does that job.
--------------------------------------------------------- */
$wfSubqueryColumns = array_values(array_unique(array_merge(
    $safeWfColumns,
    ['C_SEIKYUNO', 'C_HINBANNM', 'DT_WF_COMPLETION']
)));
$wfSubquerySelectList = implode(', ', array_map(fn($c) => '"' . $c . '"', $wfSubqueryColumns));

$wfDedupSubquery = "
    SELECT $wfSubquerySelectList
    FROM (
        SELECT $wfSubquerySelectList,
               ROW_NUMBER() OVER (
                   PARTITION BY \"C_SEIKYUNO\", \"C_HINBANNM\"
                   ORDER BY \"DT_WF_COMPLETION\" DESC NULLS LAST
               ) AS RN
        FROM $quotedWfTable
    )
    WHERE RN = 1
";

$fromClause = "$quotedTable PO
    LEFT JOIN ($wfDedupSubquery) WF
        ON WF.C_SEIKYUNO = PO.INVOICE_NO
       AND WF.C_HINBANNM = PO.ITEM_NAME";

$wfSelectList   = implode(', ', array_map(fn($c) => 'WF."' . $c . '"', $safeWfColumns));
$fullSelectList = $poSelectList . ', ' . $wfSelectList;
$allDisplayColumns = array_merge($safeDisplayColumns, $safeWfColumns);

/* ---- COUNT ----
   Counted off PO + EXISTS only (not the WF join). The LEFT JOIN to a
   deduped 1-row-per-key subquery can't change the row count either
   way, so counting off the simpler form avoids the extra subquery cost. */
$countSql = "SELECT COUNT(*) AS TOTAL FROM $quotedTable PO WHERE $whereClause";
$countStid = oci_parse($conn_oracle, $countSql);
bindSharedParams($countStid, $invoiceNo, $itemName);


if (!oci_execute($countStid)) {
    echo json_encode(['success' => false, 'message' => oracle_error_message($countStid)]);
    exit;
}
$countRow = oci_fetch_assoc($countStid);
$total = (int)($countRow['TOTAL'] ?? 0);
oci_free_statement($countStid);

/* ---- PAGE OF ROWS: safe column list, with fallback if still needed ---- */
$dataStid = null;
$usedFallback = false;

$sql = "
    SELECT $fullSelectList
    FROM $fromClause
    WHERE $whereClause
    ORDER BY PO.TRANS_DATE DESC, PO.INVOICE_NO, PO.ITEM_NAME
    OFFSET :offset ROWS FETCH NEXT :limit ROWS ONLY
";
$stid = oci_parse($conn_oracle, $sql);
bindSharedParams($stid, $invoiceNo, $itemName);
oci_bind_by_name($stid, ':offset', $offset);
oci_bind_by_name($stid, ':limit', $limit);

if (@oci_execute($stid)) {
    $dataStid = $stid;
} else {
    $firstError = oracle_error_message($stid);
    oci_free_statement($stid);

    // Fallback: same filter, no ORDER BY/paging, still bounded by ROWNUM.
    $fallbackSql = "
        SELECT $fullSelectList
        FROM $fromClause
        WHERE $whereClause
          AND ROWNUM <= :limit
    ";
    $stid2 = oci_parse($conn_oracle, $fallbackSql);
bindSharedParams($stid2, $invoiceNo, $itemName);
    oci_bind_by_name($stid2, ':limit', $limit);

    if (@oci_execute($stid2)) {
        $dataStid = $stid2;
        $usedFallback = true;
    } else {
        $message = 'This view could not be queried: ' . $firstError . '.';
        $virtualAll = array_merge($poCols['virtual'], $wfCols['virtual']);
        if (!empty($virtualAll)) {
            $message .= ' Possible computed columns involved: ' . implode(', ', $virtualAll) . '.';
        }
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

$rows = [];
while ($row = oci_fetch_assoc($dataStid)) {
    foreach ($row as $key => $value) {
        if (is_object($value) && method_exists($value, 'load')) {
            $row[$key] = $value->load();
        }
    }
    $rows[] = $row;
}
oci_free_statement($dataStid);
oci_close($conn_oracle);

echo json_encode([
    'success'      => true,
    'columns'      => $allDisplayColumns,
    'poColumns'    => $safeDisplayColumns,
    'wfColumns'    => $safeWfColumns,
    'data'         => $rows,
    'total'        => $total,
    'orderApplied' => !$usedFallback
]);