<?php
session_start();
include 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetching user data from the database
$sql = "SELECT name, email, course, semester FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $email, $course, $semester);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
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

        form input {
            padding: 10px;
            margin: 5px 0;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
        }

        form button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #editBtn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }

        #saveBtn {
            display: none;
            margin-top: 20px;
        }
    </style>
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

<!-- Profile Content -->
<div class="profile-container">
    <h1>Your Profile</h1>
    <form action="update_profile.php" method="post" id="profileForm">
        <p class="info"><strong>Name:</strong></p>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" disabled>
        
        <p class="info"><strong>Email:</strong></p>
        <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
        
        <p class="info"><strong>Course:</strong></p>
        <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" disabled>
        
        <p class="info"><strong>Semester:</strong></p>
        <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($semester); ?>" disabled>

        <button type="submit" id="saveBtn">Save Changes</button>
    </form>
    <button id="editBtn" onclick="enableEdit()">Edit Profile</button>
</div>

<script>
    function enableEdit() {
        // Enable all input fields
        document.getElementById("name").disabled = false;
        document.getElementById("course").disabled = false;
        document.getElementById("semester").disabled = false;
        document.getElementById("saveBtn").style.display = "inline-block"; // Show Save button
        document.getElementById("editBtn").style.display = "none"; // Hide Edit button
    }
</script>

</body>
</html>
