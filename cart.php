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
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_book_id'])) {
    $remove_id = $_POST['remove_book_id'];
    $sql = "DELETE FROM cart WHERE user_id='$user_id' AND book_id='$remove_id'";
    $conn->query($sql);
    echo "<script>window.location.href='cart.php';</script>";
}

// Fetch cart items
$sql = "SELECT books.id, books.title, books.price FROM cart 
        JOIN books ON cart.book_id = books.id 
        WHERE cart.user_id = '$user_id'";
$result = $conn->query($sql);

$total_price = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart</title>
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

        .cart-container { width: 80%; margin: auto; padding: 20px; background-color: #fff; border-radius: 10px; }
        .cart-item { display: flex; justify-content: space-between; padding: 10px; background-color: #f1f1f1; margin-bottom: 10px; }
        .remove-btn { background-color: red; color: white; padding: 5px; border: none; cursor: pointer; }
        .total { font-size: 20px; font-weight: bold; text-align: right; margin-top: 20px; }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div>
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="cart.php">Cart</a>
        <a href="ordered.php">Orders</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<!-- Cart -->
<div class="cart-container">
    <h1>Your Cart</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="cart-item">
            <p><?= htmlspecialchars($row['title']); ?> - $<?= htmlspecialchars($row['price']); ?></p>
            <form method="POST">
                <input type="hidden" name="remove_book_id" value="<?= $row['id']; ?>">
                <button class="remove-btn" type="submit">Remove</button>
            </form>
        </div>
        <?php $total_price += $row['price']; ?>
    <?php endwhile; ?>
    <p class="total">Total: $<?= $total_price; ?></p>
    <a href="ordered.php"><button>Proceed to Checkout</button></a>
</div>

</body>
</html>
