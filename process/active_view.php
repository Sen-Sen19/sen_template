<?php
include 'conn.php';
header('Content-Type: application/json');

try {
    $sql = "SELECT ip_address, username, date_time 
            FROM [sen_template_db].[dbo].[active_tb]";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Format datetime if it's a DateTime object
        if ($row['date_time'] instanceof DateTime) {
            $row['date_time'] = $row['date_time']->format("Y-m-d H:i:s");
        }
        $rows[] = $row;
    }

    echo json_encode($rows);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
