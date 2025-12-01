<?php
header("Content-Type: application/json");
session_start();

if(isset($_SESSION['chat_user'])){
    echo json_encode([
        "status" => "active",
        "user"   => $_SESSION['chat_user']
    ]);
} else {
    echo json_encode(["status" => "inactive"]);
}
?>
