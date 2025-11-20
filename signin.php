<?php
/*
 * signin.php
 * Handles user login.
 * Receives POST data (email, password) from the sign-in form.
 * Finds the user and verifies the hashed password.
 */

// Start a session to store login state
session_start();

// Set the content type to return JSON
header('Content-Type: application/json');

// Include the database connection file
require_once 'db_connect.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $data = json_decode(file_get_contents('php://input'), true);

    if (!empty($data['email']) && !empty($data['password'])) {
        
        $email = $conn->real_escape_string($data['email']);
        $password = $data['password'];

        // Find the user by email
        $sql = "SELECT id, username, password_hash FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            // User found
            $user = $result->fetch_assoc();
            
            // --- Verify the password ---
            if (password_verify($password, $user['password_hash'])) {
                // Password is correct!
                
                // --- Session Management ---
                // Store user data in the session
                $_SESSION['loggedin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];

                $response['success'] = true;
                $response['message'] = 'Login successful! Redirecting...';
                $response['username'] = $user['username'];
            } else {
                // Invalid password
                $response['success'] = false;
                $response['message'] = 'Invalid email or password.';
            }
        } else {
            // User not found
            $response['success'] = false;
            $response['message'] = 'Invalid email or password.';
        }
        
        $stmt->close();
        
    } else {
        $response['success'] = false;
        $response['message'] = 'Please provide email and password.';
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

$conn->close();
echo json_encode($response);

?>