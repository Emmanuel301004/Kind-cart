<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

// Simulating fetching available books for sale from the database
$available_books = [
    ["title" => "Book 1", "price" => "$20", "author" => "Author 1"],
    ["title" => "Book 2", "price" => "$15", "author" => "Author 2"],
    ["title" => "Book 3", "price" => "$25", "author" => "Author 3"],
];
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

