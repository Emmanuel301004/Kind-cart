<?php
include 'db.php';
session_start();

$data = json_decode(file_get_contents("php://input"), true);
$address = $data['address'];
$user_id = $_SESSION['user_id'];

$query = "
    INSERT INTO orders (user_id, address, status) 
    VALUES ('$user_id', '$address', 'Placed')
";
if ($conn->query($query)) {
    $order_id = $conn->insert_id;

    // Move items from cart to orders_items table
    $cart_query = "SELECT material_id FROM cart WHERE user_id = '$user_id'";
    $cart_result = $conn->query($cart_query);

    while ($cart_row = $cart_result->fetch_assoc()) {
        $material_id = $cart_row['material_id'];
        $order_item_query = "
            INSERT INTO orders_items (order_id, material_id) 
            VALUES ('$order_id', '$material_id')
        ";
        $conn->query($order_item_query);
    }

    // Clear the cart
    $clear_cart_query = "DELETE FROM cart WHERE user_id = '$user_id'";
    $conn->query($clear_cart_query);

    echo "Order placed successfully!";
} else {
    echo "Failed to place order.";
}
?>
