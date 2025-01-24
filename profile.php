<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Fetching user data from the database (this would typically be done through a database query)
$user_name = "John Doe"; // Replace with actual data
$email = $_SESSION['email'];
$course = "Computer Science"; // Replace with actual data
$semester = "6th"; // Replace with actual data

// Simulate fetching books the user bought or sold (to be replaced with actual DB queries)
$books_bought = ["Book 1", "Book 2", "Book 3"];
$books_sold = ["Book 4", "Book 5"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
        }

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

        .profile-container {
            margin: 50px auto;
            padding: 20px 40px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 600px;
        }

        h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #4CAF50;
        }

        .info {
            font-size: 18px;
            margin-bottom: 10px;
            color: #555;
        }

        .books-list {
            margin-top: 20px;
            text-align: left;
            font-size: 16px;
            color: #333;
        }

        .books-list h3 {
            margin-bottom: 10px;
        }

        .books-list ul {
            list-style-type: none;
            padding: 0;
        }

        .books-list li {
            padding: 5px;
            background-color: #f1f1f1;
            margin-bottom: 5px;
            border-radius: 5px;
        }
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

<!-- Profile Content -->
<div class="profile-container">
    <h1>Your Profile</h1>
    <p class="info"><strong>Name:</strong> <?php echo htmlspecialchars($user_name); ?></p>
    <p class="info"><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
    <p class="info"><strong>Course:</strong> <?php echo htmlspecialchars($course); ?></p>
    <p class="info"><strong>Semester:</strong> <?php echo htmlspecialchars($semester); ?></p>

    <div class="books-list">
        <h3>Books You've Bought:</h3>
        <ul>
            <?php foreach ($books_bought as $book): ?>
                <li><?php echo htmlspecialchars($book); ?></li>
            <?php endforeach; ?>
