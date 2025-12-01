<?php
include 'conn.php';
header('Content-Type: application/json');

if (!isset($_GET['username']) || empty($_GET['username'])) {
    echo json_encode(['success' => false, 'message' => 'No username provided']);
    exit;
}

$username = $_GET['username'];

$query = "SELECT img FROM [sen_template_db].[dbo].[account] WHERE username = ?";
$params = [$username];
$result = sqlsrv_query($conn, $query, $params);

if ($result === false) {
    echo json_encode(['success' => false, 'message' => 'Query failed', 'error' => sqlsrv_errors()]);
    exit;
}

$row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC);
if (!$row || empty($row['img'])) {
    echo json_encode(['success' => true, 'img' => null]);
    exit;
}

$imageData = is_resource($row['img']) ? stream_get_contents($row['img']) : $row['img'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_buffer($finfo, $imageData);
finfo_close($finfo);
if (!$mimeType || strpos($mimeType, 'image/') === false) {
    $mimeType = 'image/png';
}

$base64Image = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);

echo json_encode(['success' => true, 'img' => $base64Image]);
?>
