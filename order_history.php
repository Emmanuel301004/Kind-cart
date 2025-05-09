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

// Handle Export functionality
if (isset($_GET['export'])) {
    $export_type = $_GET['export'];
    
    // Fetch all user's orders for export
    $export_query = "SELECT o.book_title, o.owner_name, o.contact, o.book_price, o.order_date, 
                    DATE_ADD(o.order_date, INTERVAL 2 DAY) AS delivery_date,
                    o.status, o.payment_method, o.address 
                    FROM orders o
                    INNER JOIN books b ON o.book_title = b.title
                    WHERE o.user_id=? AND b.status = 'Reserved'
                    ORDER BY o.order_date DESC";

    $stmt = $conn->prepare($export_query);
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $export_result = $stmt->get_result();
    
  if ($export_type == 'excel') {
        // Export as Excel
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="order_history.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo "Book Title\tOwner\tContact\tPrice\tOrder Date\tDelivery Date\tStatus\tPayment Method\tShipping Address\n";
        
        while ($row = mysqli_fetch_assoc($export_result)) {
            echo $row['book_title'] . "\t";
            echo $row['owner_name'] . "\t";
            echo $row['contact'] . "\t";
            echo number_format($row['book_price'], 2) . "\t"; // Removed rupee symbol
            echo date("d M Y", strtotime($row['order_date'])) . "\t";
            echo date("d M Y", strtotime($row['delivery_date'])) . "\t";
            echo $row['status'] . "\t";
            echo $row['payment_method'] . "\t";
            echo str_replace("\n", " ", $row['address']) . "\n";
        }
        exit;
    } 
    elseif ($export_type == 'pdf') {
    require_once('vendor/tecnickcom/tcpdf/tcpdf.php'); // Adjusted path: going one directory up from 'tools' to access 'tcpdf.php'

    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    $pdf->SetCreator('Your Site');
    $pdf->SetAuthor('Your Name');
    $pdf->SetTitle('Order History');
    $pdf->SetSubject('Order Export');
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(TRUE, 10);
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

    // Table Header
    $html = '
    <table border="1" cellpadding="4">
        <thead>
            <tr style="background-color:#f2f2f2;">
                <th><b>Book Title</b></th>
                <th><b>Owner</b></th>
                <th><b>Contact</b></th>
                <th><b>Price</b></th>
                <th><b>Order Date</b></th>
                <th><b>Delivery Date</b></th>
                <th><b>Status</b></th>
                <th><b>Payment Method</b></th>
                <th><b>Shipping Address</b></th>
            </tr>
        </thead>
        <tbody>
    ';

    while ($row = mysqli_fetch_assoc($export_result)) {
        $html .= '<tr>
            <td>' . htmlspecialchars($row['book_title']) . '</td>
            <td>' . htmlspecialchars($row['owner_name']) . '</td>
            <td>' . htmlspecialchars($row['contact']) . '</td>
            <td>' . number_format($row['book_price'], 2) . '</td>
            <td>' . date("d M Y", strtotime($row['order_date'])) . '</td>
            <td>' . date("d M Y", strtotime($row['delivery_date'])) . '</td>
            <td>' . htmlspecialchars($row['status']) . '</td>
            <td>' . htmlspecialchars($row['payment_method']) . '</td>
            <td>' . nl2br(htmlspecialchars($row['address'])) . '</td>
        </tr>';
    }

    $html .= '</tbody></table>';

    $pdf->writeHTML($html, true, false, true, false, '');

    // Output PDF to browser
    $pdf->Output('order_history.pdf', 'D'); // 'D' for download
    exit;
}

        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Order History</title>
            <style>
                body { font-family: Arial, sans-serif; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #2e7d32; color: white; }
                h1 { color: #2e7d32; }
                .header { text-align: center; margin-bottom: 30px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Kind Kart - Order History</h1>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Owner</th>
                        <th>Price</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Status</th>
                        <th>Payment</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($row = mysqli_fetch_assoc($export_result)): 
                    $payment_status = ($row['payment_method'] == 'COD') ? 'COD' : 'Paid Online';
                ?>
                    <tr>
                        <td><?php echo $row['book_title']; ?></td>
                        <td><?php echo $row['owner_name']; ?></td>
                        <td><?php echo number_format($row['book_price'], 2); ?></td>
                        <td><?php echo date("d M Y", strtotime($row['order_date'])); ?></td>
                        <td><?php echo date("d M Y", strtotime($row['delivery_date'])); ?></td>
                        <td><?php echo $row['status']; ?></td>
                        <td><?php echo $payment_status; ?></td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </body>
        </html>
        <?php
        $html = ob_get_clean();
        
// Now convert HTML to PDF using mPDF library (if available) or display HTML
if (class_exists('\Mpdf\Mpdf')) {
    try {
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($html);
        $mpdf->Output('order_history.pdf', 'D');
        exit;
    } catch (Exception $e) {
        // Fall back to HTML output if mPDF fails
        $_SESSION['export_error'] = "PDF generation failed. Using HTML format instead.";
        header("Content-Type: text/html");
        header("Content-Disposition: attachment; filename=order_history.html");
        echo $html;
        exit;
    }
} else {
    // If no PDF library is available, output as HTML file
    $_SESSION['export_error'] = "PDF library not available. Using HTML format instead.";
    header("Content-Type: text/html");
    header("Content-Disposition: attachment; filename=order_history.html");
    echo $html;
    exit;
}}
// Fetch user's orders with only reserved books
$orders_query = "SELECT o.id, o.book_title, o.owner_name, o.contact, o.book_price, o.order_date, o.address, o.status,
                 o.payment_method, DATE_ADD(o.order_date, INTERVAL 2 DAY) AS delivery_date 
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
        
        .paid {
            color: #2e7d32;
            font-weight: 600;
            background: rgba(46, 125, 50, 0.1);
            padding: 5px 10px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .to-be-paid {
            color: #e65100;
            font-weight: 600;
            background: rgba(230, 81, 0, 0.1);
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
        
        /* Export buttons styles */
        .export-container {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            gap: 10px;
        }
        
        .export-btn {
            background: #2e7d32;
            color: white;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }
        
        .export-btn:hover {
            background: #1b5e20;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .export-btn svg {
            width: 16px;
            height: 16px;
            fill: currentColor;
        }
        
        .export-error {
            background: #ffebee;
            color: #c62828;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            th, td {
                padding: 12px 10px;
                font-size: 14px;
            }
            
            .cancel-btn, .export-btn {
                padding: 6px 10px;
                font-size: 12px;
            }
            
            .page-container {
                margin-top: 80px;
            }
            
            .export-container {
                justify-content: center;
                flex-wrap: wrap;
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
    .excel-btn {
    background: #1d6f42; /* Excel green color */
}

.excel-btn:hover {
    background: #0e5a2f;
}

.pdf-btn {
    background: #d04423; /* PDF red color */
}

.pdf-btn:hover {
    background: #b03012;
}

.export-container {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 20px;
    gap: 12px;
}

.export-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 6px;
    color: white;
    font-weight: 500;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
}

.export-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.export-btn svg {
    width: 18px;
    height: 18px;
    fill: currentColor;
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
            
            <?php if (isset($_SESSION['export_error'])): ?>
            <div class="export-error">
                <?php echo $_SESSION['export_error']; unset($_SESSION['export_error']); ?>
            </div>
            <?php endif; ?>
            
            <?php if (mysqli_num_rows($orders_result) > 0): ?>
                <!-- Add export buttons -->
           <!-- Replace the two export containers with this single container -->
<div class="export-container">
    <a href="order_history.php?export=excel" class="export-btn excel-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M21.17,3.25H2.83c-0.41,0-0.75,0.34-0.75,0.75v15c0,0.41,0.34,0.75,0.75,0.75h18.33c0.41,0,0.75-0.34,0.75-0.75V4C21.92,3.59,21.58,3.25,21.17,3.25z M20.42,18.25H3.58V4.75h16.83V18.25z"/>
            <path d="M9.5,16.25l3-8h1.5l3,8h-1.6l-0.8-2h-2.7l-0.8,2H9.5z M12.7,12.75h1.6l-0.8-2.4L12.7,12.75z"/>
            <path d="M7,13.75H5v2.5H3.5v-8H7c1.4,0,2.5,1.1,2.5,2.5v0.5C9.5,12.65,8.4,13.75,7,13.75z M7,9.75H5v2.5h2c0.55,0,1-0.45,1-1v-0.5C8,10.2,7.55,9.75,7,9.75z"/>
        </svg>
        Export to Excel
    </a>
    <a href="order_history.php?export=pdf" class="export-btn pdf-btn">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M20,2H8C6.9,2,6,2.9,6,4v12c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V4C22,2.9,21.1,2,20,2z M20,16H8V4h12V16z"/>
            <path d="M4,6H2v14c0,1.1,0.9,2,2,2h14v-2H4V6z"/>
            <path d="M10,9h2v4h-2V9z M14,9h2v4h-2V9z M10,7h6v1h-6V7z M10,14h6v1h-6V14z"/>
        </svg>
        Export to PDF
    </a>
</div>
                
                
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
                                <th>Payment</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = mysqli_fetch_assoc($orders_result)): 
                                $delivery_date = new DateTime($order['delivery_date']);
                                $current_date = new DateTime();
                                $status = "<span class='" . strtolower($order['status']) . "'>" . $order['status'] . "</span>";
                                // Replace the existing payment status code block with this:
                                $payment_status = "";
                                if ($order['payment_method'] == 'COD') {
                                    // If the visual status is "Delivered" (based on date), mark as paid regardless of database status
                                    if ($current_date >= $delivery_date) {
                                        $payment_status = "<span class='paid'>Paid</span>";
                                    } else {
                                        $payment_status = "<span class='to-be-paid'>Amount to be paid: ₹" . number_format($order['book_price'], 2) . "</span>";
                                    }
                                } else {
                                    $payment_status = "<span class='paid'>Paid Online</span>";
                                }
                            ?>
                                <tr>
                                    <td class="book-title"><?php echo htmlspecialchars($order['book_title']); ?></td>
                                    <td><?php echo htmlspecialchars($order['owner_name']); ?></td>
                                    <td><?php echo htmlspecialchars($order['contact']); ?></td>
                                    <td class="price">₹<?php echo number_format($order['book_price'], 2); ?></td>
                                    <td class="date"><?php echo date("d M Y", strtotime($order['order_date'])); ?></td>
                                    <td class="date"><?php echo date("d M Y", strtotime($order['delivery_date'])); ?></td>
                                    <td><?php echo $status; ?></td>
                                    <td><?php echo $payment_status; ?></td>
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