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
    if (isset($_POST['confirm_order']) && isset($_POST['payment_method']) && $_POST['payment_method'] == 'Card') {
        // For card payment, verify OTP
        if (isset($_POST['otp']) && $_POST['otp'] == $_SESSION['otp']) {
            // Process order (card payment with verified OTP)
            processOrder($conn, $user_id, $cart_items, $_POST);
        } else {
            // OTP validation failed
            echo "<script>alert('Invalid OTP! Please try again.');</script>";
        }
    } else if (isset($_POST['place_order']) || (isset($_POST['confirm_order']) && isset($_POST['payment_method']) && $_POST['payment_method'] == 'COD')) {
        // Process order directly for COD
        processOrder($conn, $user_id, $cart_items, $_POST);
    } else if (isset($_POST['verify_payment']) && isset($_POST['payment_method']) && $_POST['payment_method'] == 'Card') {
        // Generate OTP for card payment
        $otp = rand(100000, 999999);
        $_SESSION['otp'] = $otp;
        
        // In a real scenario, you would send this OTP via SMS or email
        // For this implementation, we'll just save it and print to console
        echo "<script>console.log('OTP for card verification: $otp');</script>";
    }
}

// Function to process orders
function processOrder($conn, $user_id, $cart_items, $post_data) {
    $address = mysqli_real_escape_string($conn, $post_data['address']);
    $custom_address = isset($post_data['custom_address']) ? mysqli_real_escape_string($conn, $post_data['custom_address']) : '';
    
    // Combine selected address with custom entrance if provided
    if ($address === 'Other' && !empty($custom_address)) {
        $address .= ' - ' . $custom_address;
    }

    $payment_method = $post_data['payment_method'];

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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script>
        function toggleCardDetails() {
            let paymentMethod = document.getElementById('payment_method').value;
            let cardDetails = document.getElementById('card-details');
            cardDetails.style.display = paymentMethod === 'Card' ? 'block' : 'none';
            
            // Update button text and action based on payment method
            let submitBtn = document.getElementById('submit-btn');
            if (paymentMethod === 'Card') {
                submitBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Verification';
                submitBtn.name = 'verify_payment';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-check-circle"></i> Place Order';
                submitBtn.name = 'place_order';
            }
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
                isValid = /^[0-9]{16}$/.test(value.replace(/\s/g, ''));
                input.value = value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
            } else if (type === 'cvv') {
                isValid = /^[0-9]{3}$/.test(value);
            }

            input.style.borderColor = isValid ? '#28a745' : '#dc3545';
            
            // Add a visual indicator
            let feedback = input.nextElementSibling;
            if (!feedback || !feedback.classList.contains('validation-feedback')) {
                feedback = document.createElement('div');
                feedback.className = 'validation-feedback';
                input.parentNode.insertBefore(feedback, input.nextSibling);
            }
            
            feedback.innerHTML = isValid ? 
                '<i class="fas fa-check-circle" style="color: #28a745;"></i>' : 
                '<i class="fas fa-times-circle" style="color: #dc3545;"></i>';
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
        
        function selectPaymentMethod(element, method) {
            document.getElementById('payment_method').value = method;
            
            // Remove selected class from all payment options
            document.querySelectorAll('.payment-option').forEach(el => {
                el.classList.remove('selected');
            });
            
            // Add selected class to clicked option
            element.classList.add('selected');
            
            // Toggle card details
            toggleCardDetails();
        }
    </script>
    <style>
        :root {
            --primary-color: #4c8bf5;
            --secondary-color: #28a745;
            --accent-color: #f5a623;
            --dark-color: #333;
            --light-color: #f8f9fa;
            --danger-color: #dc3545;
            --success-color: #28a745;
            --border-radius: 12px;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ecf0f3 0%, #dde0e2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: var(--dark-color);
        }
        
        .checkout-container {
            width: 85%;
            max-width: 1200px;
            background: #ffffff;
            padding: 40px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        
        .checkout-header {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .checkout-header h1 {
            font-size: 30px;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .checkout-progress {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .checkout-progress::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 5%;
            width: 90%;
            height: 4px;
            background-color: #e9ecef;
            z-index: 1;
        }
        
        .progress-step {
            position: relative;
            z-index: 2;
            text-align: center;
            width: 33.33%;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background-color: #e9ecef;
            color: #6c757d;
            font-weight: 600;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 10px;
            transition: var(--transition);
        }
        
        .active .step-number {
            background-color: var(--primary-color);
            color: white;
        }
        
        .completed .step-number {
            background-color: var(--success-color);
            color: white;
        }
        
        .step-text {
            font-size: 14px;
            font-weight: 500;
            color: #6c757d;
        }
        
        .active .step-text, .completed .step-text {
            color: var(--dark-color);
        }
        
        .checkout-content {
            display: flex;
            gap: 30px;
        }
        
        @media (max-width: 768px) {
            .checkout-content {
                flex-direction: column;
            }
            .checkout-container {
                width: 95%;
                padding: 20px;
            }
        }
        
        .cart-summary {
            flex: 1;
            background: var(--light-color);
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }
        
        .cart-summary h3 {
            font-size: 22px;
            margin-bottom: 20px;
            color: var(--dark-color);
            display: flex;
            align-items: center;
        }
        
        .cart-summary h3 i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .cart-items-list {
            list-style: none;
            margin-bottom: 20px;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .cart-item {
            padding: 15px;
            margin-bottom: 10px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .cart-item-details {
            flex: 1;
        }
        
        .cart-item-title {
            font-weight: 500;
            margin-bottom: 5px;
        }
        
        .cart-item-price {
            color: var(--primary-color);
            font-weight: 600;
        }
        
        .divider {
            height: 1px;
            background-color: #dee2e6;
            margin: 20px 0;
        }
        
        .total-amount {
            font-weight: 700;
            font-size: 20px;
            text-align: right;
            color: var(--secondary-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 8px;
        }
        
        .checkout-form {
            flex: 1.5;
            padding: 0;
        }
        
        .checkout-form h2 {
            color: var(--dark-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .checkout-form h2 i {
            margin-right: 10px;
            color: var(--primary-color);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            font-weight: 500;
            display: block;
            margin-bottom: 8px;
            color: #495057;
        }
        
        .form-control {
            width: 100%;
            padding: 14px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-family: 'Poppins', sans-serif;
            font-size: 16px;
            transition: var(--transition);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 139, 245, 0.2);
        }
        
        .card-details, .custom-address, .otp-form {
            background: var(--light-color);
            padding: 25px;
            border-radius: var(--border-radius);
            margin-top: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            animation: fadeIn 0.5s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(to right, var(--primary-color), #5d9cff);
            border: none;
            color: white;
            font-size: 18px;
            font-weight: 600;
            border-radius: var(--border-radius);
            cursor: pointer;
            margin-top: 25px;
            transition: var(--transition);
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
        }
        
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 139, 245, 0.3);
        }
        
        .btn-success {
            background: linear-gradient(to right, var(--secondary-color), #34ce57);
        }
        
        .btn-success:hover {
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }
        
        .btn-outline {
            background: transparent;
            border: 2px solid var(--primary-color);
            color: var(--primary-color);
        }
        
        .btn-outline:hover {
            background: rgba(76, 139, 245, 0.1);
        }
        
        #custom-address, #card-details {
            display: none;
        }
        
        .payment-options {
            display: flex;
            gap: 15px;
            margin-top: 10px;
        }
        
        .payment-option {
            flex: 1;
            padding: 15px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: var(--transition);
        }
        
        .payment-option:hover {
            border-color: var(--primary-color);
        }
        
        .payment-option.selected {
            border-color: var(--primary-color);
            background: rgba(76, 139, 245, 0.1);
        }
        
        .payment-option i {
            font-size: 24px;
            margin-bottom: 8px;
            color: var(--primary-color);
        }
        
        .validation-feedback {
            margin-top: 5px;
            display: flex;
            align-items: center;
            height: 20px;
        }
        
        .card-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .form-input-wrapper {
            position: relative;
        }
        
        .code-input {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        
        .code-input input {
            width: 50px;
            height: 60px;
            border: 2px solid #ced4da;
            border-radius: 8px;
            font-size: 24px;
            text-align: center;
            transition: var(--transition);
        }
        
        .code-input input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(76, 139, 245, 0.2);
        }
        
        .timer {
            font-size: 16px;
            margin-top: 15px;
            text-align: center;
            color: #6c757d;
        }
        
        .resend-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
        }
        
        .resend-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="checkout-container">
        <div class="checkout-header">
            <h1>Complete Your Purchase</h1>
            
            <div class="checkout-progress">
                <?php 
                $isCardPaymentWithOTP = isset($_POST['verify_payment']) && $_POST['payment_method'] == 'Card';
                ?>
                <div class="progress-step <?php echo !$isCardPaymentWithOTP ? 'active' : 'completed'; ?>">
                    <div class="step-number">1</div>
                    <div class="step-text">Shipping & Payment</div>
                </div>
                <div class="progress-step <?php echo $isCardPaymentWithOTP ? 'active' : ''; ?>">
                    <div class="step-number">2</div>
                    <div class="step-text">Verification</div>
                </div>
                <div class="progress-step">
                    <div class="step-number">3</div>
                    <div class="step-text">Confirmation</div>
                </div>
            </div>
        </div>
        
        <div class="checkout-content">
            <div class="cart-summary">
                <h3><i class="fas fa-shopping-cart"></i> Your Order</h3>
                <ul class="cart-items-list">
                    <?php
                    mysqli_data_seek($cart_items, 0);
                    while ($cart = mysqli_fetch_assoc($cart_items)): ?>
                        <li class="cart-item">
                            <div class="cart-item-details">
                                <div class="cart-item-title"><?php echo htmlspecialchars($cart['title']); ?></div>
                                <div>Qty: <?php echo $cart['quantity']; ?></div>
                            </div>
                            <div class="cart-item-price"><?php echo number_format($cart['price'] * $cart['quantity'], 2); ?></div>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <div class="divider"></div>
                
                <div class="total-amount">
                    <span>Total:</span>
                    <span><?php echo number_format($total_amount, 2); ?></span>
                </div>
            </div>
            
            <div class="checkout-form">
                <?php if (!isset($_POST['verify_payment'])): ?>
                    <!-- Step 1: Initial Checkout Form -->
                    <h2><i class="fas fa-map-marker-alt"></i> Shipping & Payment</h2>
                    <form method="POST" action="checkout.php">
                        <div class="form-group">
                            <label for="address">Delivery Location:</label>
                            <select name="address" id="address" class="form-control" onchange="toggleCustomAddress()" required>
                                <option value="">Select Location</option>
                                <option value="Chapel">Chapel</option>
                                <option value="Main Block">Main Block</option>
                                <option value="Other">Other Location</option>
                            </select>
                        </div>
                        
                        <div id="custom-address" class="custom-address">
                            <div class="form-group">
                                <label for="custom_address">Enter Specific Entrance/Address:</label>
                                <textarea name="custom_address" id="custom_address" class="form-control" placeholder="Enter your specific address or entrance details"></textarea>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Payment Method:</label>
                            <div class="payment-options">
                                <div class="payment-option" onclick="selectPaymentMethod(this, 'COD')">
                                    <i class="fas fa-money-bill-wave"></i>
                                    <div>Cash on Delivery</div>
                                </div>
                                <div class="payment-option" onclick="selectPaymentMethod(this, 'Card')">
                                    <i class="fas fa-credit-card"></i>
                                    <div>Card Payment</div>
                                </div>
                            </div>
                            <input type="hidden" name="payment_method" id="payment_method" value="COD">
                        </div>
                        
                        <div class="card-details" id="card-details">
                            <div class="form-group">
                                <label for="card_number">Card Number:</label>
                                <div class="form-input-wrapper">
                                    <input type="text" name="card_number" id="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" oninput="validateCardDetails(this, 'card_number')">
                                    <div class="card-icon"><i class="far fa-credit-card"></i></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="expiry_date">Expiry Date (MM/YY):</label>
                                <input type="text" name="expiry_date" id="expiry_date" class="form-control" placeholder="MM/YY" maxlength="5" oninput="formatExpiryDate(this)">
                            </div>
                            <div class="form-group">
                                <label for="cvv">CVV:</label>
                                <div class="form-input-wrapper">
                                    <input type="text" name="cvv" id="cvv" class="form-control" placeholder="123" maxlength="3" oninput="validateCardDetails(this, 'cvv')">
                                    <div class="card-icon"><i class="fas fa-question-circle" title="3-digit security code on the back of your card"></i></div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" id="submit-btn" name="place_order" class="btn">
                            <i class="fas fa-check-circle"></i> Place Order
                        </button>
                    </form>
                <?php elseif (isset($_POST['verify_payment']) && $_POST['payment_method'] == 'Card'): ?>
                    <!-- Step 2: OTP Verification for Card Payment -->
                    <h2><i class="fas fa-shield-alt"></i> Verification</h2>
                    <div class="otp-form">
                        <form method="POST" action="checkout.php">
                            <p style="text-align:center; margin-bottom:20px;">
                                <i class="fas fa-sms" style="font-size:40px; color:var(--primary-color); margin-bottom:15px;"></i><br>
                                We've sent a one-time password (OTP) to your registered mobile number.<br>
                                <span style="font-size:14px; color:#6c757d;">Please check the console for OTP (for demo purposes)</span>
                            </p>
                            
                            <div class="form-group">
                                <label for="otp">Enter 6-digit OTP:</label>
                                <input type="text" name="otp" id="otp" class="form-control" placeholder="Enter OTP" required>
                            </div>
                            
                            <div class="timer">
                                Time remaining: <span id="timer">05:00</span><br>
                                <a href="#" class="resend-link">Resend OTP</a>
                            </div>
                            
                            <!-- Pass along the form data from the previous step -->
                            <input type="hidden" name="address" value="<?php echo htmlspecialchars($_POST['address']); ?>">
                            <input type="hidden" name="custom_address" value="<?php echo isset($_POST['custom_address']) ? htmlspecialchars($_POST['custom_address']) : ''; ?>">
                            <input type="hidden" name="payment_method" value="<?php echo htmlspecialchars($_POST['payment_method']); ?>">
                            
                            <button type="submit" name="confirm_order" class="btn btn-success">
                                <i class="fas fa-check-circle"></i> Verify & Place Order
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set the first payment option as selected by default
            if (document.querySelector('.payment-option')) {
                document.querySelector('.payment-option').classList.add('selected');
            }
            
            // Start countdown timer for OTP
            const timerElement = document.getElementById('timer');
            if (timerElement) {
                let timeLeft = 5 * 60; // 5 minutes in seconds
                
                const countdown = setInterval(function() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    
                    timerElement.innerHTML = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                    
                    if (timeLeft <= 0) {
                        clearInterval(countdown);
                        timerElement.innerHTML = "00:00";
                        document.querySelector('.resend-link').style.display = 'inline-block';
                    }
                    
                    timeLeft--;
                }, 1000);
                
                // Resend OTP functionality
                document.querySelector('.resend-link').addEventListener('click', function(e) {
                    e.preventDefault();
                    // Generate new OTP and log to console
                    const newOTP = Math.floor(100000 + Math.random() * 900000);
                    console.log('New OTP: ' + newOTP);
                    
                    // Reset timer
                    timeLeft = 5 * 60;
                    timerElement.innerHTML = "05:00";
                    
                    alert('New OTP has been sent. Please check the console.');
                });
            }
        });
    </script>
</body>
</html>