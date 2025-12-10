<?php
include '../conn.php';

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['datetime'])) {
    echo json_encode(["status" => "error", "message" => "Missing datetime"]);
    exit;
}

$datetime = $data['datetime'];
$datetimeTrimmed = substr($datetime, 0, 19); // trim milliseconds

$sql = "DELETE FROM [sen_template_db].[dbo].[messages] 
        WHERE CONVERT(VARCHAR(19), datetime, 120) = ?";
$params = [$datetimeTrimmed];
$result = sqlsrv_query($conn, $sql, $params);

if ($result === false) {
    die(print_r(sqlsrv_errors(), true));
}

echo json_encode(["status" => "success"]);
?>
