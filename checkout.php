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
    $custom_address = isset($_POST['custom_address']) ? mysqli_real_escape_string($conn, $_POST['custom_address']) : '';
    
    // Combine selected address with custom entrance if provided
    if ($address === 'Other' && !empty($custom_address)) {
        $address .= ' - ' . $custom_address;
    }

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script>
        function toggleCardDetails() {
            let paymentMethod = document.getElementById('payment_method').value;
            let cardDetails = document.getElementById('card-details');
            cardDetails.style.display = paymentMethod === 'Card' ? 'block' : 'none';
        }

        function toggleCustomAddress() {
            let addressSelect = document.getElementById('address');
            let customAddressDiv = document.getElementById('custom-address');
            let customAddressInput = document.getElementById('custom_address');
            
            customAddressDiv.style.display = addressSelect.value === 'Other' ? 'block' : 'none';
            
            if (addressSelect.value !== 'Other') {
                customAddressInput.removeAttribute('required');
            } else {
                customAddressInput.setAttribute('required', 'required');
            }
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
            background: linear-gradient(135deg, #f6f9fc 0%, #e9ecef 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .checkout-container {
            width: 70%;
            max-width: 1000px;
            background: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            display: flex;
            gap: 30px;
        }
        .cart-items {
            flex: 1;
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
        }
        .cart-items h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: #333;
        }
        .total-amount {
            font-weight: 600;
            font-size: 18px;
            margin-top: 15px;
            text-align: right;
            color: #28a745;
        }
        .checkout-form {
            flex: 1;
        }
        .checkout-form h2 {
            color: #333;
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
            color: #495057;
        }
        textarea, input, select {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            transition: border-color 0.3s ease;
        }
        textarea:focus, input:focus, select:focus {
            outline: none;
            border-color: #007bff;
        }
        .card-details, .custom-address {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #28a745, #20c997);
            border: none;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 25px;
            transition: transform 0.3s ease;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        #custom-address {
            display: none;
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
                <label>Delivery Address:</label>
                <select name="address" id="address" onchange="toggleCustomAddress()" required>
                    <option value="">Select Address</option>
                    <option value="Chapel">Chapel</option>
                    <option value="Main Block">Main Block</option>
                    <option value="Other">Other Address</option>
                </select>
                
                <div id="custom-address" class="custom-address">
                    <label>Enter Specific Entrance/Address:</label>
                    <textarea name="custom_address" id="custom_address" placeholder="Enter your specific address or entrance details"></textarea>
                </div>

                <label>Payment Method:</label>
                <select name="payment_method" id="payment_method" onchange="toggleCardDetails()" required>
                    <option value="COD">Cash on Delivery</option>
                    <option value="Card">Card Payment</option>
                </select>
                <div class="card-details" id="card-details" style="display:none;">
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