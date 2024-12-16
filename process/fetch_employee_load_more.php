<?php
include 'conn.php';

$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;

$totalCountQuery = "
    SELECT COUNT(*) AS total FROM [my_template_db].[dbo].[employee]";
$totalCountResult = sqlsrv_query($conn, $totalCountQuery);

if ($totalCountResult === false) {
    echo json_encode(["status" => "error", "message" => sqlsrv_errors()]);
    exit;
}

$totalCountRow = sqlsrv_fetch_array($totalCountResult, SQLSRV_FETCH_ASSOC);
$totalCount = $totalCountRow['total'];

$query = "
    SELECT [EmployeeNo], [Username], [FullName], [Section], [UserType] 
    FROM [my_template_db].[dbo].[employee] 
    ORDER BY EmployeeNo 
    OFFSET ? ROWS FETCH NEXT ? ROWS ONLY";

$params = [$offset, $limit];
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
