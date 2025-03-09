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

// Calculate total
$total = 0;
$items = [];
if ($cartResult->num_rows > 0) {
    while ($row = $cartResult->fetch_assoc()) {
        $total += $row['price'] * $row['quantity'];
        $items[] = $row;
    }
    // Reset result pointer
    $cartResult->data_seek(0);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Kind Kart</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        
        /* Keeping navbar styles unchanged as requested */
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
        
        /* Improved container and content styles */
        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .page-title {
            color: #2e7d32;
            margin-bottom: 30px;
            font-size: 28px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        
        .cart-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .cart-table th {
            background: #4CAF50;
            color: white;
            font-weight: 500;
            text-align: left;
            padding: 15px;
        }
        
        .cart-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
        }
        
        .cart-table tr:last-child td {
            border-bottom: none;
        }
        
        .cart-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .product-name {
            font-weight: 500;
            color: #333;
        }
        
        .price {
            color: #2e7d32;
            font-weight: 500;
        }
        
        .quantity {
            background: #f0f0f0;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            min-width: 30px;
            text-align: center;
        }
        
        .btn {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s;
            border: none;
            text-align: center;
        }
        
        .btn-remove {
            background: #e74c3c;
            color: white;
        }
        
        .btn-remove:hover {
            background: #c0392b;
        }
        
        .cart-summary {
            background: #f9f9f9;
            padding: 20px;
            border-top: 1px solid #eee;
        }
        
        .cart-total {
            display: flex;
            justify-content: space-between;
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 20px;
        }
        
        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 15px;
        }
        
        .btn-checkout {
            background: #2e7d32;
            color: white;
            padding: 12px 24px;
            font-size: 16px;
        }
        
        .btn-checkout:hover {
            background: #1b5e20;
        }
        
        .btn-clear {
            background: #f39c12;
            color: white;
        }
        
        .btn-clear:hover {
            background: #e67e22;
        }
        
        .btn-continue {
            background: #3498db;
            color: white;
        }
        
        .btn-continue:hover {
            background: #2980b9;
        }
        
        .empty-cart {
            text-align: center;
            padding: 60px 20px;
            color: #777;
        }
        
        .empty-cart-icon {
            font-size: 60px;
            margin-bottom: 20px;
            color: #ccc;
        }
        
        .empty-cart-message {
            font-size: 18px;
            margin-bottom: 30px;
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
            <a href="my_listings.php">My Listings</a>
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
        <h1 class="page-title">Your Shopping Cart</h1>
        
        <?php if ($cartResult->num_rows == 0): ?>
            <div class="cart-container">
                <div class="empty-cart">
                    <div class="empty-cart-icon">🛒</div>
                    <div class="empty-cart-message">Your cart is empty</div>
                    <a href="buy_books.php" class="btn btn-continue">Continue Shopping</a>
                </div>
            </div>
        <?php else: ?>
            <div class="cart-container">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td class="product-name"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td class="price"><?php echo number_format($item['price'], 2); ?></td>
                                <td><span class="quantity"><?php echo htmlspecialchars($item['quantity']); ?></span></td>
                                <td class="price"><?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                <td><a href="cart.php?remove=<?php echo $item['book_id']; ?>" class="btn btn-remove">Remove</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Total:</span>
                        <span class="price"><?php echo number_format($total, 2); ?></span>
                    </div>
                    
                    <div class="action-buttons">
                        <a href="checkout.php" class="btn btn-checkout">Proceed to Checkout</a>
                        <a href="cart.php?clear=true" class="btn btn-clear">Clear Cart</a>
                        <a href="buy_books.php" class="btn btn-continue">Continue Shopping</a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>