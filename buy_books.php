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

$alertMessage = ''; // Variable to hold alert message

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $book_id = $_POST['book_id'];
    $quantity = 1;  // Default quantity for cart
    
    // Add to cart
    $sql = "INSERT INTO cart (user_id, book_id, quantity) VALUES ('$user_id', '$book_id', '$quantity')";
    
    if ($conn->query($sql) === TRUE) {
        $alertMessage = 'Book added to cart!'; // Success message
    } else {
        $alertMessage = 'Error: ' . $conn->error; // Error message
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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-family: 'Arial', sans-serif;
            color: #333;
            font-size: 2rem;
        }

        a {
            text-decoration: none;
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


        /* Book Listings */
        .book-listings {
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .book-item {
            border-bottom: 1px solid #ccc;
            padding: 20px 0;
        }

        .book-item h2 {
            color: #4CAF50;
            font-size: 1.5rem;
        }

        .book-item p {
            font-size: 1rem;
            margin: 5px 0;
        }

        .book-item button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .book-item button:hover {
            background-color: #45a049;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar div {
                flex-direction: column;
            }
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


<!-- Book Listings -->
<div class="book-listings">
    <h1>Available Books</h1>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="book-item">
            <h2><?php echo $row['title']; ?></h2>
            <p><strong>Owner:</strong> <?php echo $row['owner_name']; ?></p>
            <p><strong>Contact:</strong> <?php echo $row['contact']; ?></p>
            <p><strong>Course:</strong> <?php echo $row['course']; ?></p>
            <p><strong>Semester:</strong> <?php echo $row['semester']; ?></p>
            <p><strong>Condition:</strong> <?php echo $row['book_condition']; ?></p>
            <p><strong>Price:</strong> $<?php echo $row['price']; ?></p>
            <form method="POST">
                <input type="hidden" name="book_id" value="<?php echo $row['id']; ?>">
                <button type="submit">Add to Cart</button>
            </form>
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
