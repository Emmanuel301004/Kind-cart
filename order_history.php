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

    // Update book status to "Available"
    $update_book_query = "UPDATE books SET status = 'Available' WHERE title = '$book_title'";
    mysqli_query($conn, $update_book_query);

    // Delete the order from the orders table
    $delete_order_query = "DELETE FROM orders WHERE id = '$order_id'";
    mysqli_query($conn, $delete_order_query);

    header("Location: order_history.php");
    exit();
}

// Fetch user's orders with only reserved books
$orders_query = "SELECT o.id, o.book_title, o.owner_name, o.contact, o.book_price, o.order_date, o.address,o.status,
                 DATE_ADD(o.order_date, INTERVAL 2 DAY) AS delivery_date 
                 FROM orders o
                 INNER JOIN books b ON o.book_title = b.title
                 WHERE o.user_id='$user_id' AND b.status = 'Reserved'
                 ORDER BY o.order_date DESC";

$orders_result = mysqli_query($conn, $orders_query);
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
                                        <?php if ($current_date < $delivery_date): ?>
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
</body>
</html>