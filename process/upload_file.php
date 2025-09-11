<?php
include 'conn.php';

if(isset($_FILES['file'])){
    $fileName = $_FILES['file']['name'];
    $fileType = $_FILES['file']['type'];
    $fileContent = file_get_contents($_FILES['file']['tmp_name']);

    $uploadDate = date('Y-m-d H:i:s'); // string is simpler than DateTime object

    $sql = "INSERT INTO [sen_template_db].[dbo].[files] (FileName, FileType, FileContent, UploadDate) VALUES (?, ?, ?, ?)";

    $params = array(
        array($fileName, SQLSRV_PARAM_IN),
        array($fileType, SQLSRV_PARAM_IN),
        array($fileContent, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY)),
        array($uploadDate, SQLSRV_PARAM_IN)
    );

    $stmt = sqlsrv_query($conn, $sql, $params);

    if($stmt){
        echo "File uploaded successfully!";
    } else {
        echo "Error uploading file: " . print_r(sqlsrv_errors(), true);
    }
} else {
    echo "No file selected.";
}
?>
