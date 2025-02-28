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
$orders_query = "SELECT o.id, o.book_title, o.owner_name, o.contact, o.book_price, o.order_date, o.address, 
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
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f9fc;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
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

        .container {
            width: 90%;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background: #0084ff;
            color: white;
        }
        .delivered {
            color: green;
            font-weight: bold;
        }
        .pending {
            color: orange;
            font-weight: bold;
        }
        .no-orders {
            text-align: center;
            padding: 20px;
            font-size: 18px;
        }
        .cancel-btn {
            background: red;
            color: white;
            border: none;
            padding: 8px 12px;
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

    <div class="container">
        <h2>Order History</h2>
        <?php if (mysqli_num_rows($orders_result) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Owner Name</th>
                        <th>Contact</th>
                        <th>Price</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Delivery Address</th>
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
                            <td><?php echo htmlspecialchars($order['book_title']); ?></td>
                            <td><?php echo htmlspecialchars($order['owner_name']); ?></td>
                            <td><?php echo htmlspecialchars($order['contact']); ?></td>
                            <td>$<?php echo number_format($order['book_price'], 2); ?></td>
                            <td><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                            <td><?php echo date("d M Y", strtotime($order['delivery_date'])); ?></td>
                            <td><?php echo nl2br(htmlspecialchars($order['address'])); ?></td>
                            <td><?php echo $status; ?></td>
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
        <?php else: ?>
            <p class="no-orders">You have no reserved orders yet.</p>
        <?php endif; ?>
    </div>
</body>
</html>
