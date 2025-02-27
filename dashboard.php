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

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch all books from the database
$sql = "SELECT * FROM books WHERE status = 'Available'";
$result = $conn->query($sql);

// Fetch Recently Added Books (latest 4 entries)
$recentBooksQuery = "SELECT * FROM books WHERE status = 'Available' ORDER BY created_at DESC LIMIT 7";
$recentBooks = $conn->query($recentBooksQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kind Kart - Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
            font-size: 1rem;
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
        .hero {
            text-align: center;
            padding: 100px 20px;
            background: #81c784;
            color: white;
            margin-top: 60px;
        }
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .hero p {
            font-size: 1.2rem;
        }
        .about {
            padding: 50px 20px;
            text-align: center;
            background: white;
        }
        .about h2 {
            font-size: 2rem;
            color: #2e7d32;
        }
        .about p {
            font-size: 1.1rem;
            max-width: 800px;
            margin: auto;
            line-height: 1.6;
        }
          /* Books Section */
          /* Container for better alignment */
.books-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    padding: 20px;
}

/* Individual book card */
.book-card {
    background: white;
    padding: 20px;
    width: 220px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.book-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

/* Title Styling */
.book-card h3 {
    color: #0b6623;
    font-size: 1.2rem;
    font-weight: bold;
    margin-bottom: 8px;
}

/* Owner & Price Styling */
.book-card p {
    font-size: 0.9rem;
    color: #333;
    margin: 5px 0;
}

/* View More Button */
.more-btn {
    display: block;
    width: 150px;
    margin: 20px auto;
    padding: 12px;
    background: #007BFF;
    color: #fff;
    font-size: 1rem;
    font-weight: bold;
    border-radius: 8px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease-in-out;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: none;
    cursor: pointer;
}

.more-btn:hover {
    background: #0056b3;
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

        .footer {
            background: #2e7d32;
            color: white;
            text-align: center;
            padding: 20px;
            margin-top: 20px;
        }
        .footer a {
            color: #c8e6c9;
            text-decoration: none;
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

    <!-- Hero Section -->
    <div class="hero">
        <h1>Welcome to Kind Kart</h1>
        <p>Connecting juniors and seniors for buying and selling study materials affordably.</p>
    </div>

    <!-- About Section -->
    <div class="about">
        <h2>About Kind Kart</h2>
        <p>
            Kind Kart is a student-friendly platform designed to help juniors buy study materials at lower costs 
            while allowing seniors to sell their old books and notes. This initiative promotes affordability, 
            sustainability, and a helpful community among students.
        </p>
    </div>

     

    <!-- Recently Added Books Section -->
    <div class="books-section">
        <h2>Recently Added Books</h2>
        <div class="books-container">
            <?php while ($book = $recentBooks->fetch_assoc()): ?>
                <div class="book-card">
                    <h3><?= htmlspecialchars($book['title']) ?></h3>
                    <p><strong>Owner:</strong> <?= htmlspecialchars($book['owner_name']) ?></p>
                    <p><strong>Price:</strong> ₹<?= htmlspecialchars($book['price']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
        <a href="buy_books.php" class="more-btn">View More</a>
    </div>
    <!-- Footer Section -->
    <div class="footer">
        <p>© 2025 Kind Kart | <a href="contact.php">Contact Us</a> | <a href="privacy.php">Privacy Policy</a></p>
    </div>

</body>
</html>
