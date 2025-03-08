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
$alertMessage = ''; // Variable for alerts

function onSuccess() {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function () {
            var toastEl = document.getElementById('toastMessage');
            if (toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
            }
        });
    </script>";
}

// Initialize filter variables
$filterCourse = isset($_GET['course']) ? $_GET['course'] : '';
$filterSemester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Prepare SQL for book fetching with optional filters
$sql = "SELECT * FROM books WHERE status = 'Available'";
if (!empty($filterCourse)) {
    $sql .= " AND course = '" . $conn->real_escape_string($filterCourse) . "'";
}
if (!empty($filterSemester)) {
    $sql .= " AND semester = '" . $conn->real_escape_string($filterSemester) . "'";
}

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
            $alertMessage = "Book added successfully!";
            $alertType = "success";
            onSuccess();   
            $cartItems[] = $book_id;
        } else {
            $alertMessage = 'Error: ' . $conn->error;
            $alertType = "danger";
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
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
            font-size: 1.3rem;
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
        .book-list-container {
            margin: 80px auto 40px; /* Added space below navbar */
            padding: 30px;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 90%;
            max-width: 1000px;
        }
        .filter-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-container select {
            padding: 8px;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .filter-container button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .filter-container button:hover {
            background-color: #45a049;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
            color: #4CAF50;
        }
        .book-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
        .book-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
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
    <a href="dashboard.php" class="logo">📚 Kind Kart</a>
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="my_listings.php">My Listings</a>
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

<!-- toaster -->
<div class="toast-container position-fixed bottom-0 end-0 p-3">
    <div id="toastMessage" class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Book added successfully! to cart
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Book List -->
<div class="book-list-container">
    <h1>Available Books for Sale</h1>
    
    <!-- Filter Section -->
    <form method="GET" class="filter-container">
        <select name="course" id="course">
            <option value="">Select Course</option>
            <option value="BSc CSMM" <?php echo ($filterCourse == 'BSc CSMM') ? 'selected' : ''; ?>>BSc CSMM</option>
            <option value="BCA" <?php echo ($filterCourse == 'BCA') ? 'selected' : ''; ?>>BCA</option>
            <option value="BBA" <?php echo ($filterCourse == 'BBA') ? 'selected' : ''; ?>>BBA</option>
            <option value="BCom" <?php echo ($filterCourse == 'BCom') ? 'selected' : ''; ?>>BCom</option>
            <option value="BA Journalism" <?php echo ($filterCourse == 'BA Journalism') ? 'selected' : ''; ?>>BA Journalism</option>
            <option value="BSc Biotechnology" <?php echo ($filterCourse == 'BSc Biotechnology') ? 'selected' : ''; ?>>BSc Biotechnology</option>
            <option value="MBA" <?php echo ($filterCourse == 'MBA') ? 'selected' : ''; ?>>MBA</option>
            <option value="MCA" <?php echo ($filterCourse == 'MCA') ? 'selected' : ''; ?>>MCA</option>
            <option value="MSc Data Science" <?php echo ($filterCourse == 'MSc Data Science') ? 'selected' : ''; ?>>MSc Data Science</option>
        </select>
        
        <select name="semester" id="semester">
            <option value="">Select Semester</option>
            <option value="1" <?php echo ($filterSemester == '1') ? 'selected' : ''; ?>>1</option>
            <option value="2" <?php echo ($filterSemester == '2') ? 'selected' : ''; ?>>2</option>
            <option value="3" <?php echo ($filterSemester == '3') ? 'selected' : ''; ?>>3</option>
            <option value="4" <?php echo ($filterSemester == '4') ? 'selected' : ''; ?>>4</option>
            <option value="5" <?php echo ($filterSemester == '5') ? 'selected' : ''; ?>>5</option>
            <option value="6" <?php echo ($filterSemester == '6') ? 'selected' : ''; ?>>6</option>
        </select>
        
        <button type="submit">Filter Books</button>
        <a href="buy_books.php" class="btn btn-secondary">Reset</a>
    </form>
    
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="book-item">
                <div>
                    <p><strong>Title:</strong> <?php echo htmlspecialchars($row['title']); ?></p>
                    <p><strong>Author:</strong> <?php echo htmlspecialchars($row['owner_name']); ?></p>
                    <p><strong>Price:</strong> $<?php echo htmlspecialchars($row['price']); ?></p>
                    <p><strong>Course:</strong> <?php echo htmlspecialchars($row['course']); ?></p>
                    <p><strong>Semester:</strong> <?php echo htmlspecialchars($row['semester']); ?></p>
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
    <?php else: ?>
        <p>No books available matching your filter criteria.</p>
    <?php endif; ?>
</div>

</body>
</html>