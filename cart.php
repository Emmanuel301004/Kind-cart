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
      body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #333;
        }
      .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #2e7d32;
            padding: 15px 20px;
            color: white;
            position: fixed;
            width: 100%;
            top: 0;
            left: 0;
            z-index: 1000;
            box-sizing: border-box;
        }
        .navbar .logo {
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
            color: white;
        }
        .nav-links {
            display: flex;
            gap: 15px;
        }
        .nav-links a {
            color: white;
            text-decoration: none;
            font-size: 1.3rem;
        }
        .nav-links a:hover {
            color: #c8e6c9;
        }
        .profile-dropdown {
            position: relative;
        }
        .profile-dropdown a {
            color: white;
            text-decoration: none;
        }
        .profile-dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: white;
            min-width: 150px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 5px;
        }
        .profile-dropdown-content a {
            color: black;
            padding: 10px;
            text-decoration: none;
            display: block;
        }
        .profile-dropdown-content a:hover {
            background-color: #f1f1f1;
        }
        .profile-dropdown:hover .profile-dropdown-content {
            display: block;
        }
       
        h2 { color: #4CAF50; }
        table { width: 100%; border-collapse: collapse; background: #fff; 
        
        }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #4CAF50; color: white; }
        .btn { padding: 5px 10px; color: white; text-decoration: none; border-radius: 4px; }
        .remove { background: #e74c3c; }
        .clear { background: #f39c12; }
        .checkout { background: #3498db; }
        .container{
            margin-top:2.3%;
            margin-left:0;
        }
    </style>
</head>
<body>
    
<div class="navbar">
        <a href="dashboard.php" class="logo">📚 Kind Kart</a>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="buy_books.php">Buy Books</a>
            <a href="sell_books.php">Sell Books</a>
            <a href="order_history.php">Orders</a>
            <a href="cart.php"><img src="cart.png" alt="Cart" style="width:20px; height:20px; vertical-align:middle;"> Cart</a>
        </div>
        <div class="profile-dropdown">
            <a href="#"><img src="profile.png" alt="Profile"></a>
            <div class="profile-dropdown-content">
                <a href="profile.php">Settings</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
    <div class="container">
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
        <br>    
        <a href="checkout.php" class="btn checkout">Proceed to Checkout</a> |
        <a href="cart.php?clear=true" class="btn clear">Clear Cart</a>
    </div>
      <?php endif; ?>
</body>
</html>
