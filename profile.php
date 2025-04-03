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

// Fetch sales statistics
$sql_sales = "SELECT 
              COUNT(*) as total_sales,
              
                 SUM(CASE WHEN status = 'Delivered' THEN book_price ELSE 0 END) as total_revenue,
              COUNT(CASE WHEN status = 'Delivered' THEN 1 END) as completed_orders,
              COUNT(CASE WHEN status = 'Processing' THEN 1 END) as processing_orders
            FROM orders 
            WHERE owner_name = (SELECT name FROM users WHERE id = ?) OR user_id = ?";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->bind_param("ii", $user_id, $user_id);
$stmt_sales->execute();
$stmt_sales->bind_result($total_sales, $total_revenue, $completed_orders, $processing_orders);
$stmt_sales->fetch();
$stmt_sales->close();


// Format the total revenue to handle null values
$total_revenue = $total_revenue ? number_format($total_revenue, 2) : '0.00';

// Get recent orders (both buying and selling)
$sql_recent_orders = "SELECT id, book_title, book_price, order_date, status 
                      FROM orders 
                      WHERE user_id = ? OR owner_name = (SELECT name FROM users WHERE id = ?)
                      ORDER BY order_date DESC LIMIT 5";
$stmt_recent = $conn->prepare($sql_recent_orders);
$stmt_recent->bind_param("ii", $user_id, $user_id);
$stmt_recent->execute();
$recent_orders = $stmt_recent->get_result();
$stmt_recent->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile & Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
       
        /* Main container */
        .main-container {
            margin: 100px auto 60px;
            width: 92%;
            max-width: 1200px;
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
        }

        /* Tabs navigation */
        .tabs-nav {
            display: flex;
            width: 100%;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            margin-bottom: 10px;
        }

        .tab-link {
            flex: 1;
            padding: 15px 20px;
            text-align: center;
            background: white;
            color: #555;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            border-bottom: 3px solid transparent;
        }

        .tab-link.active {
            color: #2e7d32;
            border-bottom: 3px solid #2e7d32;
            background: #f9fff9;
        }

        .tab-link:hover:not(.active) {
            background: #f5f5f5;
            color: #333;
        }

        .tab-link i {
            margin-right: 8px;
        }

        /* Tab Content */
        .tab-content {
            display: none;
            width: 100%;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Enhanced profile container */
        .profile-container {
            background-color: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            padding: 40px;
            transition: all 0.4s ease;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            width: 100%;
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

        h2 {
            font-size: 24px;
            color: #2e7d32;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
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

        .btn-primary {
            background: linear-gradient(to right, #2196F3, #1976D2);
            color: white;
        }

        .btn-success {
            background: linear-gradient(to right, #4CAF50, #2e7d32);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(to right, #f44336, #d32f2f);
            color: white;
        }

        /* Form field styling */
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
        
        /* Security settings */
        .settings-group {
            margin-bottom: 30px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        /* Statistics cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            text-align: center;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .stat-icon {
            font-size: 36px;
            margin-bottom: 15px;
            color: #2e7d32;
        }

        .stat-value {
            font-size: 28px;
            font-weight: bold;
            margin: 0;
            color: #333;
        }

        .stat-label {
            font-size: 14px;
            color: #777;
            margin-top: 5px;
        }

        /* Tables */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }

        th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #333;
        }

        tbody tr:hover {
            background-color: #f5f5f5;
        }

        .status-pill {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-delivered {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-processing {
            background-color: #fff8e1;
            color: #ff8f00;
        }

        .view-all-btn {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #2196F3;
            text-decoration: none;
            font-weight: 600;
        }

        .view-all-btn:hover {
            text-decoration: underline;
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

            /* Adjust container for mobile */
            .main-container {
                margin: 90px auto 40px;
                width: 90%;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }

            .profile-container {
                padding: 25px 20px;
            }

            form input, button {
                padding: 12px 15px;
            }

            .button-container {
                flex-direction: column;
            }
        }
        
        /* Fix profile dropdown for mobile */
        @media (max-width: 768px) {
            .profile-dropdown-content {
                right: 0;
                top: 40px;
            }
            
            .tabs-nav {
                flex-direction: column;
            }
            
            .tab-link {
                border-bottom: none;
                border-left: 3px solid transparent;
            }
            
            .tab-link.active {
                border-bottom: none;
                border-left: 3px solid #2e7d32;
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

<div class="main-container">
    <!-- Tabs navigation -->
    <div class="tabs-nav">
        <div class="tab-link active" data-tab="profile">
            <i class="fas fa-user"></i> Profile
        </div>
        <div class="tab-link" data-tab="settings">
            <i class="fas fa-cog"></i> Settings
        </div>
        <div class="tab-link" data-tab="reports">
            <i class="fas fa-chart-bar"></i> Sales Reports
        </div>
        <div class="tab-link" data-tab="orders">
            <i class="fas fa-shopping-bag"></i> Order History
        </div>
    </div>
    
    <!-- Profile Tab -->
    <div id="profile" class="tab-content active">
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
                
                <div class="form-group">
                    <label class="form-field-label" for="address">Default Address</label>
                    <input type="text" id="address" name="address" value="Chapel" disabled>
                </div>

                <div class="button-container">
                    <button type="button" id="editBtn" class="btn-primary" onclick="enableEdit()">Edit Profile</button>
                    <button type="submit" id="saveBtn" class="btn-success" style="display: none;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Settings Tab -->
    <div id="settings" class="tab-content">
        <div class="profile-container">
            <h1>Account Settings</h1>
            
            <form action="update_settings.php" method="post" id="settingsForm">               
                <div class="settings-group">
                    <h2><i class="fas fa-shield-alt"></i> Security Settings</h2>
                    
                    <div class="form-group">
                        <label class="form-field-label" for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-field-label" for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-field-label" for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>
                
                <div class="button-container">
                    <button type="submit" class="btn-success">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Reports Tab -->
    <div id="reports" class="tab-content">
        <div class="profile-container">
            <h1>Sales & Performance Reports</h1>
            
            <div class="stats-container">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <p class="stat-value"><?php echo $total_sales; ?></p>
                    <p class="stat-label">Total Sales</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <p class="stat-value">₹<?php echo $total_revenue; ?></p>
                    <p class="stat-label">Total Revenue</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <p class="stat-value"><?php echo $completed_orders; ?></p>
                    <p class="stat-label">Completed Orders</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <p class="stat-value"><?php echo $processing_orders; ?></p>
                    <p class="stat-label">Processing Orders</p>
                </div>
            </div>
            
            <div class="chart-container">
                <h2><i class="fas fa-chart-bar"></i> Sales Overview</h2>
                <p>Your monthly sales performance will be displayed here.</p>
                <!-- In a future implementation, we can add a chart library like Chart.js here -->
            </div>
        </div>
    </div>
    
    <!-- Orders Tab -->
    <div id="orders" class="tab-content">
        <div class="profile-container">
            <h1>Recent Orders</h1>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Book Title</th>
                            <th>Price</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($recent_orders->num_rows > 0) {
                            while ($row = $recent_orders->fetch_assoc()) {
                                echo '<tr>';
                                echo '<td>#' . $row['id'] . '</td>';
                                echo '<td>' . htmlspecialchars($row['book_title']) . '</td>';
                                echo '<td>₹' . number_format($row['book_price'], 2) . '</td>';
                                echo '<td>' . date('M d, Y', strtotime($row['order_date'])) . '</td>';
                                
                                $status_class = ($row['status'] == 'Delivered') ? 'status-delivered' : 'status-processing';
                                echo '<td><span class="status-pill ' . $status_class . '">' . $row['status'] . '</span></td>';
                                
                                echo '</tr>';
                            }
                        } else {
                            echo '<tr><td colspan="5" style="text-align: center;">No orders found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
                <a href="order_history.php" class="view-all-btn">View All Orders</a>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle mobile menu
    const mobileMenu = document.getElementById('mobile-menu');
    const navLinks = document.getElementById('nav-links');
    const menuOverlay = document.getElementById('menu-overlay');
    
    mobileMenu.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
        navLinks.classList.toggle('active');
        menuOverlay.classList.toggle('active');
        document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
    });
    
    menuOverlay.addEventListener('click', () => {
        mobileMenu.classList.remove('active');
        navLinks.classList.remove('active');
        menuOverlay.classList.remove('active');
        document.body.style.overflow = '';
    });
    
    // Tab functionality
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabLinks.forEach(link => {
        link.addEventListener('click', () => {
            const tabId = link.getAttribute('data-tab');
            
            // Remove active class from all tabs
            tabLinks.forEach(item => item.classList.remove('active'));
            tabContents.forEach(item => item.classList.remove('active'));
            
            // Add active class to selected tab
            link.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Enable edit mode for profile form
    function enableEdit() {
        const inputs = document.querySelectorAll('#profileForm input:not([name="email"])');
        inputs.forEach(input => {
            input.disabled = false;
        });
        
        document.getElementById('editBtn').style.display = 'none';
        document.getElementById('saveBtn').style.display = 'block';
    }
    
    // Form validation for password change
    document.getElementById('settingsForm').addEventListener('submit', function(e) {
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        if (newPassword !== confirmPassword) {
            e.preventDefault();
            alert('New passwords do not match!');
        }
    });
</script>
</body>
</html>