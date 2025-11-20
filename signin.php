<?php
// signin.php (FINAL WORKING VERSION)

// Always return JSON
header('Content-Type: application/json');

// Enable debugging during development
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include DB connection
require_once 'db_connect.php';

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit();
}

// Read JSON body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Validate JSON body
if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON."]);
    exit();
}

// Check required fields
if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["success" => false, "message" => "Please provide email and password."]);
    exit();
}

// Extract
$email = trim($data['email']);
$password = $data['password'];

// Prepare query
$sql = "SELECT id, username, password_hash FROM users WHERE email = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit();
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Verify password
    if (password_verify($password, $user['password_hash'])) {

        // Set session data
        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode([
            "success" => true,
            "message" => "Login successful!",
            "username" => $user['username']
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Invalid email or password."
        ]);
    }

} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email or password."
    ]);
}

$stmt->close();
$conn->close();
