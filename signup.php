<?php

// signup.php (FIXED & CLEAN)

// Always return JSON
header('Content-Type: application/json');

// Enable error display for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'db_connect.php';

$response = [];

// Must be POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

// Read raw JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// Check JSON validity
if (!$data) {
    echo json_encode(["success" => false, "message" => "Invalid JSON."]);
    exit;
}

// Validate required fields
if (
    empty($data['username']) ||
    empty($data['email']) ||
    empty($data['password']) ||
    empty($data['location'])
) {
    echo json_encode(["success" => false, "message" => "Please fill in all fields."]);
    exit;
}

// Extract fields
$username = trim($data['username']);
$email = trim($data['email']);
$password = $data['password'];
$location = trim($data['location']);

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email format."]);
    exit;
}

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Check if user/email already exists
$sql = "SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("ss", $email, $username);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode(["success" => false, "message" => "Username or email already taken."]);
    $stmt->close();
    exit;
}
$stmt->close();

// Insert user
$sql = "INSERT INTO users (username, email, password_hash, location) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(["success" => false, "message" => "Database error: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssss", $username, $email, $password_hash, $location);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Sign up successful!"]);
} else {
    echo json_encode(["success" => false, "message" => "Insert error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
