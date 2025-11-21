<?php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT 
        mp.added_date,
        p.id AS plant_id,
        p.name,
        p.scientific_name,
        p.image_url,
        p.watering,
        p.light
    FROM my_plants mp
    JOIN plants p ON mp.plant_id = p.id
    WHERE mp.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$plants = [];
while ($row = $result->fetch_assoc()) {
    $plants[] = $row;
}

echo json_encode(["success" => true, "plants" => $plants]);
?>
