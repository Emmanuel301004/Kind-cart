<?php
include 'db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$material_id = $data['material_id'];
$user_id = $_SESSION['user_id'];

$query = "INSERT INTO cart (user_id, material_id) VALUES ('$user_id', '$material_id')";
if ($conn->query($query)) {
    echo "Item added to cart successfully!";
} else {
    echo "Failed to add item to cart.";
}
?>
