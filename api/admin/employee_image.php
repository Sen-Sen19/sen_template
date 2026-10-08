<?php

/* ==================== CONFIG ==================== */

// Network share with the photos, named by ID No. (example: 24-11114.png)
const PHOTO_DIR = '\\\\172.25.116.188\\employee_picture\\';

const PHOTO_TYPES = [
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
];

/* ==================== AUTH ==================== */

session_start();

// Signed-in users only. Admins can see any photo; everyone else only their own.
if (empty($_SESSION['logged_in'])) {
    http_response_code(403);
    exit;
}
$isAdmin   = strtolower($_SESSION['role'] ?? '') === 'admin';
$sessionNo = (string)($_SESSION['id_no'] ?? '');

// Release the session lock so several photos can load in parallel
session_write_close();

/* ==================== VALIDATE ==================== */

$idNo = trim($_GET['id_no'] ?? '');

// Letters, numbers, "-" and "_" only — blocks path tricks like ..\ or slashes
if ($idNo === '' || !preg_match('/^[A-Za-z0-9_-]{1,30}$/', $idNo)) {
    http_response_code(400);
    exit;
}

if (!$isAdmin && $idNo !== $sessionNo) {
    http_response_code(403);
    exit;
}

/* ==================== SERVE ==================== */

foreach (PHOTO_TYPES as $ext => $mime) {
    $file = PHOTO_DIR . $idNo . '.' . $ext;

    if (is_file($file)) {
        header('Content-Type: ' . $mime);
        header('Content-Length: ' . filesize($file));
        header('Cache-Control: private, max-age=60');
        readfile($file);
        exit;
    }
}

http_response_code(404);