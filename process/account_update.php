<?php
include 'conn.php';
header('Content-Type: application/json');


if (
    isset($_POST['employeeId'], $_POST['fullName'], $_POST['username'], 
          $_POST['department'], $_POST['password'], $_POST['type'])
) {
    $employeeId = $_POST['employeeId'];
    $fullName   = $_POST['fullName'];
    $username   = $_POST['username'];
    $department = $_POST['department'];
    $password   = $_POST['password'];
    $type       = $_POST['type'];
    $imgData = null;


    if (isset($_FILES['profileImage']) && $_FILES['profileImage']['error'] === UPLOAD_ERR_OK) {
        $imgData = file_get_contents($_FILES['profileImage']['tmp_name']);
    }

    if ($imgData !== null) {

        $query = "UPDATE account 
                  SET full_name = ?, username = ?, department = ?, password = ?, role = ?, img = ? 
                  WHERE employee_id = ?";
        $params = [
            $fullName, 
            $username, 
            $department, 
            $password, 
            $type,            [$imgData, SQLSRV_PARAM_IN, SQLSRV_PHPTYPE_STREAM(SQLSRV_ENC_BINARY)],

            $employeeId
        ];
    } else {
         $query = "UPDATE account 
                  SET full_name = ?, username = ?, department = ?, password = ?, role = ? 
                  WHERE employee_id = ?";
        $params = [$fullName, $username, $department, $password, $type, $employeeId];
    }

    $stmt = sqlsrv_query($conn, $query, $params);

    if ($stmt) {
        echo json_encode(['success' => true, 'message' => 'Account updated successfully.']);
    } else {
        $errors = sqlsrv_errors();
        echo json_encode(['success' => false, 'message' => 'Error updating account.', 'details' => $errors]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
}
?>
