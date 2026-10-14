<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

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

require_once __DIR__ . '/../connection/template_conn.php';

/* ==================== QUERY ==================== */

$q        = trim($_GET['q'] ?? '');
$isExport = !empty($_GET['export']);
$offset   = max(0, (int)($_GET['offset'] ?? 0));
$limit    = min(200, max(1, (int)($_GET['limit'] ?? 30)));

$table  = '[factory_supplies_db].[dbo].[item_masterlist]';
$where  = '';
$params = [];

if ($q !== '') {
    $where  = 'WHERE [product_code] LIKE ? OR [product_name] LIKE ?';
    $like   = '%' . $q . '%';
    $params = [$like, $like];
}

// Total
$stmt = sqlsrv_query($conn_sen, "SELECT COUNT(*) AS total FROM $table $where", $params);
if (!$stmt) {
    respond(false, ['message' => 'Could not load items.']);
}
$total = (int)(sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)['total'] ?? 0);
sqlsrv_free_stmt($stmt);

// Rows
$sql = "SELECT [product_code], [product_name], [added_by], [datetime_added], [unit_cost_jpy], [unit_cost_php]
        FROM $table $where
        ORDER BY [product_code]";

if (!$isExport) {
    $sql     .= ' OFFSET ? ROWS FETCH NEXT ? ROWS ONLY';
    $params[] = $offset;
    $params[] = $limit;
}

$stmt = sqlsrv_query($conn_sen, $sql, $params);
if (!$stmt) {
    respond(false, ['message' => 'Could not load items.']);
}

$data = [];
while ($r = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = [
        'product_code'   => trim((string)$r['product_code']),
        'product_name'   => $r['product_name'],
        'added_by'       => $r['added_by'],
        'datetime_added' => $r['datetime_added'] instanceof DateTimeInterface
            ? $r['datetime_added']->format('Y-m-d H:i:s')
            : $r['datetime_added'],
        'unit_cost_jpy'  => $r['unit_cost_jpy'] === null ? null : (float)$r['unit_cost_jpy'],
        'unit_cost_php'  => $r['unit_cost_php'] === null ? null : (float)$r['unit_cost_php'],
    ];
}
sqlsrv_free_stmt($stmt);

respond(true, ['total' => $total, 'data' => $data]);