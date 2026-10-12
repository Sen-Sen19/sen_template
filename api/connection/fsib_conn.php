<?php

$username = "FSIB"; 
$password = "FSIB"; 
$database = "172.25.119.1:1521/FSIB"; 

try {
    $conn_oracle = oci_connect($username, $password, $database);
    if (!$conn_oracle) {
        $m = oci_error();
        throw new Exception($m['message']);
    }
} catch (Exception $e) {
    echo "Oracle Connection Failed: " . $e->getMessage();
}
