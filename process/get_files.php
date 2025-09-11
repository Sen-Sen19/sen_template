<?php
include 'conn.php';

$type = isset($_GET['type']) ? $_GET['type'] : '';

$sql = "SELECT FileName, UploadDate FROM [sen_template_db].[dbo].[files]";
$params = [];

if($type && $type !== 'any'){
    $sql .= " WHERE FileName LIKE ?";
    $params[] = '%' . $type;
}

$stmt = sqlsrv_query($conn, $sql, $params);

$files = [];
if($stmt){
    while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)){
        // Convert UploadDate to string if it's a DateTime object
        if($row['UploadDate'] instanceof DateTime){
            $row['UploadDate'] = $row['UploadDate']->format('Y-m-d H:i:s');
        }
        $files[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($files);
?>
