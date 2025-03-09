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
$alertType = 'success';

// Handle book deletion
if (isset($_POST['delete_book'])) {
    $book_id = $_POST['book_id'];
    
    // Check if book has pending orders before deletion
    $check_order_query = "SELECT * FROM orders WHERE book_id = '$book_id' AND EXISTS (
        SELECT 1 FROM books WHERE books.book_id = orders.book_id AND books.status = 'Reserved'
    )";
    $check_result = $conn->query($check_order_query);
    
    if ($check_result->num_rows > 0) {
        $alertMessage = "Cannot delete book with pending orders. Please fulfill or cancel the orders first.";
        $alertType = "danger";
    } else {
        $delete_query = "DELETE FROM books WHERE book_id = '$book_id' AND user_id = '$user_id'";
        if ($conn->query($delete_query) === TRUE) {
            $alertMessage = "Book deleted successfully!";
            $alertType = "success";
        } else {
            $alertMessage = "Error deleting book: " . $conn->error;
            $alertType = "danger";
        }
    }
}

// Handle book status update
if (isset($_POST['update_status'])) {
    $book_id = $_POST['book_id'];
    $new_status = $_POST['new_status'];
    
    $update_query = "UPDATE books SET status = '$new_status' WHERE book_id = '$book_id' AND user_id = '$user_id'";
    if ($conn->query($update_query) === TRUE) {
        $alertMessage = "Book status updated successfully!";
        $alertType = "success";
    } else {
        $alertMessage = "Error updating status: " . $conn->error;
        $alertType = "danger";
    }
}

// Handle price update
if (isset($_POST['update_price'])) {
    $book_id = $_POST['book_id'];
    $new_price = $_POST['new_price'];
    
    if (is_numeric($new_price) && $new_price >= 0) {
        $update_query = "UPDATE books SET price = '$new_price' WHERE book_id = '$book_id' AND user_id = '$user_id'";
        if ($conn->query($update_query) === TRUE) {
            $alertMessage = "Book price updated successfully!";
            $alertType = "success";
        } else {
            $alertMessage = "Error updating price: " . $conn->error;
            $alertType = "danger";
        }
    } else {
        $alertMessage = "Invalid price! Please enter a valid amount.";
        $alertType = "danger";
    }
}

