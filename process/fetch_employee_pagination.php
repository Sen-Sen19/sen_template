<?php
include 'conn.php';

// Get the current page and the number of records per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;  // Default to page 1 if no page is specified
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;  // Default to 10 records per page

// Calculate the starting record for the query
$start = ($page - 1) * $limit;

// Fetch the employee data with pagination
$sql = "SELECT [EmployeeNo], [Username], [FullName], [Section], [UserType] 
        FROM [my_template_db].[dbo].[employee] 
        ORDER BY [EmployeeNo] 
        OFFSET $start ROWS 
        FETCH NEXT $limit ROWS ONLY";
        
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(json_encode(array("error" => "Error fetching data.")));
}

$employees = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employees[] = $row;
}

// Get total record count for pagination
$countSql = "SELECT COUNT(*) AS total FROM [my_template_db].[dbo].[employee]";
$countStmt = sqlsrv_query($conn, $countSql);
$countRow = sqlsrv_fetch_array($countStmt, SQLSRV_FETCH_ASSOC);
$totalRecords = $countRow['total'];

// Return data along with total count for pagination
echo json_encode([
    'employees' => $employees,
    'totalRecords' => $totalRecords
]);
?>
