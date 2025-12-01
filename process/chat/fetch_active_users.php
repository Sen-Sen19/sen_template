<?php
include "../conn.php";

// Get current datetime in Asia/Manila timezone
date_default_timezone_set("Asia/Manila");
$now = new DateTime();

$sql = "SELECT full_name, last_activity FROM account";
$stmt = sqlsrv_query($conn, $sql);

$users = [];

if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $lastActivity = $row['last_activity'];
        if ($lastActivity) {
            $lastActTime = $lastActivity->format('Y-m-d H:i:s');
            $lastDateTime = new DateTime($lastActTime);

            $diff = $now->getTimestamp() - $lastDateTime->getTimestamp();
            $minutes = round($diff / 60);

            if ($minutes <= 2) {
                $status = "Active";
                $inactive = 0;
            } else {
                $status = "Inactive";
                if ($minutes < 60) {
                    $inactive = $minutes . " mins ago";
                } elseif ($minutes < 1440) {
                    $inactive = round($minutes/60) . " hours ago";
                } else {
                    $inactive = round($minutes/1440) . " days ago";
                }
            }
        } else {
            $status = "Inactive";
            $inactive = "Never";
        }

        $users[] = [
            "full_name" => $row['full_name'],
            "status" => $status,
            "inactive" => $inactive
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($users);
