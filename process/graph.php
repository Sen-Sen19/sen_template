<?php

include 'conn.php';

$year = isset($_GET['year']) ? $_GET['year'] : '2021';

$query = "
    SELECT [month], [rate]
    FROM [my_template_db].[dbo].[monthly_performance]
    WHERE [year] = ?
";

$params = array($year);
$stmt = sqlsrv_query($conn, $query, $params);


if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}


$months = [];
$rates = [];


while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    $months[] = $row['month'];  
    $rates[] = $row['rate'];    
}


$monthOrder = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
];


$sortedMonths = [];
$sortedRates = [];
foreach ($monthOrder as $month) {
    $key = array_search($month, $months);
    if ($key !== false) {
        $sortedMonths[] = $months[$key];
        $sortedRates[] = $rates[$key];
    } else {

        $sortedMonths[] = $month;
        $sortedRates[] = 0; 
    }
}


sqlsrv_close($conn);


echo json_encode([
    'months' => $sortedMonths,
    'rates'  => $sortedRates
]);
?>
