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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $owner_name = $_POST['owner_name'];
    $contact = $_POST['contact'];
    $course = $_POST['course'];
    $semester = $_POST['semester'];
    $book_condition = $_POST['book_condition'];
    $price_type = $_POST['price_type'];
    $price = $price_type == 'paid' ? $_POST['price'] : 0; // If free, price is 0
    $status = 'Available'; // Default status

    // Validate price for "Paid" option
    if ($price_type == 'paid' && (!is_numeric($price) || $price <= 0)) {
        $alertMessage = "Invalid price! Please enter a valid amount greater than 0.";
    } else {
        // Generate a unique book_id
        $book_id = 'BOOK-' . uniqid();

        $sql = "INSERT INTO books (book_id, title, owner_name, contact, course, semester, book_condition, price, user_id, status) 
                VALUES ('$book_id', '$title', '$owner_name', '$contact', '$course', '$semester', '$book_condition', '$price', '$user_id', '$status')";

        if ($conn->query($sql) === TRUE) {
            $alertMessage = "Book listed successfully!";
        } else {
            $alertMessage = "Error: " . $conn->error;
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
    <style>
        /* Global Styling */
body {
    font-family: 'Poppins', sans-serif;
    background-color: #f8f9fa; /* Light Gray Background */
    
    height: 100vh;
    margin: 0;
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
/* Form Container */
/* Sell Form Styling */
.sell-form {
    width: 50%;
    background: #ffffff;
    padding: 25px;
    margin: 100px auto; /* Centering & Spacing */
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
}

/* Title */
.sell-form h1 {
    font-size: 24px;
    font-weight: 600;
    color: #2e7d32; /* Navbar Green */
    text-align: center;
    margin-bottom: 20px;
}

/* Labels */
.sell-form label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 6px;
}

/* Inputs & Select Fields */
.sell-form input, 
.sell-form select {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background: #f9f9f9;
    transition: all 0.3s ease;
}

/* Focus effect */
.sell-form input:focus, 
.sell-form select:focus {
    border-color: #2e7d32;
    outline: none;
    box-shadow: 0 0 5px rgba(46, 125, 50, 0.5);
}

/* Price Type Radio */
.price-type-container {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

/* Radio Button Styling */
.sell-form input[type="radio"] {
    accent-color: #2e7d32;
    transform: scale(1.2);
    margin-right: 5px;
}
/* Ensure it doesn't show any focus styles */
.sell-form input[type="radio"]:focus,
.sell-form input[type="radio"]:focus-visible {
    outline: none;
    box-shadow: none;
}

/* Submit Button */
.sell-form button {
    width: 100%;
    padding: 12px;
    background: #2e7d32; /* Same as Navbar */
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Hover Effect */
.sell-form button:hover {
    background: #1b5e20;
    transform: scale(1.02);
}

/* Responsive Design */
@media (max-width: 768px) {
    .sell-form {
        width: 90%;
    }
}

    </style>
<link rel="stylesheet" href="style.css">
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

<div class="sell-form">
    <h1>Sell Your Books</h1>
    <form method="POST">
        <label for="title">Book Title:</label>
        <input type="text" name="title" id="title" required>

        <label for="owner_name">Your Name:</label>
        <input type="text" name="owner_name" id="owner_name" required>

        <label for="contact">Contact Number:</label>
        <input type="text" name="contact" id="contact" required>

        <label for="course">Course:</label>
        <select name="course" id="course" required>
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
        <label for="semester">Semester:</label>
        <select name="semester" id="semester" required>
            <option value="1">1</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="6">6</option>
        </select>

        <label for="book_condition">Condition of the Book:</label>
        <select name="book_condition" id="book_condition" required>
            <option value="New">New</option>
            <option value="Good">Good</option>
            <option value="Fair">Fair</option>
            <option value="Poor">Poor</option>
        </select>

        <label>Price Type:</label>
        <div class="price-type-container">
            <input type="radio" name="price_type" value="free" id="price_free" onclick="togglePriceField()" required>
            <label for="price_free">Free</label>
            <input type="radio" name="price_type" value="paid" id="price_paid" onclick="togglePriceField()">
            <label for="price_paid">Paid</label>
        </div>

        <label for="price">Price:</label>
        <input type="number" name="price" id="price" min="1" disabled>

        <button type="submit">List Book</button>
    </form>
</div>

<?php if (!empty($alertMessage)): ?>
    <script>
        alert("<?php echo $alertMessage; ?>");
    </script>
<?php endif; ?>

</body>
</html>
