<?php
/*
 * get_plants.php
 * UPDATED:
 * - Removed closing ?> tag.
 */

// Set the content type to return JSON *first*
header('Content-Type: application/json');

// Include the database connection file
require_once 'db_connect.php';

$plants = [];
$response = ['success' => false]; // Default response

// SQL query to get all plants
$sql = "SELECT id, name, scientific_name, care_level, care_level_icon, watering, light, image_url FROM plants";
$result = $conn->query($sql);

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $plants[] = $row;
        }
    }
    // Free the result set
    $result->free();
    $response['success'] = true;
    $response['plants'] = $plants;
} else {
    // Handle query error
    $response['message'] = 'Error fetching plants: ' . $conn->error;
}

// Close the database connection
$conn->close();

// Send the JSON response
echo json_encode($response);

// No closing ?> tag