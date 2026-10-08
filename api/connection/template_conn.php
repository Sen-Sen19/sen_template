<?php
$serverName = "172.25.116.188";

$connectionOptions = [
    "Database" => "sen_template_db",
    "Uid" => "sa",
    "PWD" => "SystemGroup@2022"
];

$conn_sen = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn_sen) {
    die(print_r(sqlsrv_errors(), true));
}
?>