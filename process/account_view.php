<?php
include 'conn.php';
header('Content-Type: application/json');

$query = "SELECT [full_name], [username], [password], [role],[department],[employee_id]
          FROM [sen_template_db].[dbo].[account]";

$result = sqlsrv_query($conn, $query);

if ($result === false) {
    echo json_encode(['error' => sqlsrv_errors()]);
    exit;
}

$data = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $data[] = $row;
}

echo json_encode($data);
?>
