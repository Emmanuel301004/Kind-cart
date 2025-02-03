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

// Fetch user's cart items
$sql = "SELECT c.book_id, b.title, b.price, c.quantity
        FROM cart c
        JOIN books b ON c.book_id = b.id
        WHERE c.user_id = '$user_id'";
$result = $conn->query($sql);

// If the user submits an order (fakes payment and status)
$alertMessage = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Simulate payment process
    echo "<script>alert('Payment processed successfully!');</script>";

    // Insert the order into the database
    $order_status = 'Processing'; // This is the fake status
    $order_query = "INSERT INTO orders (user_id, book_id, title, price, created_at)
                    SELECT user_id, book_id, title, price, NOW()
                    FROM cart WHERE user_id = '$user_id'";
    
    if ($conn->query($order_query) === TRUE) {
        // Clear the cart after order
        $clear_cart_query = "DELETE FROM cart WHERE user_id = '$user_id'";
        $conn->query($clear_cart_query);

        // Simulate updating order status
        $alertMessage = 'Order placed successfully! Your order is being processed. Estimated delivery: 3-5 business days.';
    } else {
        $alertMessage = 'Error: ' . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 0;
            color: #333;
        }

        h1, h2 {
            color: #222;
            margin-bottom: 20px;
        }

        p {
            color: #555;
            font-size: 1.1rem;
        }

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

        .cart-container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            text-align: center;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .cart-item .price {
            font-weight: bold;
        }

        .cart-item .quantity {
            color: #555;
        }

        .order-summary {
            margin-top: 30px;
            padding: 10px;
            background-color: #ecf0f1;
            border-radius: 5px;
        }

        .order-button {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .order-button:hover {
            background-color: #2ecc71;
        }

        /* Order Status */
        .order-status {
            margin-top: 30px;
            padding: 15px;
            background-color: #f4e1d2;
            border-radius: 5px;
        }

        .order-status p {
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<div class="cart-container">
    <h1>Your Cart</h1>

    <?php if ($result->num_rows > 0): ?>
        <?php $total_price = 0; ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="cart-item">
                <div>
                    <p><strong>Title:</strong> <?= htmlspecialchars($row['title']); ?></p>
                    <p><strong>Quantity:</strong> <?= $row['quantity']; ?></p>
                </div>
                <div>
                    <p class="price">$<?= number_format($row['price'], 2); ?></p>
                </div>
            </div>
            <?php $total_price += $row['price'] * $row['quantity']; ?>
        <?php endwhile; ?>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <p><strong>Total Price:</strong> $<?= number_format($total_price, 2); ?></p>
        </div>
        <form method="POST">
            <button type="submit" class="order-button">Proceed to Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty. Please add items to your cart.</p>
    <?php endif; ?>

</div>

<?php if (!empty($alertMessage)): ?>
    <div class="order-status">
        <p><?= $alertMessage; ?></p>
    </div>
<?php endif; ?>

</body>
</html>
