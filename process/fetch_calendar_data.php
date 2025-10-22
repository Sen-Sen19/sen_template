<?php
include 'conn.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents("php://input"), true);
$username = $input['username'];
$month = $input['month']; // format: YYYY-MM

if (!$username || !$month) {
  echo json_encode(['error' => 'Missing parameters']);
  exit;
}

list($year, $monthNum) = explode('-', $month);

$sql = "
WITH Dates AS (
  SELECT CAST(DATEFROMPARTS($year, $monthNum, 1) AS DATE) AS [date]
  UNION ALL
  SELECT DATEADD(DAY, 1, [date])
  FROM Dates
  WHERE [date] < EOMONTH(DATEFROMPARTS($year, $monthNum, 1))
),
Logs AS (
  SELECT 
      CAST([date] AS DATE) AS [log_date],
      CASE 
          WHEN [status] = 'VL' THEN 'VL'
          WHEN [status] = 'SL' THEN 'SL'
          WHEN [status] = 'LWOP' THEN 'LWOP'
              WHEN [status] = 'RDOT' THEN 'RDOT'
          WHEN [status] = 'NO WORK' THEN 'NO WORK'
          WHEN [status] = 'HOLIDAY' THEN 'HOLIDAY'
          WHEN [status] = 'RD' THEN 'RD'
          WHEN [time_in] IS NOT NULL AND [time_out] IS NOT NULL THEN 'Present'
          WHEN [time_in] IS NOT NULL AND [time_out] IS NULL THEN 'No Time Out'
          ELSE 'Pending'
      END AS [status]
  FROM [sen_template_db].[dbo].[worklog]
  WHERE [name] = ?
    AND MONTH([date]) = $monthNum
    AND YEAR([date]) = $year
)
SELECT 
  CONVERT(varchar(10), D.[date], 23) AS [date],
  CASE 
      WHEN L.[status] IS NOT NULL THEN L.[status]
      WHEN DATENAME(WEEKDAY, D.[date]) = 'Sunday' THEN 'RD'
      ELSE 'Pending'
  END AS [status]
FROM Dates D
LEFT JOIN Logs L ON D.[date] = L.[log_date]
ORDER BY D.[date]
OPTION (MAXRECURSION 0);
";

$stmt = sqlsrv_prepare($conn, $sql, [$username]);
sqlsrv_execute($stmt);

$result = [];
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
  $result[] = $row;
}

echo json_encode($result);
?>
