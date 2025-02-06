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
$sql = "SELECT c.book_id, b.title, c.quantity, b.price
        FROM cart c
        JOIN books b ON c.book_id = b.id
        WHERE c.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn->begin_transaction();
    try {
        // Insert ordered items into orderedhistory
        $order_query = "INSERT INTO orderedhistory (book_id, book_name, price, order_date)
                        SELECT c.book_id, b.title, b.price, NOW()
                        FROM cart c
                        JOIN books b ON c.book_id = b.id
                        WHERE c.user_id = ?";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        
        // Remove ordered books from books table
        $delete_books_query = "DELETE FROM books WHERE id IN (SELECT book_id FROM cart WHERE user_id = ?)";
        $stmt = $conn->prepare($delete_books_query);
        $stmt->bind_param("i", $book);
        $stmt->execute();

        // Clear the cart after order
        $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
        $stmt = $conn->prepare($clear_cart_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        $conn->commit();

        // Redirect to success page
        header("Location: success.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        echo "Error processing order: " . $e->getMessage();
    }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Summary</title>
    <style>
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
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="cart-item">
                <div>
                    <p><strong>Title:</strong> <?= htmlspecialchars($row['title']); ?></p>
                    <p><strong>Quantity:</strong> <?= $row['quantity']; ?></p>
                    <p><strong>Price:</strong> ₹<?= number_format($row['price'], 2); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
        <form method="POST">
            <button type="submit" class="order-button">Proceed to Order</button>
        </form>
    <?php else: ?>
        <p>Your cart is empty. Please add items to your cart.</p>
    <?php endif; ?>
</div>

</body>
</html>
