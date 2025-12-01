<?php
include 'conn.php';
header('Content-Type: application/json');

$query = "SELECT [full_name], [username], [password], [role], [department], [employee_id], [img]
          FROM [sen_template_db].[dbo].[account]";

$result = sqlsrv_query($conn, $query);

if ($result === false) {
    echo json_encode(['error' => sqlsrv_errors()]);
    exit;
}

$data = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    if (!empty($row['img'])) {
        // 🧠 Ensure we read binary content safely
        if (is_resource($row['img'])) {
            $imageData = stream_get_contents($row['img']);
        } else {
            $imageData = $row['img'];
        }

        // Detect MIME type (defaults to png)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_buffer($finfo, $imageData);
        finfo_close($finfo);
        if (!$mimeType || strpos($mimeType, 'image/') === false) {
            $mimeType = 'image/png';
        }

        // Base64 encode
        $row['img'] = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
    } else {
        $row['img'] = null;
    }

    $data[] = $row;
}

echo json_encode($data);
?>
