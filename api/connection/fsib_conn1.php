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

    // Views/queries across this schema use bare TO_DATE('YYYY/MM/DD') literals
    // (no explicit format mask), which fail with ORA-01861 if the session's
    // default NLS_DATE_FORMAT doesn't match. Pin it here, once, for every
    // connection made through this file.
    $sessionStid = oci_parse($conn_oracle, "ALTER SESSION SET NLS_DATE_FORMAT = 'YYYY/MM/DD'");
    oci_execute($sessionStid);
    oci_free_statement($sessionStid);

} catch (Exception $e) {
    echo "Oracle Connection Failed: " . $e->getMessage();
}   