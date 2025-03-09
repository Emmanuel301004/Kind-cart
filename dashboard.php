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
// Function to get active users count
function getActiveUsersCount($conn) {
    $usersQuery = "SELECT COUNT(*) as active_users FROM users";
    $usersResult = $conn->query($usersQuery);
    return $usersResult->fetch_assoc()['active_users'];
}

// Function to get books exchanged count
function getBooksExchangedCount($conn) {
    $booksQuery = "SELECT COUNT(*) as books_exchanged FROM orders";
    $booksResult = $conn->query($booksQuery);
    return $booksResult->fetch_assoc()['books_exchanged'];
}

// Get the counts
$activeUsersCount = getActiveUsersCount($conn);
$booksExchangedCount = getBooksExchangedCount($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kind Kart - Dashboard</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
            color: #333;
            line-height: 1.6;
        }
        /* Keeping navbar styles intact as requested */
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
        
        /* Enhanced Hero Section */
        .hero {
            text-align: center;
            padding: 150px 20px 100px;
            background: linear-gradient(135deg, #81c784 0%, #2e7d32 100%);
            color: white;
            margin-top: 60px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }
        .hero::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" width="100" height="100" opacity="0.1"><text x="10" y="50" font-family="Arial" font-size="30" fill="white">📚</text></svg>');
            opacity: 0.1;
        }
        .hero h1 {
            font-size: 3.5rem;
            margin-bottom: 25px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
            font-weight: 700;
            letter-spacing: 1px;
        }
        .hero p {
            font-size: 1.4rem;
            max-width: 800px;
            margin: 0 auto 30px;
            line-height: 1.8;
            font-weight: 300;
            letter-spacing: 0.5px;
        }
        .hero-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }
        .hero-btn {
            padding: 12px 28px;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .hero-btn-primary {
            background-color: white;
            color: #2e7d32;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .hero-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }
        .hero-btn-secondary {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }
        .hero-btn-secondary:hover {
            background-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
        }
        
        /* Enhanced About Section */
        .about {
            padding: 90px 30px;
            text-align: center;
            background: white;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            border-radius: 0 0 10px 10px;
            margin: 0 15px 50px;
            z-index: 1;
        }
        .about h2 {
            font-size: 2.4rem;
            color: #2e7d32;
            margin-bottom: 35px;
            position: relative;
            display: inline-block;
            font-weight: 600;
        }
        .about h2:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .about p {
            font-size: 1.2rem;
            max-width: 900px;
            margin: auto;
            line-height: 1.9;
            color: #555;
            font-weight: 300;
        }
        .features {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 50px;
        }
        .feature-card {
            background: #f9f9f9;
            border-radius: 12px;
            padding: 25px;
            width: 280px;
            text-align: left;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #81c784 0%, #2e7d32 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            color: white;
            font-size: 24px;
        }
        .feature-card h3 {
            color: #2e7d32;
            font-size: 1.3rem;
            margin-bottom: 15px;
        }
        .feature-card p {
            font-size: 1rem;
            color: #666;
            line-height: 1.6;
            text-align: left;
        }

        /* Enhanced Statistics Section */
        .statistics {
            padding: 90px 30px;
            background-color: #e8f5e9;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .statistics::after {
            content: "";
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(129, 199, 132, 0.1);
            border-radius: 50%;
        }
        .statistics h2 {
            font-size: 2.4rem;
            color: #2e7d32;
            margin-bottom: 50px;
            position: relative;
            display: inline-block;
            font-weight: 600;
        }
        .statistics h2:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .stats-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
        }
        .stat-item {
            background: white;
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            width: 220px;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            overflow: hidden;
        }
        .stat-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #81c784, #2e7d32);
        }
        .stat-item:hover {
            transform: translateY(-15px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.12);
        }
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 15px;
            line-height: 1;
        }
        .stat-title {
            font-size: 1.2rem;
            color: #555;
            font-weight: 500;
        }
        
        /* Testimonial Section - NEW */
        .testimonials {
            padding: 90px 30px;
            background: white;
            text-align: center;
            position: relative;
        }
        .testimonials h2 {
            font-size: 2.4rem;
            color: #2e7d32;
            margin-bottom: 50px;
            position: relative;
            display: inline-block;
            font-weight: 600;
        }
        .testimonials h2:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .testimonial-container {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 30px;
            padding: 20px 0;
            max-width: 1000px;
            margin: 0 auto;
            scrollbar-width: none; /* For Firefox */
        }
        .testimonial-container::-webkit-scrollbar {
            display: none; /* For Chrome, Safari, and Opera */
        }
        .testimonial-card {
            scroll-snap-align: center;
            background: #f9f9f9;
            padding: 35px;
            border-radius: 15px;
            min-width: 300px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.05);
            position: relative;
            text-align: left;
        }
        .testimonial-card::before {
            content: """;
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 80px;
            color: rgba(46, 125, 50, 0.1);
            font-family: Georgia, serif;
            line-height: 0;
        }
        .testimonial-text {
            font-style: italic;
            font-size: 1.1rem;
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }
        .testimonial-author {
            display: flex;
            align-items: center;
        }
        .author-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #ddd;
            margin-right: 15px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #2e7d32;
            background: #c8e6c9;
        }
        .author-info {
            flex: 1;
        }
        .author-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        .author-title {
            color: #777;
            font-size: 0.9rem;
        }
        .testimonial-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
        }
        .testimonial-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #ddd;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .testimonial-dot.active {
            background: #2e7d32;
        }
        
        /* Enhanced Books Section */
        .books-section {
            padding: 90px 30px;
            text-align: center;
            background: #f8f9fa;
            position: relative;
        }
        .books-section::before {
            content: "";
            position: absolute;
            top: -50px;
            left: -50px;
            width: 200px;
            height: 200px;
            background-color: rgba(129, 199, 132, 0.1);
            border-radius: 50%;
            z-index: 0;
        }
        .books-section h2 {
            font-size: 2.4rem;
            color: #2e7d32;
            margin-bottom: 50px;
            position: relative;
            display: inline-block;
            font-weight: 600;
            z-index: 1;
        }
        .books-section h2:after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: #2e7d32;
            margin: 15px auto 0;
            border-radius: 2px;
        }
        .books-container {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
            padding: 20px;
            max-width: 1300px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
        }
        .book-card {
            background: white;
            padding: 30px 25px;
            width: 240px;
            border-radius: 18px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            text-align: center;
            transition: transform 0.4s ease, box-shadow 0.4s ease;
            position: relative;
            overflow: hidden;
            border: 1px solid #f0f0f0;
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
        .book-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .book-card h3 {
            color: #0b6623;
            font-size: 1.35rem;
            font-weight: 600;
            margin-bottom: 15px;
            line-height: 1.4;
        }
        .book-card p {
            font-size: 1.05rem;
            color: #555;
            margin: 10px 0;
        }
        .book-card p strong {
            color: #333;
            font-weight: 500;
        }
        .book-card .book-img {
            height: 120px;
            width: 90px;
            margin: 0 auto 20px;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(0,0,0,0.1);
            font-size: 24px;
            color: #2e7d32;
        }
        .more-btn {
            display: inline-block;
            margin: 50px auto 0;
            padding: 16px 32px;
            background: #2e7d32;
            color: #fff;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 50px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 6px 15px rgba(46, 125, 50, 0.3);
            border: none;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }
        .more-btn:after {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.5s ease;
        }
        .more-btn:hover {
            background: #1b5e20;
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.4);
        }
        .more-btn:hover:after {
            left: 100%;
        }
        
        /* Newsletter section - NEW */
        .newsletter {
            background: linear-gradient(135deg, #2e7d32 0%, #1b5e20 100%);
            padding: 70px 30px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .newsletter::before {
            content: "";
            position: absolute;
            top: -20px;
            right: -20px;
            width: 150px;
            height: 150px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }
        .newsletter h2 {
            font-size: 2.2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }
        .newsletter p {
            max-width: 600px;
            margin: 0 auto 30px;
            font-size: 1.1rem;
            opacity: 0.9;
        }
        .newsletter-form {
            display: flex;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 50px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }
        .newsletter-input {
            flex: 1;
            padding: 15px 25px;
            border: none;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
        }
        .newsletter-input:focus {
            outline: none;
        }
        .newsletter-btn {
            background: white;
            color: #2e7d32;
            border: none;
            padding: 0 30px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        .newsletter-btn:hover {
            background: #f0f0f0;
        }
        
        /* Enhanced Footer */
        .footer {
            background: #1b5e20;
            color: white;
            text-align: center;
            padding: 60px 20px 40px;
            position: relative;
        }
        .footer::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background: linear-gradient(90deg, #81c784, #2e7d32);
        }
        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto 40px;
            text-align: left;
        }
        .footer-column {
            flex: 1;
            min-width: 200px;
        }
        .footer-column h3 {
            color: white;
            font-size: 1.3rem;
            margin-bottom: 20px;
            position: relative;
            padding-bottom: 10px;
        }
        .footer-column h3::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: #81c784;
        }
        .footer-column ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .footer-column ul li {
            margin-bottom: 10px;
        }
        .footer-column a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s;
        }
        .footer-column a:hover {
            color: white;
            padding-left: 5px;
        }
        .footer-social {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .social-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        .social-icon:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
        }
        .copyright {
            padding-top: 30px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 0.95rem;
            opacity: 0.8;
        }
        
        /* Back to top button - NEW */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #2e7d32;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
            z-index: 99;
        }
        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
        }
        .back-to-top:hover {
            background: #1b5e20;
            transform: translateY(-5px);
        }
        
        /* Floating action button - NEW */
        .floating-action {
            position: fixed;
            bottom: 30px;
            left: 30px;
            background: #2e7d32;
            color: white;
            padding: 15px 25px;
            border-radius: 50px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            z-index: 99;
        }
        .floating-action:hover {
            background: #1b5e20;
            transform: translateY(-5px);
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
            .about, .statistics, .books-section, .testimonials, .newsletter {
                padding: 60px 20px;
            }
            .hero-buttons {
                flex-direction: column;
                gap: 15px;
            }
            .feature-card {
                width: 100%;
            }
            .footer-content {
                flex-direction: column;
                gap: 30px;
            }
            .newsletter-form {
                flex-direction: column;
                border-radius: 10px;
            }
            .newsletter-input {
                width: 100%;
                border-radius: 10px 10px 0 0;
                text-align: center;
            }
            .newsletter-btn {
                width: 100%;
                padding: 15px;
                border-radius: 0 0 10px 10px;
            }
        }
        @media (max-width: 576px) {
            .nav-links {
                display: none;
            }
            .stat-item, .book-card {
                width: 100%;
            }
            .hero {
                padding: 120px 15px 80px;
            }
            .about, .statistics, .books-section, .testimonials, .newsletter {
                padding: 50px 15px;
            }
            .back-to-top, .floating-action {
                bottom: 15px;
            }
            .back-to-top {
                right: 15px;
            }
            .floating-action {
                left: 15px;
                padding: 10px 15px;
                font-size: 0.9rem;
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
  
    <!-- Hero Section -->   
    <div class="hero">         
        <h1>Welcome to Kind Kart</h1>         
        <p>Connecting juniors and seniors for buying and selling study materials affordably. Our platform bridges the educational gap by creating a sustainable marketplace for academic resources.</p>
        <div class="hero-buttons">
            <a href="buy_books.php" class="hero-btn hero-btn-primary">📚 Browse Books</a>
            <a href="sell_books.php" class="hero-btn hero-btn-secondary">💰 Sell Your Books</a>
        </div>
    </div>
    <!-- About Section -->
    <div class="about">
        <h2>About Kind Kart</h2>
        <p>Kind Kart is a student-run marketplace designed to make educational resources accessible and affordable. Our platform facilitates the exchange of textbooks and study materials between seniors and juniors, promoting sustainability and academic success.</p>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Book Exchange</h3>
                <p>Find second-hand textbooks at affordable prices or sell your used books to students who need them next.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">💰</div>
                <h3>Save Money</h3>
                <p>Get educational materials at a fraction of their original cost, making education more affordable for everyone.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">♻️</div>
                <h3>Sustainability</h3>
                <p>Reduce waste by giving books a second life, contributing to a more sustainable campus environment.</p>
            </div>
        </div>
    </div>
    
    <!-- Statistics Section -->
    <div class="statistics">
        <h2>Our Impact</h2>
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $activeUsersCount; ?></div>
                <div class="stat-title">Active Users</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $result->num_rows; ?></div>
                <div class="stat-title">Available Books</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $booksExchangedCount; ?></div>
                <div class="stat-title">Books Exchanged</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">₹20K+</div>
                <div class="stat-title">Money Saved</div>
            </div>
        </div>
    </div>
    
    <!-- Testimonial Section -->
    <div class="testimonials">
        <h2>What Students Say</h2>
        <div class="testimonial-container">
            <div class="testimonial-card">
                <p class="testimonial-text">Kind Kart helped me find all my engineering textbooks at half the price. The platform is so easy to use and the sellers were really helpful!</p>
                <div class="testimonial-author">
                    <div class="author-avatar">AK</div>
                    <div class="author-info">
                        <div class="author-name">Anika Kumar</div>
                        <div class="author-title">Computer Science, 2nd Year</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">As a final year student, I was able to sell all my books quickly. The process was smooth and I'm happy knowing my books are helping juniors.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">RS</div>
                    <div class="author-info">
                        <div class="author-name">Rahul Singh</div>
                        <div class="author-title">Mechanical Engineering, 4th Year</div>
                    </div>
                </div>
            </div>
            <div class="testimonial-card">
                <p class="testimonial-text">The platform saved me so much money on textbooks! I recommend Kind Kart to all first-year students who are looking to manage their budget.</p>
                <div class="testimonial-author">
                    <div class="author-avatar">PM</div>
                    <div class="author-info">
                        <div class="author-name">Priya Mehta</div>
                        <div class="author-title">Economics, 1st Year</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="testimonial-nav">
            <div class="testimonial-dot active"></div>
            <div class="testimonial-dot"></div>
            <div class="testimonial-dot"></div>
        </div>
    </div>
    
    <!-- Books Section -->
<div class="books-section">
    <h2>Recently Added Books</h2>
    <div class="books-container">
        <?php
        // Display recent books
        if ($recentBooks->num_rows > 0) {
            while($book = $recentBooks->fetch_assoc()) {
                echo '<div class="book-card">';
                echo '<div class="book-img">📘</div>';
                echo '<h3>' . $book['title'] . '</h3>';
                echo '<p><strong>Owner:</strong> ' . $book['owner_name'] . '</p>';
                echo '<p><strong>Price:</strong> ₹' . $book['price'] . '</p>';
                echo '<p><strong>Condition:</strong> ' . $book['book_condition'] . '</p>';
                echo '</div>';
            }
        } else {
            echo "<p>No books available at the moment.</p>";
        }
        ?>
    </div>
    <a href="buy_books.php" class="more-btn">View All Books</a>
</div>
    <!-- Newsletter Section -->
    <div class="newsletter">
        <h2>Stay Updated</h2>
        <p>Subscribe to our newsletter to receive notifications about new books and special offers.</p>
        <form class="newsletter-form">
            <input type="email" placeholder="Enter your email" class="newsletter-input" required>
            <button type="submit" class="newsletter-btn">Subscribe</button>
        </form>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div class="footer-content">
            <div class="footer-column">
                <h3>Kind Kart</h3>
                <p>A student-run marketplace for affordable educational resources, promoting sustainability and academic success.</p>
                <div class="footer-social">
                    <a href="#" class="social-icon">f</a>
                    <a href="#" class="social-icon">t</a>
                    <a href="#" class="social-icon">in</a>
                    <a href="#" class="social-icon">ig</a>
                </div>
            </div>
            <div class="footer-column">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Home</a></li>
                    <li><a href="buy_books.php">Buy Books</a></li>
                    <li><a href="sell_books.php">Sell Books</a></li>
                    <li><a href="my_listings.php">My Listings</a></li>
                    <li><a href="order_history.php">Order History</a></li>
                </ul>
            </div>
            <div class="footer-column">
                <h3>Support</h3>
                <ul>
                    <li><a href="https://drive.google.com/file/d/1I-l9mSVjKIkeA0LJEAzHgnWOjZUXk4sL/view?usp=sharing">FAQs</a></li>
                    <li><a href="https://forms.gle/rCzeChVn1UJEeyxB6">Contact Us</a></li>
                    <li><a href="https://drive.google.com/file/d/1BkQOLC-Gtie9JCsU7GxptP2MEtgJ-pc8/view?usp=sharing">Terms of Service</a></li>
                    <li><a href="https://drive.google.com/file/d/14fumxnqPBuW0mjZdAg0HFk5oYcLkLZJl/view?usp=sharing">Privacy Policy</a></li>
                </ul>
            </div>
        </div>
        <div class="copyright">
            &copy; 2025 Kind Kart. All Rights Reserved.
        </div>
    </div>
    
    <!-- Back to top button -->
    <a href="#" class="back-to-top" id="backToTop">↑</a>
    
    <!-- Floating action button -->
    <a href="sell_books.php" class="floating-action">Sell Your Books</a>
    
    <script>
        // Back to top button functionality
        window.addEventListener('scroll', function() {
            var backToTopBtn = document.getElementById('backToTop');
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.add('visible');
            } else {
                backToTopBtn.classList.remove('visible');
            }
        });
        
        document.getElementById('backToTop').addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({top: 0, behavior: 'smooth'});
        });
        
        testimonialDots.forEach((dot, index) => {
    dot.addEventListener('click', () => {
        // Remove active class from all dots
        testimonialDots.forEach(d => d.classList.remove('active'));
        // Add active class to current dot
        dot.classList.add('active');
        
        // Instead of scrollIntoView, just update the scroll position of the container
        const testimonialContainer = document.querySelector('.testimonial-container');
        const testimonials = document.querySelectorAll('.testimonial-card');
        if (testimonials[index]) {
            const scrollAmount = testimonials[index].offsetLeft;
            testimonialContainer.scrollTo({
                left: scrollAmount,
                behavior: 'smooth'
            });
        }
    });
});
    </script>
</body>
</html>