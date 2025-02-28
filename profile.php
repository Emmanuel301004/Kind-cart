<?php
session_start();
include 'db.php'; // Database connection file
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("No session user_id found."); // Log an error if user_id is not found in the session
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
if (!$stmt->fetch()) {
    error_log("No data found for user ID: $user_id"); // Log if no data is fetched
    $user_name = $email = $course = $semester = ''; // Set default empty values
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
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
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
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
