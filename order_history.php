<?php
session_start();
include 'db.php'; // Database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle order cancellation
if (isset($_POST['cancel_order'])) {
    $order_id = $_POST['order_id'];
    $book_title = $_POST['book_title'];

    // Sanitize inputs using prepared statements
    // Update book status using prepared statement
    $update_stmt = $conn->prepare("UPDATE books SET status = 'Available' WHERE title = ?");
    $update_stmt->bind_param("s", $book_title);
    $update_stmt->execute();

    // Delete order using prepared statement
    $delete_stmt = $conn->prepare("DELETE FROM orders WHERE id = ?");
    $delete_stmt->bind_param("s", $order_id);
    $delete_stmt->execute();

    header("Location: order_history.php");
    exit();
}

// Fetch user's orders with only reserved books
$orders_query = "SELECT o.id, o.book_title, o.owner_name, o.contact, o.book_price, o.order_date, o.address, o.status,
                 DATE_ADD(o.order_date, INTERVAL 2 DAY) AS delivery_date 
                 FROM orders o
                 INNER JOIN books b ON o.book_title = b.title
                 WHERE o.user_id=? AND b.status = 'Reserved'
                 ORDER BY o.order_date DESC";

$stmt = $conn->prepare($orders_query);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f9fc;
            margin: 0;
            padding: 0;
            color: #333;
        }
        /* Keeping navbar styles intact */
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
        
        /* New improved styling for the rest of the page */
        .page-container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .container {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }
        
        h2 {
            color: #2e7d32;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            position: relative;
            padding-bottom: 10px;
        }
        
        h2:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: #2e7d32;
            border-radius: 2px;
        }
        
        .order-summary {
            margin-bottom: 25px;
            text-align: center;
            color: #666;
            font-size: 16px;
        }
        
        .table-container {
            overflow-x: auto;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #2e7d32;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        th:first-child {
            border-top-left-radius: 10px;
        }
        
        th:last-child {
            border-top-right-radius: 10px;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        tr:last-child td {
            border-bottom: none;
        }
        
        .delivered {
            color: #2e7d32;
            font-weight: 600;
            background: rgba(46, 125, 50, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .pending {
            color: #ff9800;
            font-weight: 600;
            background: rgba(255, 152, 0, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .no-orders {
            text-align: center;
            padding: 40px 20px;
            font-size: 18px;
            color: #666;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
        }
        
        .cancel-btn {
            background: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }
        
        .cancel-btn:hover {
            background: #d32f2f;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .book-title {
            font-weight: 600;
            color: #333;
        }
        
        .price {
            font-weight: 600;
        }
        
        .date {
            color: #666;
            font-size: 14px;
        }
        
        @media (max-width: 768px) {
            th, td {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .cancel-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .page-container {
                margin-top: 80px;
            }
        }
            /* Mobile menu button */
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
    
    /* Responsive navbar adjustments */
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
    }
    
    /* Fix profile dropdown for mobile */
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

    <div class="page-container">
        <div class="container">
            <h2>Your Order History</h2>
            <p class="order-summary">View and manage your reserved book orders</p>
            
            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Owner</th>
                                <th>Contact</th>
                                <th>Price</th>
                                <th>Order Date</th>
                                <th>Delivery Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): 
                                $delivery_date = new DateTime($order['delivery_date']);
                                $current_date = new DateTime();
                                $status = ($current_date >= $delivery_date) ? "<span class='delivered'>Delivered</span>" : "<span class='pending'>Pending</span>";
                            ?>
                                <tr>
                                    <td class="book-title"><?php echo htmlspecialchars($order['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($order['owner_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['contact']); ?></td>
                                    <td class="price"><?php echo number_format($order['book_price'], 2); ?></td>
                                    <td class="date"><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                    <td class="date"><?php echo date("d M Y", strtotime($order['delivery_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($order['status']); ?></td>
                                    <td>
                                        <?php if ($current_date < $delivery_date && $order['status'] != 'Delivered'): ?>
                                            <form method="post">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <input type="hidden" name="book_title" value="<?php echo $order['book_title']; ?>">
                                                <button type="submit" name="cancel_order" class="cancel-btn">Cancel</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="no-orders">
                    <p>You have no reserved orders yet.</p>
                    <p>Browse our collection to find your next favorite book!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script>// Add this to your existing JavaScript at the bottom of your file
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
});</script>
</body>
</html> 