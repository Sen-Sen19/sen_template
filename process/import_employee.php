<?php
include 'conn.php';

$response = array(); // Initialize response array

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['csvFile'])) {
    $fileName = $_FILES['csvFile']['name'];
    $fileTmpName = $_FILES['csvFile']['tmp_name'];
    $fileError = $_FILES['csvFile']['error'];

    // Check for upload errors
    if ($fileError === 0) {
        $fileExt = pathinfo($fileName, PATHINFO_EXTENSION);
        
        // Only process CSV files
        if ($fileExt === 'csv') {
            // Open the file for reading
            if (($handle = fopen($fileTmpName, "r")) !== FALSE) {
                // Skip the header row
                fgetcsv($handle);

                // Prepare the SQL query
                $sql = "INSERT INTO employee (EmployeeNo, Username, FullName, Section, UserType) VALUES (?, ?, ?, ?, ?)";

                // Loop through the CSV file and insert each row
                $insertedCount = 0;
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $EmployeeNo = $data[0];
                    $Username = $data[1];
                    $FullName = $data[2];
                    $Section = $data[3];
                    $UserType = $data[4];

                    // Prepare and execute the SQL query
                    $params = array($EmployeeNo, $Username, $FullName, $Section, $UserType);
                    $stmt = sqlsrv_query($conn, $sql, $params);

                    if (!$stmt) {
                        // Check for duplicate primary key violation (error code 2627)
                        $error = sqlsrv_errors();
                        if ($error[0]['code'] == 2627) {
                            $response['success'] = false;
                            $response['message'] = "Cannot import data because the data already exists (duplicate key).";
                        } else {
                            $response['success'] = false;
                            $response['message'] = "Error inserting data: " . print_r($error, true);
                        }
                        echo json_encode($response); // Return error message and stop execution
                        exit;
                    }
                    $insertedCount++;
                }

                fclose($handle);

                // Return success response
                $response['success'] = true;
                $response['message'] = "$insertedCount records were successfully imported.";
                echo json_encode($response);
            } else {
                $response['success'] = false;
                $response['message'] = "Error opening the file.";
                echo json_encode($response);
            }
        } else {
            $response['success'] = false;
            $response['message'] = "Invalid file type. Please upload a CSV file.";
            echo json_encode($response);
        }
    } else {
        $response['success'] = false;
        $response['message'] = "Error uploading file. Please try again.";
        echo json_encode($response);
    }
} else {
    $response['success'] = false;
    $response['message'] = "No file uploaded.";
    echo json_encode($response);
}
?>
