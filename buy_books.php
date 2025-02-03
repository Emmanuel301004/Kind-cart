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

// Fetch user's cart items to disable already added books
$cart_sql = "SELECT book_id FROM cart WHERE user_id = '$user_id'";
$cart_result = $conn->query($cart_sql);
$cart_items = [];

while ($row = $cart_result->fetch_assoc()) {
    $cart_items[] = $row['book_id'];
}

$alertMessage = ''; // Variable to hold alert message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $quantity = 1;  // Default quantity for cart
    
    if (!in_array($book_id, $cart_items)) {
        // Add to cart
        $sql = "INSERT INTO cart (user_id, book_id, quantity) VALUES ('$user_id', '$book_id', '$quantity')";
        if ($conn->query($sql) === TRUE) {
            $alertMessage = 'Book added to cart!';
            // Refresh page to update the button state
            echo "<script>window.location.href='buy_books.php';</script>";
        } else {
            $alertMessage = 'Error: ' . $conn->error;
        }
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
   .book-list-container {
            width: 80%;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            text-align: center;
        }
        .book-item {
            display: flex;
            justify-content: space-between;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        .book-item button {
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .added {
            background-color: gray;
            cursor: not-allowed;
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

<div class="book-list-container">
    <h1>Available Books for Sale</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="book-item">
            <div>
                <p><strong>Title:</strong> <?= htmlspecialchars($row['title']); ?></p>
                <p><strong>Author:</strong> <?= htmlspecialchars($row['owner_name']); ?></p>
                <p><strong>Price:</strong> $<?= htmlspecialchars($row['price']); ?></p>
            </div>
            <div>
                <form method="POST">
                    <input type="hidden" name="book_id" value="<?= $row['id']; ?>">
                    <button type="submit" <?= in_array($row['id'], $cart_items) ? 'class="added" disabled' : '' ?>>
                        <?= in_array($row['id'], $cart_items) ? 'Added to Cart' : 'Add to Cart' ?>
                    </button>
                </form>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<?php if (!empty($alertMessage)): ?>
    <script>alert("<?= $alertMessage; ?>");</script>
<?php endif; ?>

</body>
</html>
