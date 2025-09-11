<?php
include 'conn.php';


if (isset($_POST['employeeId']) && isset($_POST['fullName']) && isset($_POST['username']) && isset($_POST['department']) && isset($_POST['password']) && isset($_POST['type'])) {
    $employeeId = $_POST['employeeId'];
    $fullName = $_POST['fullName'];
    $username = $_POST['username'];
    $department = $_POST['department'];
    $password = $_POST['password'];
    $type = $_POST['type'];

    $query = "UPDATE account SET full_name = ?, username = ?, department = ?, password = ?, role = ? WHERE employee_id = ?";
    $params = array($fullName, $username, $department, $password, $type, $employeeId);
    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt) {
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating account: ' . sqlsrv_errors()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
}
?>
