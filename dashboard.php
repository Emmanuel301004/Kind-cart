<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Exchange Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <style>
        /* General Styling */
        body {
            font-family: 'Arial', sans-serif;
            background-color:rgb(16, 17, 17);
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
            background: rgba(44, 62, 80, 0.14);
            padding: 15px 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: fixed;
            width: 98vw;
            top: 0;
            left: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
        }

        .navbar .logo {
            color: #ecf0f1;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-links {
            flex-grow: 1;
            text-align: center;
        }

        .nav-links a {
            color: #ecf0f1;
            text-decoration: none;
            margin: 0 15px;
            font-size: 1rem;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: #bdc3c7;
        }

        .nav-icons a {
            color: #ecf0f1;
            text-decoration: none;
            margin-left: 15px;
            font-size: 1.2rem;
            transition: color 0.3s ease;
        }

        .nav-icons a:hover {
            color: #bdc3c7;
        }

        .logout {
            background-color: #e74c3c;
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 1rem;
            text-transform: uppercase;
        }

        .logout:hover {
            background-color: #c0392b;
        }

        /* Dashboard Container */
        .dashboard-container {
            text-align: center;
            margin: 100px auto 50px;
            padding: 20px 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 800px;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 10px;
            color: #4CAF50;
        }

        .button-container {
            margin-top: 30px;
        }

        .button-container a {
            text-decoration: none;
            margin: 0 10px;
            padding: 12px 20px;
            background-color: #4CAF50;
            color: white;
            border-radius: 5px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .button-container a:hover {
            background-color: #45a049;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .nav-links {
                display: none;
            }
            .dashboard-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<!-- Navbar -->
<div class="navbar">
    <a href="dashboard.php" class="logo">📚 Kind Kart</a>
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="order_history.php">Orders</a>
    </div>
    <div class="nav-icons">
        <a href="cart.php"><i class="fas fa-shopping-cart"></i> cart</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i>profile</a>
        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> login </a>
    </div>
</div>

<!-- Dashboard Content -->
<div class="dashboard-container">
    <h1>Welcome to the Book Exchange</h1>
    <p>Hi, <?php echo htmlspecialchars($_SESSION['email']); ?>! <br> Find or sell your books effortlessly with our platform.</p>
    <div class="button-container">
        <a href="buy_books.php">Browse Books</a>
        <a href="sell_books.php">Sell Your Books</a>
    </div>
</div>

</body>
</html>
