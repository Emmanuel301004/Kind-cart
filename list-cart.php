<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id'];

$query = "
    SELECT m.name, m.description, m.price 
    FROM cart c 
    JOIN materials m ON c.material_id = m.id 
    WHERE c.user_id = '$user_id'
";
$result = $conn->query($query);

$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

echo json_encode($cart_items);
?>
