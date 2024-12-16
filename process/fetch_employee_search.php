<?php
include 'conn.php';

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query for fetching data
$query = "
    SELECT [EmployeeNo], [Username], [FullName], [Section], [UserType] 
    FROM [my_template_db].[dbo].[employee] 
    WHERE 1 = 1";

$params = [];


if (!empty($search)) {
    $query .= " AND ([EmployeeNo] LIKE ? OR [Username] LIKE ? OR [FullName] LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}


$countQuery = "
    SELECT COUNT(*) AS total
    FROM [my_template_db].[dbo].[employee] 
    WHERE 1 = 1";

if (!empty($search)) {
    $countQuery .= " AND ([EmployeeNo] LIKE ? OR [Username] LIKE ? OR [FullName] LIKE ?)";
}

$countStmt = sqlsrv_query($conn, $countQuery, $params);
if ($countStmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$countRow = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC);
$totalCount = $countRow['total'];

sqlsrv_free_stmt($countStmt);


$query .= " ORDER BY [EmployeeNo] 
    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";
$params[] = $offset;
$params[] = $limit;

$stmt = sqlsrv_query($conn, $query, $params);
if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$data = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);


echo json_encode(['data' => $data, 'totalCount' => $totalCount]);
?>
