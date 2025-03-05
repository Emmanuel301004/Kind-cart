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

// Ensure the 'status' column exists in the books table
$conn->query("ALTER TABLE books ADD COLUMN status ENUM('Available', 'Sold', 'Reserved') NOT NULL DEFAULT 'Available'");

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$alertMessage = ''; // Variable for alerts
// Define onSuccess function before calling it
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
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $owner_name = $_POST['owner_name'];
    $contact = $_POST['contact'];
    $course = $_POST['course'];
    $semester = $_POST['semester'];
    $book_condition = $_POST['book_condition'];
    $price_type = $_POST['price_type'];
    $price = $price_type == 'paid' ? $_POST['price'] : 0; // If free, price is 0

    // Validate price for "Paid" option
    if ($price_type == 'paid' && (!is_numeric($price) || $price <= 0)) {
        $alertMessage = "Invalid price! Please enter a valid amount greater than 0.";
    } else {
        // Generate a unique book_id
        $book_id = 'BOOK-' . uniqid();

        $sql = "INSERT INTO books (book_id, title, owner_name, contact, course, semester, book_condition, price, user_id, status) 
                VALUES ('$book_id', '$title', '$owner_name', '$contact', '$course', '$semester', '$book_condition', '$price', '$user_id', 'Available')";
        if ($conn->query($sql) === TRUE) {
            $alertMessage = "Book listed successfully!";
            $alertType = "success";
            onSuccess();
        } else {
            $alertMessage = "Error: " . $conn->error;
            $alertType = "danger";
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
    <title>Sell Books</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2e7d32;
            --dark-green: #1b5e20;
            --light-green: #c8e6c9;
            --background-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-gray);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
      
        .sell-form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            flex-grow: 1;
        }

        .sell-form {
            width: 100%;
            max-width: 500px;
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .sell-form:hover {
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            transform: translateY(-5px);
        }

        .sell-form h1 {
            color: var(--primary-green);
            text-align: center;
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        .form-floating {
            margin-bottom: 1rem;
        }

        .form-floating input, 
        .form-floating select {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-floating input:focus, 
        .form-floating select:focus {
            border-color: var(--primary-green);
            box-shadow: 0 0 0 0.25rem rgba(46, 125, 50, 0.25);
        }

        .form-floating label {
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .price-type-container {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 1rem;
        }

        .radio-container {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-submit {
            width: 100%;
            padding: 0.75rem;
            background-color: var(--primary-green);
            border: none;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            background-color: var(--dark-green);
            transform: scale(1.02);
        }

        @media (max-width: 768px) {
            .sell-form {
                padding: 1.5rem;
                max-width: 95%;
            }
        }
    </style>
    <script>
        function togglePriceField() {
            const priceType = document.querySelector('input[name="price_type"]:checked').value;
            const priceField = document.getElementById('price');
            if (priceType === 'paid') {
                priceField.disabled = false;
                priceField.required = true;
            } else {
                priceField.disabled = true;
                priceField.required = false;
                priceField.value = '';
            }
        }
    </script>
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

    <div class="sell-form-container">
        <div class="sell-form">
            <h1>Sell Your Books</h1>
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="title" name="title" placeholder="Book Title" required>
                    <label for="title">Book Title</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="owner_name" name="owner_name" placeholder="Your Name" required>
                    <label for="owner_name">Your Name</label>
                </div>

                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="contact" name="contact" placeholder="Contact Number" required>
                    <label for="contact">Contact Number</label>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select" id="course" name="course" required>
                        <option value="">Select Course</option>
                        <option value="BSc CSMM">BSc CSMM</option>
                        <option value="BCA">BCA</option>
                        <option value="BBA">BBA</option>
                        <option value="BCom">BCom</option>
                        <option value="BA Journalism">BA Journalism</option>
                        <option value="BSc Biotechnology">BSc Biotechnology</option>
                        <option value="MBA">MBA</option>
                        <option value="MCA">MCA</option>
                        <option value="MSc Data Science">MSc Data Science</option>
                    </select>
                    <label for="course">Course</label>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select" id="semester" name="semester" required>
                        <option value="">Select Semester</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                    </select>
                    <label for="semester">Semester</label>
                </div>

                <div class="form-floating mb-3">
                    <select class="form-select" id="book_condition" name="book_condition" required>
                        <option value="">Select Book Condition</option>
                        <option value="New">New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                    <label for="book_condition">Book Condition</label>
                </div>

                <div class="price-type-container">
                    <div class="radio-container">
                        <input type="radio" class="btn-check" name="price_type" id="paid" value="paid" onchange="togglePriceField()" autocomplete="off">
                        <label class="btn btn-outline-success" for="paid">Paid</label>
                    </div>
                    <div class="radio-container">
                        <input type="radio" class="btn-check" name="price_type" id="free" value="free" onchange="togglePriceField()" autocomplete="off">
                        <label class="btn btn-outline-success" for="free">Free</label>
                    </div>
                </div>

                <div class="form-floating mb-3">
                    <input type="number" class="form-control" id="price" name="price" placeholder="Price" min="1" disabled>
                    <label for="price">Price</label>
                </div>

                <button type="submit" class="btn btn-submit">List Book</button>
            </form>
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
</body>
</html>