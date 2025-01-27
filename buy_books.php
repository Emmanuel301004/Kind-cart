<?php
session_start();

<<<<<<< HEAD
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
=======
// Check if the user is logged in
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

<<<<<<< HEAD
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
=======
// Simulating fetching available books for sale from the database
$available_books = [
    ["title" => "Book 1", "price" => "$20", "author" => "Author 1"],
    ["title" => "Book 2", "price" => "$15", "author" => "Author 2"],
    ["title" => "Book 3", "price" => "$25", "author" => "Author 3"],
];
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Books</title>
<<<<<<< HEAD
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9fafb;
=======
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
            margin: 0;
            padding: 0;
        }

<<<<<<< HEAD
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
=======
        .navbar {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
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
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
        }

        .book-item button {
            background-color: #4CAF50;
            color: white;
<<<<<<< HEAD
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background-color 0.3s;
=======
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
        }

        .book-item button:hover {
            background-color: #45a049;
        }
<<<<<<< HEAD

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
=======
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div>
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" style="color: white;">Logout</a>
</div>

<!-- Book List -->
<div class="book-list-container">
    <h1>Available Books for Sale</h1>
    <?php foreach ($available_books as $book): ?>
        <div class="book-item">
            <div>
                <p><strong>Title:</strong> <?php echo htmlspecialchars($book['title']); ?></p>
                <p><strong>Author:</strong> <?php echo htmlspecialchars($book['author']); ?></p>
                <p><strong>Price:</strong> <?php echo htmlspecialchars($book['price']); ?></p>
            </div>
            <div>
                <button>Buy Now</button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

</body>
</html>

>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
