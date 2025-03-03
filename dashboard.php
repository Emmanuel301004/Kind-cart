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

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}
$user_id = $_SESSION['user_id'];

// Fetch all books from the database
$sql = "SELECT * FROM books WHERE status = 'Available'";
$result = $conn->query($sql);

// Fetch Recently Added Books (latest 4 entries)
$recentBooksQuery = "SELECT * FROM books WHERE status = 'Available' ORDER BY created_at DESC LIMIT 7";
$recentBooks = $conn->query($recentBooksQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kind Kart - Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
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
        .hero {
            text-align: center;
            padding: 120px 20px 80px;
            background: linear-gradient(135deg, #81c784 0%, #2e7d32 100%);
            color: white;
            margin-top: 60px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 20px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        .hero p {
            font-size: 1.3rem;
            max-width: 800px;
            margin: 0 auto;
            line-height: 1.8;
        }
        .about {
            padding: 70px 20px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        .about h2 {
            font-size: 2.2rem;
            color: #2e7d32;
            margin-bottom: 30px;
            position: relative;
            display: inline-block;
        }
        .about h2:after {
            content: '';
            display: block;
            width: 70px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .about p {
            font-size: 1.2rem;
            max-width: 800px;
            margin: auto;
            line-height: 1.8;
            color: #555;
        }

        /* Statistics Section */
        .statistics {
            padding: 70px 20px;
            background-color: #e8f5e9;
            text-align: center;
        }
        .statistics h2 {
            font-size: 2.2rem;
            color: #2e7d32;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
        }
        .statistics h2:after {
            content: '';
            display: block;
            width: 70px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stat-item {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.1);
            width: 200px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-item:hover {
            transform: translateY(-10px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 10px;
        }
        .stat-title {
            font-size: 1.1rem;
            color: #555;
        }
        
        /* Books Section */
        .books-section {
            padding: 70px 20px;
            text-align: center;
            background: white;
        }
        .books-section h2 {
            font-size: 2.2rem;
            color: #2e7d32;
            margin-bottom: 40px;
            position: relative;
            display: inline-block;
        }
        .books-section h2:after {
            content: '';
            display: block;
            width: 70px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .books-container {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
            justify-content: center;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .book-card {
            background: white;
            padding: 25px;
            width: 220px;
            border-radius: 16px;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #f0f0f0;
        }
        .book-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
        }
        .book-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 6px;
            background: linear-gradient(90deg, #81c784, #2e7d32);
        }
        .book-card h3 {
            color: #0b6623;
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .book-card p {
            font-size: 1rem;
            color: #555;
            margin: 8px 0;
        }
        .more-btn {
            display: inline-block;
            margin: 40px auto 0;
            padding: 14px 28px;
            background: #2e7d32;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 12px rgba(46, 125, 50, 0.3);
            border: none;
            cursor: pointer;
        }
        .more-btn:hover {
            background: #1b5e20;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 125, 50, 0.4);
        }
        .footer {
            background: #2e7d32;
            color: white;
            text-align: center;
            padding: 30px 20px;
            margin-top: 20px;
        }
        .footer p {
            font-size: 1rem;
            margin-bottom: 15px;
        }
        .footer a {
            color: #c8e6c9;
            text-decoration: none;
            margin: 0 10px;
            transition: color 0.3s;
        }
        .footer a:hover {
            color: white;
            text-decoration: underline;
        }
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 2.2rem;
            }
            .hero p, .about p {
                font-size: 1.1rem;
            }
            .stat-item {
                width: calc(50% - 30px);
                padding: 20px;
            }
            .book-card {
                width: calc(50% - 30px);
            }
        }
        @media (max-width: 576px) {
            .nav-links {
                display: none;
            }
            .stat-item, .book-card {
                width: 100%;
            }
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
  
  <!-- Hero Section -->   
<div class="hero">         
    <h1>Welcome to Kind Kart</h1>         
    <p>Connecting juniors and seniors for buying and selling study materials affordably. Our platform bridges the educational gap by creating a sustainable marketplace for academic resources.</p>     
</div>      

<!-- About Section -->     
<div class="about">         
    <h2>About Kind Kart</h2>         
    <p>             
        Kind Kart is a student-friendly platform designed to help juniors buy study materials at lower costs while allowing seniors to sell their old books and notes. This initiative promotes affordability, sustainability, and a helpful community among students. We believe in reducing waste while making education more accessible to everyone. Our mission is to create a circular economy within educational institutions, where knowledge resources are shared efficiently and economically. By connecting students directly, we eliminate middlemen and ensure that quality study materials remain within reach for all learners regardless of their financial background.         
    </p>     
</div>

   <!-- Statistics Section -->
   <div class="statistics">
        <h2>Our Impact</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number" id="stat1">0</div>
                <div class="stat-title">Active Users</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-number" id="stat2">0</div>
                <div class="stat-title">Books Exchanged</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-number" id="stat3">0</div>
                <div class="stat-title">Institutions</div>
            </div>
            
            <div class="stat-item">
                <div class="stat-number" id="stat4">0</div>
                <div class="stat-title">Course Subjects</div>
            </div>
        </div>
    </div>
    <!-- Recently Added Books Section -->
    <div class="books-section">
        <h2>Recently Added Books</h2>
        <div class="books-container">
            <?php while ($book = $recentBooks->fetch_assoc()): ?>
                <div class="book-card">
                    <h3><?= htmlspecialchars($book['title']) ?></h3>
                    <p><strong>Owner:</strong> <?= htmlspecialchars($book['owner_name']) ?></p>
                    <p><strong>Price:</strong> ₹<?= htmlspecialchars($book['price']) ?></p>
                </div>
            <?php endwhile; ?>
        </div>
        <a href="buy_books.php" class="more-btn">View More Books</a>
    </div>
    
    <!-- Footer Section -->
    <div class="footer">
        <p>© 2025 Kind Kart - Connecting Students Through Knowledge Sharing</p>
        <div>
            <a href="contact.php">Contact Us</a> | 
            <a href="privacy.php">Privacy Policy</a> | 
            <a href="terms.php">Terms of Service</a> | 
            <a href="faq.php">FAQs</a>
        </div>
    </div>

    <!-- JavaScript for Counter Animation -->
    <script>
        // Function to animate counting up
        function animateCounter(elementId, finalValue, duration) {
            const element = document.getElementById(elementId);
            let startTime = null;
            const step = timestamp => {
                if (!startTime) startTime = timestamp;
                const progress = Math.min((timestamp - startTime) / duration, 1);
                
                // For values with K+ suffix
                if (finalValue.toString().includes('K')) {
                    const numericValue = parseFloat(finalValue);
                    const currentValue = Math.floor(progress * numericValue);
                    element.textContent = currentValue + 'K+';
                } else {
                    const numericValue = parseInt(finalValue);
                    const currentValue = Math.floor(progress * numericValue);
                    element.textContent = currentValue + '+';
                }
                
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Function to check if element is in viewport
        function isInViewport(element) {
            const rect = element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        }

        // Start animation when scrolled into view
        function handleScroll() {
            const statisticsSection = document.querySelector('.statistics');
            if (isInViewport(statisticsSection)) {
                // Start animations
                animateCounter('stat1', '15K', 2000); // 2000ms = 2 seconds duration
                animateCounter('stat2', '50K', 2000);
                animateCounter('stat3', '100', 2000);
                animateCounter('stat4', '250', 2000);
                
                // Remove scroll listener once animated
                window.removeEventListener('scroll', handleScroll);
            }
        }

        // Add scroll event listener
        window.addEventListener('scroll', handleScroll);
        
        // Also check once on page load (in case statistics are already in view)
        document.addEventListener('DOMContentLoaded', handleScroll);
    </script>


</body>
</html>
