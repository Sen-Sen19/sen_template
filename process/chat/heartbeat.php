<?php
require '../conn.php';

$data = json_decode(file_get_contents("php://input"), true);

if(isset($data['employee_id']) && !empty($data['date_today']) && !empty($data['time_now'])){

    $employee_id = $data['employee_id'];
    $date       = $data['date_today'];   // Example: January 22, 2025
    $time       = $data['time_now'];     // Example: 10:22:33 PM

    // Convert into a valid DATETIME format for SQL Server
    $datetime = date('Y-m-d H:i:s', strtotime("$date $time"));

    $tsql = "UPDATE [sen_template_db].[dbo].[account] 
             SET last_activity = ? 
             WHERE employee_id = ?";
    $params = array($datetime, $employee_id);

    $stmt = sqlsrv_query($conn, $tsql, $params);

    echo json_encode($stmt ? ['status'=>'ok'] : ['status'=>'error','msg'=>sqlsrv_errors()]);
} else {
    echo json_encode(['status'=>'error','msg'=>'Missing required fields']);
}
?>
