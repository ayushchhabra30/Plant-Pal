<?php
/*
 * signup.php
 * UPDATED:
 * - Removed closing ?> tag.
 * - Added session_start()
 */

// Start a session
session_start();

// Set the content type to return JSON *first*
header('Content-Type: application/json');

// Include the database connection file
require_once 'db_connect.php';

// Create an empty array to store the response
$response = [];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get the incoming JSON data and decode it
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if all required fields are present
    if (!empty($data['username']) && !empty($data['email']) && !empty($data['password']) && !empty($data['location'])) {
        
        $username = $conn->real_escape_string($data['username']);
        $email = $conn->real_escape_string($data['email']);
        $location = $conn->real_escape_string($data['location']);
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // Check if email or username already exists
        $sql_check = "SELECT id FROM users WHERE email = ? OR username = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("ss", $email, $username);
        $stmt_check->execute();
        $stmt_check->store_result();
        
        if ($stmt_check->num_rows > 0) {
            $response['success'] = false;
            $response['message'] = 'Username or email already taken.';
        } else {
            // User does not exist, proceed with insertion
            $sql_insert = "INSERT INTO users (username, email, password_hash, location) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $username, $email, $password_hash, $location);
            
            if ($stmt_insert->execute()) {
                $response['success'] = true;
                $response['message'] = 'Sign up successful! You can now sign in.';
            } else {
                $response['success'] = false;
                $response['message'] = 'Error: ' . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();

    } else {
        $response['success'] = false;
        $response['message'] = 'Please fill in all fields.';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$conn->close();

echo json_encode($response);

// No closing ?> tag