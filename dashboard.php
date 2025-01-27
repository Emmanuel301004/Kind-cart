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
<<<<<<< HEAD
    <link rel="stylesheet" href="style.css">
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
=======
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
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344

.navbar .logout:hover {
    background-color: #c0392b;
}

<<<<<<< HEAD
/* Dashboard Content */
.dashboard-container {
    text-align: center;
    padding: 50px 20px;
    max-width: 800px;
    margin: 50px auto;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.dashboard-container .button-container {
    margin-top: 30px;
}

.dashboard-container .btn {
    display: inline-block;
    margin: 10px;
    padding: 15px 30px;
    background-color: #3498db;
    color: white;
    font-size: 1rem;
    border: none;
    border-radius: 5px;
    text-decoration: none;
    transition: background-color 0.3s ease;
}

.dashboard-container .btn:hover {
    background-color: #2980b9;
}
</style>
</head>
<body>

=======
            p {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
<div class="navbar">
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
<<<<<<< HEAD
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
=======
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
</div>

<<<<<<< HEAD

=======
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
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
