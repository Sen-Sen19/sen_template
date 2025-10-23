<?php
include 'conn.php';
header('Content-Type: application/json');

date_default_timezone_set('Asia/Manila'); // Always use PH time to avoid confusion

try {
    $sql = "SELECT ip_address, username, date_time 
            FROM [sen_template_db].[dbo].[active_tb]";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception(print_r(sqlsrv_errors(), true));
    }

    $rows = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        // Normalize datetime format (SQLSRV may return DateTime or string)
        if ($row['date_time'] instanceof DateTime) {
            $row['date_time'] = $row['date_time']->format("Y-m-d H:i:s");
        } else {
            // Just in case SQL returns a string with milliseconds
            $row['date_time'] = date("Y-m-d H:i:s", strtotime($row['date_time']));
        }

        // Optional: add a computed "is_active" flag (for easier front-end logic)
        $lastActivity = strtotime($row['date_time']);
        $now = time();
        $diff = $now - $lastActivity;
        $row['is_active'] = ($diff <= 300); // Active if within 5 minutes

        $rows[] = $row;
    }

    echo json_encode($rows);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
