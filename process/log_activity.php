<?php
include 'conn.php';
session_start();

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);
$username  = $input['username'] ?? null;
$date_time = $input['date_time'] ?? null;
$ip        = $_SERVER['REMOTE_ADDR'];

if ($username && $date_time) {
    // Check if username already exists
    $check = "SELECT 1 FROM [sen_template_db].[dbo].[active_tb] WHERE username = ?";
    $stmt  = sqlsrv_query($conn, $check, [$username]);

    if ($stmt && sqlsrv_fetch($stmt)) {
        // Update existing record
        $update = "UPDATE [sen_template_db].[dbo].[active_tb] 
                   SET ip_address = ?, date_time = ? 
                   WHERE username = ?";
        $params = [$ip, $date_time, $username];
        $stmt   = sqlsrv_prepare($conn, $update, $params);

        if ($stmt && sqlsrv_execute($stmt)) {
            echo json_encode([
                "status" => "updated",
                "username" => $username,
                "ip" => $ip,
                "date_time" => $date_time
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed"]);
        }
    } else {
        // Insert new record
        $insert = "INSERT INTO [sen_template_db].[dbo].[active_tb] (ip_address, username, date_time) 
                   VALUES (?, ?, ?)";
        $params = [$ip, $username, $date_time];
        $stmt   = sqlsrv_prepare($conn, $insert, $params);

        if ($stmt && sqlsrv_execute($stmt)) {
            echo json_encode([
                "status" => "inserted",
                "username" => $username,
                "ip" => $ip,
                "date_time" => $date_time
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Insert failed"]);
        }
    }
} else {
    echo json_encode(["status" => "error", "message" => "Missing data"]);
}
