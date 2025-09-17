<?php
include 'conn.php';
session_start();

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$username  = $input['username'] ?? null;
$date_time = $input['date_time'] ?? null;
$ip        = $_SERVER['REMOTE_ADDR'];

if ($username && $date_time) {
    // delete if same ip already exists
    $delete = "DELETE FROM [sen_template_db].[dbo].[active_tb] WHERE ip_address = ?";
    $stmt = sqlsrv_prepare($conn, $delete, [$ip]);
    sqlsrv_execute($stmt);

    // insert new record
    $sql = "INSERT INTO [sen_template_db].[dbo].[active_tb] (ip_address, username, date_time) VALUES (?, ?, ?)";
    $params = [$ip, $username, $date_time];
    $stmt = sqlsrv_prepare($conn, $sql, $params);

    if ($stmt && sqlsrv_execute($stmt)) {
        echo json_encode(["status" => "success", "username" => $username, "ip" => $ip, "date_time" => $date_time]);
    } else {
        echo json_encode(["status" => "error", "message" => "Insert failed"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
}
