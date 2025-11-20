<?php
/*
 * get_weather_tip.php
 * A new API endpoint to get a weather-based tip.
 * It checks if the user is logged in, finds their location,
 * and returns a (simulated) weather tip.
 */

session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';

$response = [];

// Check if user is logged in by checking the session
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $user_id = $_SESSION['user_id'];
    
    // 1. Get the user's location from the 'users' table
    $sql = "SELECT location FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        $location = $user['location'];
        
        if ($location) {
            // 2. --- SIMULATED WEATHER API CALL ---
            // In a real application, you would use $location to call a real
            // weather API (like OpenWeatherMap) to get live data.
            // Since we can't safely store an API key here, we'll simulate it.
            
            $simulated_temp = rand(5, 30); // Random temp between 5°C and 30°C
            $tip = "";
            
            if ($simulated_temp < 10) {
                $tip = "It's cold in " . htmlspecialchars($location) . " ($simulated_temp°C)! Consider bringing sensitive plants indoors.";
            } else if ($simulated_temp > 25) {
                $tip = "It's a hot day in " . htmlspecialchars($location) . " ($simulated_temp°C)! Your plants may need extra water today.";
            } else {
                $tip = "The weather in " . htmlspecialchars($location) . " is pleasant ($simulated_temp°C). A great day for gardening!";
            }
            
            $response['success'] = true;
            $response['tip'] = $tip;
        
        } else {
            $response['success'] = false;
            $response['message'] = 'You have not set a location in your profile.';
        }
        
    } else {
        $response['success'] = false;
        $response['message'] = 'Could not find user profile.';
    }
    $stmt->close();
    
} else {
    // User is not logged in
    $response['success'] = false;
    $response['message'] = 'User not logged in.';
}

$conn->close();
echo json_encode($response);

?>