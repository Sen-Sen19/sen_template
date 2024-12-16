<?php
include 'conn.php';

$sql = "SELECT TOP (1000) [EmployeeNo], [Username], [FullName], [Section], [UserType] FROM [my_template_db].[dbo].[employee]";
$stmt = sqlsrv_query($conn, $sql);

if ($stmt === false) {
    die(json_encode(array("error" => "Error fetching data.")));
}

$employees = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $employees[] = $row;
}

echo json_encode($employees);
?>
