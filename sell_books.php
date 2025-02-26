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
            text-transform: uppercase;
        }


/* Form Container */
.sell-form {
    width: 45vw;
    background: #ffffff;
    padding: 25px;
    
    
    
    height: 89vh;
}

/* Form Title */
.sell-form h1 {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

/* Labels */
.sell-form label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #555;
    text-align: left;
    margin-bottom: 6px;
}

/* Input & Select Fields */
.sell-form input, 
.sell-form select {
    width: 80%;
    padding: 12px;
    margin-top: 6px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 6px;
    font-size: 14px;
    background: #fafafa;
    transition: all 0.3s ease;
}


/* Price Type - Aligning Radio Buttons */
.price-type-container {
    display: flex;
    
    
    margin: 15px 3P;
}

.price-type-container label {
    display: flex;
    
    
    cursor: pointer;
}

/* Styled Radio Buttons */
.sell-form input[type="radio"] {
    accent-color: #2c3e50;
    transform: scale(1.2);
}

/* Submit Button */
.sell-form button {
    width: 50%;
    padding: 12px;
    background: #2c3e50;
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

/* Button Hover Effect */
.sell-form button:hover {
    background: #1a252f;
    transform: scale(1.02);
}
/* Price Type - Aligning Radio Buttons Properly */
.price-type-container {
    display: flex;
    
    gap: -50px; /* Adjust spacing between radio buttons */
    margin-bottom: 15px;
    
}

.price-type-container label {
    display: flex;
    
    gap: 2px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    margin: 0; /* Remove extra margin */
}

/* Styled Radio Buttons */
.sell-form input[type="radio"] {
    accent-color: #2c3e50;
    transform: scale(1.1);
    margin-right: 1px; /* Reduce spacing */
}

#price-free{
    margin-right: 10px; /* Add spacing */
}
/* Responsive Design */
@media (max-width: 600px) {
    .sell-form {
        width: 90%;
        padding: 20px;
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
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="cart.php">Cart</a>
        <a href="order_history.php">Orders</a>
        <a href="profile.php">Profile</a>
    </div>
    <a href="logout.php" class="logout">Logout</a>
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
