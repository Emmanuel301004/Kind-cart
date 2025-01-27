<?php
session_start();

// Check if orders exist
if (!isset($_SESSION['orders'])) {
    $_SESSION['orders'] = []; // Initialize if not set
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="profile.php">Profile</a>
        <a href="cart.php">Cart</a>
        <a href="ordered.php">Orders</a>
    </div>

    <!-- Orders Content -->
    <div class="form-container">
        <h1>Your Orders</h1>
        <?php if (empty($_SESSION['orders'])): ?>
            <p>You have no orders yet.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($_SESSION['orders'] as $order): ?>
                    <li>Order: <?php echo htmlspecialchars($order); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</body>
</html>
