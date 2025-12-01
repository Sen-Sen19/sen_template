<?php
include 'conn.php';

header('Content-Type: application/json'); // Always return JSON

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = trim($_POST['employeeId'] ?? '');
    $fullName   = trim($_POST['fullName'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $type       = trim($_POST['type'] ?? '');

    // 🧠 Basic validation
    if (empty($employeeId) || empty($fullName) || empty($username) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please fill in all required fields.']);
        exit;
    }

    // 🖼 Handle optional image upload (or fallback to default)
    $imgData = null;
    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES['profileImage']['tmp_name']);
    } else {
        // Use default image if no upload provided
        $defaultPath = '../../dist/img/office-man.png';
        if (file_exists($defaultPath)) {
            $imgData = file_get_contents($defaultPath);
        }
    }

    // ✅ Check for duplicate Employee ID
    $checkQuery = "SELECT COUNT(*) AS count FROM account WHERE employee_id = ?";
    $checkStmt  = sqlsrv_query($conn, $checkQuery, [$employeeId]);

    if (!$checkStmt) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking for duplicates.',
            'details' => sqlsrv_errors()
        ]);
        exit;
    }

    $row = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);
    if ($row && $row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Employee ID already exists.']);
        exit;
    }

    // ✅ Insert query
    $sql = "INSERT INTO account (employee_id, full_name, username, department, password, role, img)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    // ✅ Parameter binding — varbinary-safe
    $params = [
        [$employeeId, SQLSRV_PARAM_IN],
        [$fullName, SQLSRV_PARAM_IN],
        [$username, SQLSRV_PARAM_IN],
        [$department, SQLSRV_PARAM_IN],
        [$password, SQLSRV_PARAM_IN],
        [$type, SQLSRV_PARAM_IN],
        [$imgData, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY)]
    ];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt) {
        echo json_encode(['success' => true, 'message' => 'Account added successfully.']);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'SQL Error',
            'details' => sqlsrv_errors()
        ]);
    }
}
?>
