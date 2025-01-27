<?php
session_start();

// If no items in the cart, redirect to the cart page
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Simulate payment process
    $payment_status = "Payment Successful! Your order has been placed.";

    // Store the cart items in orders and clear the cart
    $_SESSION['orders'] = array_merge($_SESSION['orders'], $_SESSION['cart']);
    $_SESSION['cart'] = []; // Clear cart

    echo "<script>alert('$payment_status');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Navbar -->
    <div class="navbar">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="profile.php">Profile</a>
        <a href="cart.php">Cart</a>
    </div>

    <!-- Checkout Form -->
    <div class="form-container">
        <h1>Checkout</h1>
        <h2>Your Cart</h2>
        <ul>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
            <?php endforeach; ?>
        </ul>

        <h2>Total: $<?php echo count($_SESSION['cart']) * 10; ?> </h2> <!-- Demo price $10 per book -->
        <form method="POST">
            <button type="submit">Pay Now</button>
        </form>
    </div>
</body>
</html>
