<?php
session_start();
include 'db.php'; // Include your database connection file

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch cart items
$cart_items = mysqli_query($conn, "SELECT cart.*, books.title, books.price, books.owner_name, books.contact, books.course, books.semester, books.book_condition, books.status 
                                   FROM cart 
                                   JOIN books ON cart.book_id = books.book_id 
                                   WHERE cart.user_id='$user_id'");

// Calculate total amount
$total_amount = 0;
mysqli_data_seek($cart_items, 0);
while ($cart = mysqli_fetch_assoc($cart_items)) {
    $total_amount += $cart['price'] * $cart['quantity'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $payment_method = $_POST['payment_method'];
    $card_number = isset($_POST['card_number']) ? $_POST['card_number'] : null;
    $expiry_date = isset($_POST['expiry_date']) ? $_POST['expiry_date'] : null;
    $cvv = isset($_POST['cvv']) ? $_POST['cvv'] : null;

    if ($payment_method == 'Card' && (!$card_number || !$expiry_date || !$cvv)) {
        echo "<script>alert('Please enter all card details!'); window.history.back();</script>";
        exit();
    }

    // Insert order details into orders table and update book status to 'Reserved'
    mysqli_data_seek($cart_items, 0);
    while ($cart = mysqli_fetch_assoc($cart_items)) {
        $book_id = $cart['book_id'];
        $quantity = $cart['quantity'];

        // Insert order details
        $order_query = "INSERT INTO orders (user_id, book_id, order_date, address, book_title, owner_name, contact, course, semester, book_condition, book_price) 
                        VALUES ('$user_id', '$book_id', NOW(), '$address', '{$cart['title']}', '{$cart['owner_name']}', '{$cart['contact']}', '{$cart['course']}', '{$cart['semester']}', '{$cart['book_condition']}', '{$cart['price']}')";
        mysqli_query($conn, $order_query);

        // Update book status to 'Reserved'
        $update_status_query = "UPDATE books SET status='Reserved' WHERE book_id='$book_id'";
        mysqli_query($conn, $update_status_query);
    }

    // Clear cart after order placement
    mysqli_query($conn, "DELETE FROM cart WHERE user_id='$user_id'");

    // Redirect to success page
    header("Location: success.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <script>
        function toggleCardDetails() {
            let paymentMethod = document.getElementById('payment_method').value;
            let cardDetails = document.getElementById('card-details');
            cardDetails.style.display = paymentMethod === 'Card' ? 'block' : 'none';
        }

        function validateCardDetails(input, type) {
            let value = input.value;
            let isValid = false;

            if (type === 'card_number') {
                isValid = /^[0-9]{16}$/.test(value);
            } else if (type === 'cvv') {
                isValid = /^[0-9]{3}$/.test(value);
            }

            input.style.borderColor = isValid ? 'green' : 'red';
        }

        function formatExpiryDate(input) {
            let value = input.value.replace(/\D/g, ''); // Remove non-numeric characters
            if (value.length > 4) {
                value = value.slice(0, 4); // Limit to MMYY format
            }
            if (value.length >= 3) {
                input.value = value.slice(0, 2) + '/' + value.slice(2);
            } else {
                input.value = value;
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f6f9fc;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .checkout-container {
            width: 60%;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 20px;
        }
        .cart-items {
            flex: 1;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        .cart-items h3 {
            font-size: 20px;
            margin-bottom: 15px;
        }
        .checkout-form {
            flex: 1;
        }
        label {
            font-weight: 600;
            display: block;
            margin-top: 12px;
        }
        textarea, input, select {
            width: 100%;
            padding: 12px;
            margin-top: 6px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .card-details {
            display: none;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: rgb(8, 175, 44);
            border: none;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="cart-items">
            <h3>Your Cart Items</h3>
            <ul>
                <?php
                mysqli_data_seek($cart_items, 0);
                while ($cart = mysqli_fetch_assoc($cart_items)): ?>
                    <li><?php echo htmlspecialchars($cart['title']) . " - $" . number_format($cart['price'], 2) . " x " . $cart['quantity']; ?></li>
                <?php endwhile; ?>
            </ul>
            <div class="total-amount">Total Amount: $<?php echo number_format($total_amount, 2); ?></div>
        </div>
        <div class="checkout-form">
            <h2>Checkout</h2>
            <form method="POST" action="checkout.php">
                <label>Address:</label>
                <textarea name="address" required></textarea>
                <label>Payment Method:</label>
                <select name="payment_method" id="payment_method" onchange="toggleCardDetails()" required>
                    <option value="COD">Cash on Delivery</option>
                    <option value="Card">Card Payment</option>
                </select>
                <div class="card-details" id="card-details">
                    <label>Card Number:</label>
                    <input type="text" name="card_number" maxlength="16" oninput="validateCardDetails(this, 'card_number')">
                    <label>Expiry Date (MM/YY):</label>
                    <input type="text" name="expiry_date" placeholder="MM/YY" maxlength="5" oninput="formatExpiryDate(this)">
                    <label>CVV:</label>
                    <input type="text" name="cvv" maxlength="3" oninput="validateCardDetails(this, 'cvv')">
                </div>
                <button type="submit" class="btn">Place Order</button>
            </form>
        </div>
    </div>
</body>
</html>
