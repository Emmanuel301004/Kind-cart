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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        /* Navbar styles - kept intact */
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
       
        /* Enhanced profile container */
        .profile-container {
            margin: 100px auto 50px;
            padding: 40px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 600px;
            transition: all 0.3s ease;
        }

        .profile-container:hover {
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
        }

        h1 {
            font-size: 32px;
            margin-bottom: 30px;
            color: #2e7d32;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 15px;
        }

        .info {
            font-size: 16px;
            margin-bottom: 5px;
            color: #555;
            text-align: left;
            font-weight: bold;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        form input {
            padding: 12px 15px;
            margin: 0;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #ddd;
            box-sizing: border-box;
            font-size: 16px;
            transition: border 0.3s ease, box-shadow 0.3s ease;
            background-color: #f9f9f9;
        }

        form input:disabled {
            background-color: #f0f0f0;
            color: #777;
            cursor: not-allowed;
        }

        form input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 25px;
        }

        button {
            padding: 12px 25px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        #editBtn {
            background-color: #2196F3;
            color: white;
        }

        #saveBtn {
            display: none;
            background-color: #4CAF50;
            color: white;
        }

        #editBtn:hover {
            background-color: #0d8bf2;
        }

        #saveBtn:hover {
            background-color: #3d9140;
        }

        /* Form field styling */
        .form-group {
            position: relative;
            margin-bottom: 20px;
        }

        .form-field-label {
            display: block;
            text-align: left;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
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


<div class="profile-container">
    <h1>Your Profile</h1>
    <form action="update_profile.php" method="post" id="profileForm">
        <div class="form-group">
            <label class="form-field-label" for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_name); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label class="form-field-label" for="email">Email</label>
            <input type="text" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label class="form-field-label" for="course">Course</label>
            <input type="text" id="course" name="course" value="<?php echo htmlspecialchars($course); ?>" disabled>
        </div>
        
        <div class="form-group">
            <label class="form-field-label" for="semester">Semester</label>
            <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($semester); ?>" disabled>
        </div>

        <div class="button-container">
            <button type="button" id="editBtn" onclick="enableEdit()">Edit Profile</button>
            <button type="submit" id="saveBtn">Save Changes</button>
        </div>
    </form>
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