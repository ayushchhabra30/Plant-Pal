<?php
// db_connect.php (FIXED)

// Show all errors during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ---- Database Configuration ----
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'ayush9122'); 
define('DB_NAME', 'plant_pal_db'); // <-- Confirm this EXACT name in phpMyAdmin

// ---- Create Connection ----
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// ---- Check Connection ----
if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

$conn->set_charset("utf8mb4");
