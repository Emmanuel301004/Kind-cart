<?php
session_start();

// Assume cart data is stored in $_SESSION['cart']
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Check if an item is being added to the cart
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_title = $_POST['title'];
    // Add book to cart
    $_SESSION['cart'][] = $book_title;

    // Show success message
    echo "<script>alert('$book_title has been added to your cart!');</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Navbar */
.navbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #2c3e50;
    padding: 15px 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar a {
    color: #ecf0f1;
    text-decoration: none;
    margin-right: 15px;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.navbar a:hover {
    color: #bdc3c7;
}

.navbar .logout {
    background-color: #e74c3c;
    color: white;
    padding: 8px 15px;
    border-radius: 4px;
    font-size: 1rem;
    text-transform: uppercase;
}

.navbar .logout:hover {
    background-color: #c0392b;
}

    </style>
</head>
<body>
<div class="navbar">
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>
<!-- Cart Content -->
<div class="form-container">
    <h1>Your Cart</h1>
    <?php if (empty($_SESSION['cart'])): ?>
        <p>Your cart is empty.</p>
    <?php else: ?>
        <ul>
            <?php foreach ($_SESSION['cart'] as $item): ?>
                <li><?php echo htmlspecialchars($item); ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
    <a href="checkout.php"><button>Proceed to Checkout</button></a>
</div>

</body>
</html>
