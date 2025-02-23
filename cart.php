<?php
session_start();

// Database connection
$host = 'localhost';
$db = 'user_management';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Remove item from cart
if (isset($_GET['remove'])) {
    $book_id = $_GET['remove'];
    $deleteSql = "DELETE FROM cart WHERE user_id = '$user_id' AND book_id = '$book_id'";
    $conn->query($deleteSql);
    header("Location: cart.php");
    exit();
}

// Clear entire cart
if (isset($_GET['clear'])) {
    $clearSql = "DELETE FROM cart WHERE user_id = '$user_id'";
    $conn->query($clearSql);
    header("Location: cart.php");
    exit();
}

// Fetch cart items from database
$cartSql = "SELECT books.book_id, books.title, books.price, cart.quantity FROM cart JOIN books ON cart.book_id = books.book_id WHERE cart.user_id = '$user_id'";
$cartResult = $conn->query($cartSql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f8f9fa; padding: 20px; }
        h2 { color: #4CAF50; }
        table { width: 100%; border-collapse: collapse; background: #fff; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .btn { padding: 5px 10px; color: white; text-decoration: none; border-radius: 4px; }
        .remove { background: #e74c3c; }
        .clear { background: #f39c12; }
        .checkout { background: #3498db; }
    </style>
</head>
<body>
    <h2>Your Shopping Cart</h2>
    <?php if ($cartResult->num_rows == 0): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Remove</th>
            </tr>
            <?php while ($row = $cartResult->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['title']); ?></td>
                    <td>$<?php echo number_format($row['price'], 2); ?></td>
                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                    <td><a href="cart.php?remove=<?php echo $row['book_id']; ?>" class="btn remove">Remove</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
        <br>
        <a href="checkout.php" class="btn checkout">Proceed to Checkout</a> |
        <a href="cart.php?clear=true" class="btn clear">Clear Cart</a>
    <?php endif; ?>
</body>
</html>
