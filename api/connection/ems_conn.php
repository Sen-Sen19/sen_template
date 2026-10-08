<?php
$serverName = "172.25.116.188";

$connectionOptions = [
    "Database" => "emp_mgt_db",
    "Uid" => "sa",
    "PWD" => "SystemGroup@2022"
];

$conn_emp = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn_emp) {
    die(print_r(sqlsrv_errors(), true));
}
?>