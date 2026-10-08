<?php
header('Content-Type: application/json');

// Include database connection
require_once __DIR__ . '/connection/template_conn.php';

// Start session
session_start();

// Get POST data
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

$response = ['status' => false, 'message' => ''];

// Validate input
if (empty($username) || empty($password)) {
    $response['message'] = 'Employee number and password are required';
    echo json_encode($response);
    exit;
}

// Prepare and execute query
$sql = "SELECT TOP (1) [id], [id_no], [full_name], [password], [role], [last_active], [ip_address]
        FROM [sen_template_db].[dbo].[accounts]
        WHERE [id_no] = ?";

$params = array($username);
$stmt = sqlsrv_query($conn_sen, $sql, $params);

if ($stmt === false) {
    $response['message'] = 'Database error: ' . print_r(sqlsrv_errors(), true);
    echo json_encode($response);
    exit;
}

// Fetch the user
$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

if ($user) {
    // Verify password (assuming passwords are stored as plain text for now)
    // In a real application, you should use password_verify() with hashed passwords
    if ($password === $user['password']) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['id_no'] = $user['id_no'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // Determine redirect URL based on role
        if (strtolower($user['role']) === 'admin') {
            $redirect_url = '/sen_template/pages/admin/index.php';
        } else {
            $redirect_url = '/sen_template/pages/user/index.php';
        }

        $response = [
            'status' => true,
            'message' => 'Login successful',
            'redirect' => $redirect_url
        ];
    } else {
        $response['message'] = 'Invalid employee number or password';
    }
} else {
    $response['message'] = 'Invalid employee number or password';
}

// Free statement resources
sqlsrv_free_stmt($stmt);

echo json_encode($response);
?>