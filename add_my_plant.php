<?php
// add_my_plant.php
header('Content-Type: application/json');

session_start();
require_once 'db_connect.php';

// Must be logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Login required"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// read input json
$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['plant_id'])) {
    echo json_encode(["success" => false, "message" => "Invalid input"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$plant_id = $data['plant_id'];


$sql = "INSERT INTO my_plants (user_id, plant_id) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $plant_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Added to your collection"]);
} else {
    echo json_encode(["success" => false, "message" => "DB error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
