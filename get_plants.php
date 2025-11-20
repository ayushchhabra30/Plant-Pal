<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$sql = "SELECT id, name, scientific_name, care_level, care_level_icon, watering, light, image_url FROM plants";
$result = $conn->query($sql);

$response = ["success" => true, "plants" => []];

while ($row = $result->fetch_assoc()) {
    $response["plants"][] = $row;
}

echo json_encode($response);
$conn->close();
