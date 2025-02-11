<?php
session_start();

$host = 'localhost';
$db = 'user_management';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$stmt = $conn->prepare("SELECT c.book_id, b.title, b.price FROM cart c JOIN books b ON c.book_id = b.id WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Insert orders and delete books
    while ($row = $result->fetch_assoc()) {
        $book_id = $row['book_id'];

        // Insert into orders
        $stmt_order = $conn->prepare("INSERT INTO orders (user_id, book_id) VALUES (?, ?)");
        $stmt_order->bind_param("ii", $user_id, $book_id);
        $stmt_order->execute();

        // Delete book from books table
        $stmt_delete_book = $conn->prepare("DELETE FROM books WHERE id = ?");
        $stmt_delete_book->bind_param("i", $book_id);
        $stmt_delete_book->execute();
    }

    // Clear the cart
    $stmt_clear = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt_clear->bind_param("i", $user_id);
    $stmt_clear->execute();

    // Redirect to ordered page
    header("Location: ordered.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
          /* Include your original CSS styles */
          body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
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
        .form-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-container h1 {
            text-align: center;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            background: #f9f9f9;
            padding: 10px;
            margin-bottom: 5px;
        }
        button {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 4px;
        }
        button:hover {
            background-color: #27ae60;
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
    <div class="form-container">
        <h1>Checkout</h1>
        <h2>Your Cart</h2>
        <ul>
            <?php
            $stmt->execute();
            $result = $stmt->get_result();
            $total_price = 0;
            while ($row = $result->fetch_assoc()) {
                echo "<li>{$row['title']} - $ {$row['price']}</li>";
                $total_price += $row['price'];
            }
            ?>
        </ul>

        <h2>Total: $<?php echo number_format($total_price, 2); ?></h2>

        <form method="POST">
            <button type="submit">Pay Now</button>
        </form>
    </div>
</body>
</html>
