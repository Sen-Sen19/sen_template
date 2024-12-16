<?php
include 'conn.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeId = $_POST['employeeId'];
    $fullName = $_POST['fullName'];
    $username = $_POST['username'];
    $department = $_POST['department'];
    $password = $_POST['password'];
    $type = $_POST['type'];


    $checkQuery = "SELECT employee_id FROM account WHERE employee_id = ?";
    $stmt = sqlsrv_query($conn, $checkQuery, array($employeeId));
    if (sqlsrv_has_rows($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Employee ID already exists.']);
        exit;
    }


    $insertQuery = "INSERT INTO account (employee_id, full_name, username, department, password, role) 
                    VALUES (?, ?, ?, ?, ?, ?)";
    $params = array($employeeId, $fullName, $username, $department, $password, $type);
    $stmt = sqlsrv_query($conn, $insertQuery, $params);

    if ($stmt) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error adding record.']);
    }
}
?>
