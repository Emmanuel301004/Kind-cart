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

// Fetch all books from the database
$sql = "SELECT * FROM books";
$result = $conn->query($sql);
$cartItems = [];

// Fetch current cart items for the user
$cartSql = "SELECT book_id FROM cart WHERE user_id = '$user_id'";
$cartResult = $conn->query($cartSql);
if ($cartResult) {
    while ($cartRow = $cartResult->fetch_assoc()) {
        $cartItems[] = $cartRow['book_id'];
    }
}

$alertMessage = ''; // Variable to hold alert message

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['book_id'])) {
    $book_id = $_POST['book_id'];
    $quantity = 1;  // Default quantity for cart

    // Check if the book is already in the cart
    $checkCartSql = "SELECT * FROM cart WHERE user_id = '$user_id' AND book_id = '$book_id'";
    $checkCartResult = $conn->query($checkCartSql);

    if ($checkCartResult->num_rows == 0) { // Only insert if book is not already in the cart
        $sql = "INSERT INTO cart (user_id, book_id, quantity) VALUES ('$user_id', '$book_id', '$quantity')";
        if ($conn->query($sql) === TRUE) {
            $alertMessage = 'Book added to cart!';
            $cartItems[] = $book_id;
        } else {
            $alertMessage = 'Error: ' . $conn->error;
        }
    } else {
        $alertMessage = 'This book is already in your cart.';
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Books</title>
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
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
            text-transform: uppercase;
        }

        .book-list-container {
            margin: 50px auto;
            padding: 20px 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 800px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .book-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }

        .book-item button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
        }

        .book-item button:hover {
            background-color: #45a049;
        }

        .book-item button.added {
            background-color: #ccc;
            cursor: not-allowed;
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

<!-- Book List -->
<div class="book-list-container">
    <h1>Available Books for Sale</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="book-item">
            <div>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($row['owner_name']); ?></p>
                <p><strong>Price:</strong> $<?php echo htmlspecialchars($row['price']); ?></p>
            </div>
            <div>
                <form method="POST">
                    <input type="hidden" name="book_id" value="<?php echo $row['book_id']; ?>">
                    <button type="submit" class="<?php echo in_array($row['book_id'], $cartItems) ? 'added' : ''; ?>">
                        <?php echo in_array($row['book_id'], $cartItems) ? 'Added to Cart' : 'Add to Cart'; ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- JavaScript for Alert Message -->
<?php if (!empty($alertMessage)): ?>
    <script>
        alert("<?php echo $alertMessage; ?>");
    </script>
<?php endif; ?>

</body>
</html>
