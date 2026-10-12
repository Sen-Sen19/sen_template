<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../connection/template_conn.php';

if (!$conn_sen) {
    echo json_encode(['success' => false, 'message' => 'Database connection unavailable.']);
    exit;
}

$dateFrom = $_GET['date_from'] ?? '';
$dateTo   = $_GET['date_to'] ?? '';
$transactionRef = isset($_GET['transaction_reference']) ? trim($_GET['transaction_reference']) : '';
$offset   = isset($_GET['offset']) ? max(0, (int) $_GET['offset']) : 0;
$limit    = isset($_GET['limit']) ? max(1, (int) $_GET['limit']) : 100;

$whereClauses = [];
$params = [];

if ($dateFrom !== '' && $dateTo !== '') {
    $whereClauses[] = 'voucher_date BETWEEN ? AND ?';
    $params[] = $dateFrom;
    $params[] = $dateTo;
}

if ($transactionRef !== '') {
    $whereClauses[] = 'transaction_reference LIKE ?';
    $params[] = '%' . $transactionRef . '%';
}

$where = $whereClauses ? ('WHERE ' . implode(' AND ', $whereClauses)) : '';

$countSql = "SELECT COUNT(*) AS TOTAL FROM dbo.journal_entries $where";
$countStmt = sqlsrv_query($conn_sen, $countSql, $params);
if ($countStmt === false) {
    $e = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . ($e[0]['message'] ?? 'unknown error')]);
    exit;
}
$countRow = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC);
$total = (int) ($countRow['TOTAL'] ?? 0);
sqlsrv_free_stmt($countStmt);

$dataSql = "
    SELECT
        voucher_date, ledger_journal_name, journal_batch_number, line_number, original_system,
        voucher_remark, debit_credit, prepared_by, prepared_by_name, approved_by, approved_by_name,
        approved_date, department, department_name, ledger_accounts, account_name, ledger_account,
        detail, detail_name, business_partner, business_partner_name, aging_code, detail_remarks,
        transaction_currency, transaction_amount, exchange_rate, accounting_amount, reporting_currency,
        reporting_rate, reporting_amount, tax_code, tax_name, tax_percentage, transaction_reference,
        invoice, due_date, payment_method, allocation_reference, transaction_reference_no2
    FROM dbo.journal_entries
    $where
    ORDER BY voucher_date DESC, id DESC
    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY
";
$dataParams = array_merge($params, [$offset, $limit]);

$dataStmt = sqlsrv_query($conn_sen, $dataSql, $dataParams);
if ($dataStmt === false) {
    $e = sqlsrv_errors();
    echo json_encode(['success' => false, 'message' => 'Query failed: ' . ($e[0]['message'] ?? 'unknown error')]);
    exit;
}

$rows = [];
while ($row = sqlsrv_fetch_array($dataStmt, SQLSRV_FETCH_ASSOC)) {
    foreach ($row as $key => $value) {
        if ($value instanceof DateTime) {
            $row[$key] = $value->format('Y-m-d');
        }
    }
    $rows[] = $row;
}
sqlsrv_free_stmt($dataStmt);
sqlsrv_close($conn_sen);

echo json_encode(['success' => true, 'data' => $rows, 'total' => $total]);