<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../connection/template_conn.php';

if (!isset($conn_sen) || !$conn_sen) {
    echo json_encode(['success' => false, 'message' => 'SQL Server connection failed.']);
    exit;
}

// Columns shown on the front end (green / "Journal" group).
$journalDisplayColumns = [
    'voucher_date', 'ledger_journal_name', 'journal_batch_number', 'original_system',
    'voucher_remark', 'prepared_by_name', 'approved_by_name', 'department_name', 'account_name',
    'business_partner_name', 'transaction_currency', 'transaction_amount', 'exchange_rate',
    'accounting_amount', 'reporting_currency', 'reporting_rate', 'reporting_amount',
    'transaction_reference',
    'department', 'line_number', 'debit_credit', 'line_amount_mst'   // new, matching-only
];
/* ---------------------------------------------------------
   INPUT: list of invoice numbers — the front end sends the distinct
   INVOICE_NO values from whatever page of PO+WF rows it just loaded.
   Matching is invoice-only now (department is no longer part of the
   key), so this is a match lookup keyed purely on transaction_reference.
--------------------------------------------------------- */
$body = json_decode(file_get_contents('php://input'), true);
$invoices = is_array($body['invoices'] ?? null) ? $body['invoices'] : [];

// De-dupe + sanitize: only keep non-empty string invoice numbers.
$seen = [];
$cleanInvoices = [];
foreach ($invoices as $inv) {
    $invoice = trim((string)$inv);

    if ($invoice === '' || isset($seen[$invoice])) {
        continue;
    }
    $seen[$invoice] = true;
    $cleanInvoices[] = $invoice;
}

if (empty($cleanInvoices)) {
    echo json_encode(['success' => true, 'matches' => new stdClass()]);
    exit;
}

// Safety cap — meant to be called with one page's worth of rows at a time.
if (count($cleanInvoices) > 500) {
    $cleanInvoices = array_slice($cleanInvoices, 0, 500);
}

$placeholders = implode(', ', array_fill(0, count($cleanInvoices), '?'));
$columnList = implode(', ', array_map(fn($c) => '[' . $c . ']', $journalDisplayColumns));

/* ---------------------------------------------------------
   Invoice-only match: no department filter. A single invoice can
   legitimately have multiple journal rows (different departments,
   accounts, tax lines, split vouchers, etc.) — returning all of them
   lets the front end's amount-based partitioning sort out which PO
   lines belong to which voucher, same as it already does for the
   multi-voucher-per-key case.
--------------------------------------------------------- */
$sql = "
    SELECT $columnList
    FROM dbo.journal_entries
    WHERE transaction_reference IN ($placeholders)
";

$stmt = sqlsrv_query($conn_sen, $sql, $cleanInvoices);
if ($stmt === false) {
    echo json_encode(['success' => false, 'message' => 'Journal query failed: ' . print_r(sqlsrv_errors(), true)]);
    exit;
}

// matches is now keyed by transaction_reference -> ARRAY of journal rows.
$matches = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    foreach ($row as $col => $value) {
        if ($value instanceof DateTime) {
            $row[$col] = $value->format('Y-m-d');
        }
    }
    $key = $row['transaction_reference'];
    if (!isset($matches[$key])) {
        $matches[$key] = [];
    }
    $matches[$key][] = $row;
}
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn_sen);

echo json_encode([
    'success' => true,
    'matches' => $matches
]);