// Handle order status update
if (isset($_POST['update_order_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['order_status'];
    
    $update_query = "UPDATE orders SET status = '$new_status' WHERE id = '$order_id'";
    if ($conn->query($update_query) === TRUE) {
        $alertMessage = "Order status updated successfully!";
        $alertType = "success";
    } else {
        $alertMessage = "Error updating order status: " . $conn->error;
        $alertType = "danger";
    }
}

// Handle OTP verification and delivery confirmation
if (isset($_POST['verify_delivery_otp'])) {
    $order_id = $_POST['order_id'];
    $provided_otp = $_POST['otp'];
    $stored_otp = isset($_SESSION['order_'.$order_id.'_otp']) ? $_SESSION['order_'.$order_id.'_otp'] : null;
    
    if ($stored_otp && $provided_otp == $stored_otp) {
        // Update order status to Delivered
        $update_query = "UPDATE orders SET status = 'Delivered' WHERE id = '$order_id'";
        if ($conn->query($update_query) === TRUE) {
            // Clear OTP from session
            unset($_SESSION['order_'.$order_id.'_otp']);
            
            $alertMessage = "Delivery confirmed successfully!";
            $alertType = "success";
        } else {
            $alertMessage = "Error confirming delivery: " . $conn->error;
            $alertType = "danger";
        }
    } else {
        $alertMessage = "Invalid OTP. Please try again.";
        $alertType = "danger";
    }
}

// Generate and send OTP for delivery confirmation
if (isset($_POST['send_delivery_otp'])) {
    $order_id = $_POST['order_id'];
    
    // Generate a random 6-digit OTP
    $otp = mt_rand(100000, 999999);
    
    // Store OTP in session (In a real app, you would send this via SMS/email)
    $_SESSION['order_'.$order_id.'_otp'] = $otp;
      // For development purposes only - log to console instead of showing in UI
      echo "<script>console.log('Development mode: OTP generated is " . $otp . "');</script>";
    
    $alertMessage = "OTP has been generated: $otp (In a real app, this would be sent to the buyer via SMS/email)";
    $alertType = "info";
}

// Fetch user's books
$books_query = "SELECT * FROM books WHERE user_id = '$user_id' ORDER BY status, title";
$books_result = $conn->query($books_query);

// Fetch orders for books sold by this user
$orders_query = "SELECT o.*, b.book_id, b.title, b.status as book_status 
                FROM orders o 
                JOIN books b ON o.book_id = b.book_id 
                WHERE b.user_id = '$user_id'
                ORDER BY o.order_date DESC";
$orders_result = $conn->query($orders_query);   

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Listings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #2e7d32;
            --dark-green: #1b5e20;
            --light-green: #c8e6c9;
            --background-gray: #f8f9fa;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-gray);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        
        .container {
            max-width: 1200px;
            margin: 100px auto 40px;
            padding: 0 20px;
        }
        
        .tab-container {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            
        }
        
        .nav-tabs .nav-link {
            color: #666;
            font-weight: 500;
            padding: 10px 20px;
            border: none;
            border-bottom: 3px solid transparent;
        }
        
        .nav-tabs .nav-link.active {
            color: var(--primary-green);
            background: transparent;
            border-bottom: 3px solid var(--primary-green);
        }
        
        h2 {
            color: var(--primary-green);
            margin-bottom: 20px;
            font-weight: 600;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            display: inline-block;
        }
        
        .status-available {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .status-reserved {
            background-color: rgba(255, 152, 0, 0.1);
            color: #FF9800;
        }
        
        .status-sold {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196F3;
        }
        
        .status-processing {
            background-color: rgba(156, 39, 176, 0.1);
            color: #9C27B0;
        }
        
        .status-packing {
            background-color: rgba(63, 81, 181, 0.1);
            color: #3F51B5;
        }
        
        .status-ontheway {
            background-color: rgba(0, 188, 212, 0.1);
            color: #00BCD4;
        }
        
        .status-delivered {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4CAF50;
        }
        
        .book-card {
            border: 1px solid #eee;
            border-radius: 10px;
            margin-bottom: 20px;
            background: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .book-card:hover {
            box-shadow: 0 10px 15px rgba(0,0,0,0.1);
            transform: translateY(-3px);
        }
        
        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-title {
            font-weight: 600;
            margin: 0;
            color: #333;
        }
        
        .card-body {
            padding: 20px;
        }
        
        .book-details {
            margin-bottom: 15px;
        }
        
        .book-details p {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .book-details strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }
        
        .actions-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn-edit {
            background-color: var(--primary-green);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-edit:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
        }
        
        .btn-delete {
            background-color: #f44336;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-delete:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }
        
        .order-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .order-table th, .order-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .order-table th {
            background: #2e7d32;
            color: white;
            font-weight: 500;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 0.5px;
        }
        
        .order-table tr:hover {
            background-color: #f9f9f9;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #666;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 10px;
            margin: 20px 0;
        }
        
        .modal-dialog {
            max-width: 500px;
        }
        
        .modal-content {
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: none;
        }
        
        .modal-header {
            background-color: var(--primary-green);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .modal-title {
            font-weight: 600;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .modal-footer {
            border-top: none;
            padding: 15px 20px 20px;
        }
        
        /* FIX: Updated dropdown styles to ensure visibility */
        .status-dropdown-menu {
            min-width: 200px;
            position: absolute;
            z-index: 1050; /* Higher z-index to appear above table */
        }
        
        .status-dropdown-item {
            padding: 10px 15px;
            transition: all 0.2s;
        }
        
        .status-dropdown-item:hover {
            background-color: var(--light-green);
        }
        
        /* FIX: Make dropdown container position relative */
        .dropdown {
            position: relative;
        }
        
        .otp-form {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 15px;
        }
        
        .otp-input {
            letter-spacing: 5px;
            font-size: 1.2rem;
            text-align: center;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 0 15px;
                margin-top: 80px;
            }
            
            .tab-container {
                padding: 20px 15px;
            }
            
            .book-details strong {
                width: 100px;
            }
            
            .actions-container {
                flex-direction: column;
            }
            
            .order-table th, .order-table td {
                padding: 10px 8px;
                font-size: 14px;
            }
            
            /* FIX: Ensure dropdown is visible on mobile */
            .dropdown-menu {
                position: absolute;
                left: auto;
                right: 0;
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
    
    <div class="container">
        <?php if (!empty($alertMessage)): ?>
            <div class="alert alert-<?php echo $alertType; ?> alert-dismissible fade show" role="alert">
                <?php echo $alertMessage; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    
        <div class="tab-container">
            <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="books-tab" data-bs-toggle="tab" data-bs-target="#books-content" type="button" role="tab" aria-controls="books-content" aria-selected="true">My Books</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="orders-tab" data-bs-toggle="tab" data-bs-target="#orders-content" type="button" role="tab" aria-controls="orders-content" aria-selected="false">Received Orders</button>
                </li>
            </ul>
            
            <div class="tab-content" id="myTabContent">
                <!-- Books Tab -->
                <div class="tab-pane fade show active" id="books-content" role="tabpanel" aria-labelledby="books-tab">
                    <h2>My Listed Books</h2>
                    
                    <?php if ($books_result->num_rows > 0): ?>
                        <div class="books-container">
                            <?php while ($book = $books_result->fetch_assoc()): ?>
                                <div class="book-card">
                                    <div class="card-header">
                                        <h3 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h3>
                                        <div>
                                            <?php 
                                                $statusClass = '';
                                                switch($book['status']) {
                                                    case 'Available':
                                                        $statusClass = 'status-available';
                                                        break;
                                                    case 'Reserved':
                                                        $statusClass = 'status-reserved';
                                                        break;
                                                    case 'Sold':
                                                        $statusClass = 'status-sold';
                                                        break;
                                                }
                                            ?>
                                            <span class="status-badge <?php echo $statusClass; ?>"><?php echo $book['status']; ?></span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <div class="book-details">
                                            <p><strong>Book ID:</strong> <?php echo htmlspecialchars($book['book_id']); ?></p>
                                            <p><strong>Course:</strong> <?php echo htmlspecialchars($book['course']); ?></p>
                                            <p><strong>Semester:</strong> <?php echo htmlspecialchars($book['semester']); ?></p>
                                            <p><strong>Condition:</strong> <?php echo htmlspecialchars($book['book_condition']); ?></p>
                                            <p><strong>Price:</strong> <?php echo number_format($book['price'], 2); ?></p>
                                        </div>
                                        
                                        <div class="actions-container">
                                            <!-- Edit Price Button -->
                                            <button type="button" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#editPriceModal<?php echo $book['book_id']; ?>" <?php echo ($book['status'] != 'Available') ? 'disabled' : ''; ?>>
                                                Edit Price
                                            </button>
                                            
                                            <!-- Update Status Button -->
                                            <button type="button" class="btn btn-edit" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $book['book_id']; ?>" <?php echo ($book['status'] == 'Sold') ? 'disabled' : ''; ?>>
                                                Update Status
                                            </button>
                                            
                                            <!-- Delete Button -->
                                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this book?');">
                                                <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                <button type="submit" name="delete_book" class="btn btn-delete" <?php echo ($book['status'] == 'Reserved') ? 'disabled' : ''; ?>>
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Edit Price Modal -->
                                <div class="modal fade" id="editPriceModal<?php echo $book['book_id']; ?>" tabindex="-1" aria-labelledby="editPriceModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editPriceModalLabel">Update Price for <?php echo htmlspecialchars($book['title']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="new_price" class="form-label">New Price</label>
                                                        <input type="number" class="form-control" id="new_price" name="new_price" min="0" step="0.01" value="<?php echo $book['price']; ?>" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_price" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Update Status Modal -->
                                <div class="modal fade" id="updateStatusModal<?php echo $book['book_id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="updateStatusModalLabel">Update Status for <?php echo htmlspecialchars($book['title']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                                                    <div class="mb-3">
                                                        <label for="new_status" class="form-label">New Status</label>
                                                        <select class="form-select" id="new_status" name="new_status" required>
                                                            <option value="Available" <?php echo ($book['status'] == 'Available') ? 'selected' : ''; ?>>Available</option>
                                                            <option value="Reserved" <?php echo ($book['status'] == 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
                                                            <option value="Sold" <?php echo ($book['status'] == 'Sold') ? 'selected' : ''; ?>>Sold</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" name="update_status" class="btn btn-primary">Save Changes</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <p>You haven't listed any books yet.</p>
                            <a href="sell_books.php" class="btn btn-primary mt-3">List a Book</a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Orders Tab -->
                <div class="tab-pane fade" id="orders-content" role="tabpanel" aria-labelledby="orders-tab">
                    <h2>Orders Received</h2>
                    
                    <?php if ($orders_result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="order-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Book</th>
                                        <th>Price</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    // Rewind the result set
                                    $orders_result->data_seek(0);
                                    while ($order = $orders_result->fetch_assoc()): 
                                        // If status field doesn't exist in your orders table, 
                                        // use book_status as fallback (you'll need to modify your database structure)
                                        $order_status = isset($order['status']) ? $order['status'] : ($order['book_status'] == 'Reserved' ? 'Processing' : 'Delivered');
                                    ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo htmlspecialchars($order['title']); ?></td>
                                
                                            <td><?php echo number_format($order['book_price'], 2); ?></td>
                                            <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch($order_status) {
                                                        case 'Processing':
                                                            $statusClass = 'status-processing';
                                                            break;
                                                        case 'Packing':
                                                            $statusClass = 'status-packing';
                                                            break;
                                                        case 'On the way':
                                                            $statusClass = 'status-ontheway';
                                                            break;
                                                        case 'Out for delivery':
                                                            $statusClass = 'status-ontheway';
                                                            break;
                                                        case 'Delivered':
                                                            $statusClass = 'status-delivered';
                                                            break;
                                                        default:
                                                            $statusClass = 'status-processing';
                                                    }
                                                ?>
                                                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $order_status; ?></span>
                                            </td><td>
    <?php if ($order['book_status'] != 'Sold' && $order_status != 'Delivered'): ?>
        <div class="dropdown">
            <button class="btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                Update Status
            </button>
            <ul class="dropdown-menu status-dropdown-menu">
                <li>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="order_status" value="Processing">
                        <button type="submit" name="update_order_status" class="dropdown-item status-dropdown-item">Processing</button>
                    </form>
                </li>
                <li>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="order_status" value="Packing">
                        <button type="submit" name="update_order_status" class="dropdown-item status-dropdown-item">Packing</button>
                    </form>
                </li>
                <li>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="order_status" value="On the way">
                        <button type="submit" name="update_order_status" class="dropdown-item status-dropdown-item">On the way</button>
                    </form>
                </li>
                <li>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <input type="hidden" name="order_status" value="Out for delivery">
                        <button type="submit" name="update_order_status" class="dropdown-item status-dropdown-item">Out for delivery</button>
                    </form>
                </li>
                <li>
                    <form method="post">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <button type="submit" name="send_delivery_otp" class="dropdown-item status-dropdown-item">Generate Delivery OTP</button>
                    </form>
                </li>
            </ul>
        </div>
        
        <!-- OTP Verification Form -->
        <?php if ($order_status == 'Out for delivery'): ?>
        <div class="otp-form mt-2">
            <form method="post" class="d-flex">
                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                <input type="text" name="otp" class="form-control form-control-sm otp-input me-2" placeholder="Enter OTP" required>
                <button type="submit" name="verify_delivery_otp" class="btn btn-sm btn-success">Verify</button>
            </form>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($order_status == 'Delivered'): ?>
            <span class="badge bg-success">Completed</span>
        <?php else: ?>
            <span class="badge bg-secondary">No action needed</span>
        <?php endif; ?>
    <?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</tbody>
</table>
</div>
<?php else: ?>
<div class="no-data">
    <p>You haven't received any orders yet.</p>
</div>
<?php endif; ?>
</div>
</div>
</div>
</div>

<script>
// Additional JavaScript if needed
document.addEventListener('DOMContentLoaded', function() {
    // Auto-close alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
</body>
</html>