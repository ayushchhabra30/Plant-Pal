<?php
/*
 * db_connect.php
 * UPDATED:
 * - Removed the closing ?> tag to prevent "headers already sent" errors.
 * - Added error reporting to hide minor notices.
 */

// Hide all errors except for fatal ones (this helps prevent breaking JSON)
error_reporting(E_ERROR | E_PARSE);

// --- Database Configuration ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'ayush9122'); // <-- Make sure this is your correct password
define('DB_NAME', 'plant_pal_db');

// --- Attempt to Connect ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($conn->connect_error) {
    // If connection fails, stop and send a JSON error.
    // This is safer than just die().
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit();
}

// Set the character set
$conn->set_charset("utf8mb4");

// We no longer need the closing ?> tag. This is intentional.