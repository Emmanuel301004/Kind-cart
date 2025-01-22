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
    <style>
        /* General Styling */
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

        /* Navbar Styling */
        .navbar {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar a {
            color: white;
            text-decoration: none;
            margin: 0 10px;
            font-size: 16px;
        }

        .navbar a:hover {
            text-decoration: underline;
        }

        .navbar .logout {
            background-color: #FF6347;
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            color: white;
            cursor: pointer;
            text-decoration: none;
        }

        .navbar .logout:hover {
            background-color: #e55347;
        }

        /* Dashboard Container */
        .dashboard-container {
            text-align: center;
            margin: 50px auto;
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

        p {
            font-size: 18px;
            margin-bottom: 20px;
            color: #555;
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
            .dashboard-container {
                padding: 20px;
            }

            h1 {
                font-size: 28px;
            }

            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<div class="navbar">
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
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
