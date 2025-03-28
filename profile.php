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
            background-color: #f5f7fa;
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
       
        /* Enhanced profile container - IMPROVED STYLING */
        .profile-container {
            margin: 120px auto 60px;
            padding: 40px;
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            width: 92%;
            max-width: 650px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
        }

        .profile-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(to right, #4CAF50, #2e7d32);
        }

        .profile-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.12);
            transform: translateY(-5px);
        }

        h1 {
            font-size: 32px;
            margin-bottom: 35px;
            color: #2e7d32;
            text-align: center;
            font-weight: 600;
            position: relative;
            padding-bottom: 15px;
        }

        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: linear-gradient(to right, #4CAF50, #2e7d32);
            border-radius: 3px;
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
            gap: 20px;
        }

        form input {
            padding: 14px 18px;
            margin: 0;
            width: 100%;
            border-radius: 10px;
            border: 1.5px solid #e0e0e0;
            box-sizing: border-box;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: #f9f9f9;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.05);
        }

        form input:disabled {
            background-color: #f5f5f5;
            color: #666;
            cursor: not-allowed;
            border-color: #ddd;
        }

        form input:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.2);
            background-color: #fff;
        }

        .button-container {
            display: flex;
            justify-content: center;
            margin-top: 35px;
            gap: 15px;
        }

        button {
            padding: 14px 30px;
            font-size: 16px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        button::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(to right, rgba(255,255,255,0.2), transparent);
            transition: all 0.4s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.15);
        }

        button:hover::after {
            left: 100%;
        }

        #editBtn {
            background: linear-gradient(to right, #2196F3, #1976D2);
            color: white;
        }

        #saveBtn {
            display: none;
            background: linear-gradient(to right, #4CAF50, #2e7d32);
            color: white;
        }

        #editBtn:hover {
            background: linear-gradient(to right, #1E88E5, #1565C0);
        }

        #saveBtn:hover {
            background: linear-gradient(to right, #43A047, #2E7D32);
        }

        /* Form field styling - IMPROVED */
        .form-group {
            position: relative;
            margin-bottom: 5px;
            transition: all 0.3s ease;
        }

        .form-group:hover {
            transform: translateX(5px);
        }

        .form-field-label {
            display: block;
            text-align: left;
            margin-bottom: 10px;
            color: #444;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group:hover .form-field-label {
            color: #2e7d32;
        }
        
        /* Mobile menu button - kept intact */
        .menu-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 21px;
            cursor: pointer;
        }
        
        .menu-toggle span {
            display: block;
            height: 3px;
            width: 100%;
            background-color: white;
            border-radius: 3px;
            transition: all 0.3s ease;
        }
        
        /* Responsive navbar adjustments - kept intact */
        @media (max-width: 768px) {
            .navbar {
                padding: 15px;
            }
            
            .menu-toggle {
                display: flex;
                z-index: 1001;
            }
            
            .menu-toggle.active span:nth-child(1) {
                transform: translateY(9px) rotate(45deg);
            }
            
            .menu-toggle.active span:nth-child(2) {
                opacity: 0;
            }
            
            .menu-toggle.active span:nth-child(3) {
                transform: translateY(-9px) rotate(-45deg);
            }
            
            .nav-links {
                position: fixed;
                top: 0;
                right: -100%;
                width: 250px;
                height: 100vh;
                background: #2e7d32;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 25px;
                transition: right 0.3s ease;
                z-index: 1000;
                padding: 60px 0;
                box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
            }
            
            .nav-links.active {
                right: 0;
            }
            
            .nav-links a {
                opacity: 0;
                transform: translateX(20px);
                transition: opacity 0.3s ease, transform 0.3s ease;
            }
            
            .nav-links.active a {
                opacity: 1;
                transform: translateX(0);
            }
            
            /* Add animation delay for each nav item */
            .nav-links a:nth-child(1) { transition-delay: 0.1s; }
            .nav-links a:nth-child(2) { transition-delay: 0.2s; }
            .nav-links a:nth-child(3) { transition-delay: 0.3s; }
            .nav-links a:nth-child(4) { transition-delay: 0.4s; }
            .nav-links a:nth-child(5) { transition-delay: 0.5s; }
            .nav-links a:nth-child(6) { transition-delay: 0.6s; }
            
            /* Overlay when menu is open */
            .menu-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.5);
                z-index: 999;
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.3s ease, visibility 0.3s ease;
            }
            
            .menu-overlay.active {
                opacity: 1;
                visibility: visible;
            }

            /* Adjust profile container for mobile */
            .profile-container {
                margin: 100px auto 40px;
                padding: 25px 20px;
                width: 90%;
            }

            form input {
                padding: 12px 15px;
            }

            button {
                padding: 12px 25px;
            }
        }
        
        /* Fix profile dropdown for mobile - kept intact */
        @media (max-width: 768px) {
            .profile-dropdown-content {
                right: 0;
                top: 40px;
            }
        }
    </style>
</head>
<body>
<!-- Update your navbar div to include the hamburger menu -->
<div class="navbar">
    <a href="dashboard.php" class="logo">📚 Kind Kart</a>
    
    <div class="menu-toggle" id="mobile-menu">
        <span></span>
        <span></span>
        <span></span>
    </div>
    
    <div class="nav-links" id="nav-links">
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

<!-- Add this overlay div right after the navbar -->
<div class="menu-overlay" id="menu-overlay"></div>


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
    // Add this to your existing JavaScript at the bottom of your file
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.getElementById('nav-links');
    const menuOverlay = document.getElementById('menu-overlay');
    
    // Toggle mobile menu
    mobileMenu.addEventListener('click', function() {
        this.classList.toggle('active');
        navLinks.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
    });
    
    // Close menu when clicking on overlay
    menuOverlay.addEventListener('click', function() {
        mobileMenu.classList.remove('active');
        navLinks.classList.remove('active');
        menuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Close menu when clicking a link
    const menuLinks = navLinks.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            mobileMenu.classList.remove('active');
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });
    });
    
    // Close menu when window resizes to desktop size
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
            mobileMenu.classList.remove('active');
            navLinks.classList.remove('active');
            menuOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    });
});
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