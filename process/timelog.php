<?php
ob_start();
include 'conn.php';
header('Content-Type: application/json');

$response = ['status' => 'error','msg'=>'Unknown error'];

try {
    if(isset($_POST['action'])){
        $action = $_POST['action'];
        $username = $_POST['username'] ?? 'Unknown';
        $currentDate = $_POST['date'] ?? date('Y-m-d'); // <-- use selected date
        $time = $_POST['time'] ?? null;

        if($action === 'get_log'){
            // Fetch existing log for this date
            $check = sqlsrv_query($conn, "SELECT time_in, time_out FROM [dbo].[worklog] WHERE [date] = ? AND [name] = ?", array($currentDate, $username));
            if(sqlsrv_has_rows($check)){
                $row = sqlsrv_fetch_array($check, SQLSRV_FETCH_ASSOC);
                $response = [
                    'status'=>'success',
                    'time_in'=>$row['time_in'] ? (is_object($row['time_in']) ? $row['time_in']->format('g:i A') : date('g:i A', strtotime($row['time_in']))) : null,
                    'time_out'=>$row['time_out'] ? (is_object($row['time_out']) ? $row['time_out']->format('g:i A') : date('g:i A', strtotime($row['time_out']))) : null
                ];
            } else {
                $response = ['status'=>'empty'];
            }
        }

        if(!$time){
            $time = $action === 'time_in' ? "07:30" : "18:00";
        }

if($action === 'time_in'){
    // Get current server time in PH timezone
    $dateTime = new DateTime("now", new DateTimeZone("Asia/Manila"));
    $timeString = $dateTime->format("H:i:s"); 

    // Check if row exists
    $check = sqlsrv_query($conn, 
        "SELECT * FROM [dbo].[worklog] WHERE [date] = ? AND [name] = ?", 
        [$currentDate, $username]
    );

    if(sqlsrv_has_rows($check)){
        $sql = "UPDATE [dbo].[worklog] SET [time_in] = ? WHERE [date] = ? AND [name] = ?";
        $params = [$timeString, $currentDate, $username];
    } else {
        $sql = "INSERT INTO [dbo].[worklog] ([time_in], [date], [name]) VALUES (?, ?, ?)";
        $params = [$timeString, $currentDate, $username];
    }

    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt){
        $response = ['status'=>'success','time'=>$dateTime->format("g:i A")];
    } else {
        $response = ['status'=>'error','msg'=>json_encode(sqlsrv_errors())];
    }
}



        if($action === 'time_out'){
            $sql = "UPDATE [dbo].[worklog] SET [time_out] = ? WHERE [date] = ? AND [name] = ?";
            $params = array($time, $currentDate, $username);
            $stmt = sqlsrv_query($conn, $sql, $params);

            if($stmt){
                $response = [
                    'status'=>'success',
                    'time'=> date('g:i A', strtotime($time))
                ];
            } else {
                $response = ['status'=>'error','msg'=>json_encode(sqlsrv_errors())];
            }
        }

    } else {
        $response = ['status'=>'error','msg'=>'No action specified'];
    }
} catch(Exception $e){
    $response = ['status'=>'error','msg'=>$e->getMessage()];
}

echo json_encode($response);
ob_end_flush();
