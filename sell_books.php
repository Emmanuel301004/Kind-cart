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
        // Insert book details into the database
        $sql = "INSERT INTO books (title, owner_name, contact, course, semester, book_condition, price, user_id) 
                VALUES ('$title', '$owner_name', '$contact', '$course', '$semester', '$book_condition', '$price', '$user_id')";

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
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f9fafb;
            margin: 0;
            padding: 0;
        }

        h1 {
            font-family: 'Arial', sans-serif;
            color: #333;
            font-size: 2rem;
        }

        a {
            text-decoration: none;
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

.navbar .logout:hover {
    background-color: #c0392b;
}


        /* Form Container */
        .sell-form {
            max-width: 600px;
            margin: 40px auto;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .sell-form h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        .sell-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #333;
        }

        .sell-form input, 
        .sell-form select, 
        .sell-form button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        .sell-form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .sell-form button:hover {
            background-color: #45a049;
        }

        .sell-form .price-options {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar div {
                flex-direction: column;
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
    <div class="nav-links">
        <a href="dashboard.php">Home</a>
        <a href="buy_books.php">Buy Books</a>
        <a href="sell_books.php">Sell Books</a>
        <a href="cart.php">Cart</a>
        <a href="orders.php">Orders</a>
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
        <input type="text" name="course" id="course" required>

        <label for="semester">Semester:</label>
        <input type="number" name="semester" id="semester" min="1" max="8" required>

        <label for="book_condition">Condition of the Book:</label>
        <select name="book_condition" id="book_condition" required>
            <option value="New">New</option>
            <option value="Good">Good</option>
            <option value="Fair">Fair</option>
            <option value="Poor">Poor</option>
        </select>

        <label>Price Type:</label>
        <div class="price-options">
            <div>
                <input type="radio" name="price_type" value="free" id="price_free" onclick="togglePriceField()" required>
                <label for="price_free">Free</label>
            </div>
            <div>
                <input type="radio" name="price_type" value="paid" id="price_paid" onclick="togglePriceField()">
                <label for="price_paid">Paid</label>
            </div>
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
<script>
document.querySelector("form").addEventListener("submit", function(event) {
    const priceType = document.querySelector('input[name="price_type"]:checked').value;
    const price = document.getElementById("price").value;

    if (priceType === "paid" && (isNaN(price) || price <= 0)) {
        event.preventDefault();
        alert("Please enter a valid price greater than 0.");
    }
});
</script>
