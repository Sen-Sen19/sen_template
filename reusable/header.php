<?php
// Project root URL, e.g. "/sen_template". Auto-detected from the current script.
$system = $system ?? '/' . explode('/', trim($_SERVER['SCRIPT_NAME'], '/'))[0];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="Sub PC Checksheet Record" />
    <meta name="keywords" content="Sub PC Checksheet Record" />

    <title>FALP NEXUS</title>


    <title><?php echo htmlspecialchars($page_title ?? 'Template'); ?></title>
    <link rel="icon" type="image/png" href="../../dist/img/logo.png">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($page_css ?? ''); ?>">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>