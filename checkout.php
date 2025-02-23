<?php
session_start();
include "db.php"; // Database connection

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address']; // Get the address from the form

    if (empty($address)) {
        echo "<script>alert('Please enter your delivery address!');</script>";
    } else {
        if ($payment_method == "card") {
            $card_number = $_POST['card_number'];
            $card_name = $_POST['card_name'];
            $expiry = $_POST['expiry'];
            $cvv = $_POST['cvv'];

            if (empty($card_number) || empty($card_name) || empty($expiry) || empty($cvv)) {
                echo "<script>alert('Please fill in all card details!');</script>";
            } else {
                // Dummy Payment Processing (Replace with actual payment gateway logic)
                $payment_success = true;

                if ($payment_success) {
                    processOrder($conn, $user_id, $address);
                } else {
                    echo "<script>alert('Payment Failed! Try again.');</script>";
                }
            }
        } elseif ($payment_method == "cod") {
            processOrder($conn, $user_id, $address);
        }
    }
}

function processOrder($conn, $user_id, $address) {
    // Fetch book details from cart and books table
    $bookDetailsQuery = "SELECT books.book_id, books.title, books.owner_name, books.contact, books.course, books.semester, books.book_condition, books.price 
                         FROM cart 
                         JOIN books ON cart.book_id = books.book_id 
                         WHERE cart.user_id = '$user_id'";
    
    $bookDetailsResult = $conn->query($bookDetailsQuery);

    if ($bookDetailsResult->num_rows > 0) {
        $order_id = uniqid(); // Generate a unique order ID

        while ($row = $bookDetailsResult->fetch_assoc()) {
            $book_id = $row['book_id'];
            $title = $row['title'];
            $owner_name = $row['owner_name'];
            $contact = $row['contact'];
            $course = $row['course'];
            $semester = $row['semester'];
            $book_condition = $row['book_condition'];
            $price = $row['price'];

            // Insert into order_items table
            $insertOrderItem = "INSERT INTO order_items (order_id, book_id, book_title, book_price, quantity) 
                                VALUES ('$order_id', '$book_id', '$title', '$price', 1)";
            if (!$conn->query($insertOrderItem)) {
                echo "<script>alert('Error adding items to order.');</script>";
                return;
            }

            // Insert into orders table
            $insertOrder = "INSERT INTO orders (order_id, user_id, book_id, book_title, owner_name, contact, course, semester, book_condition, book_price, order_date, address) 
                            VALUES ('$order_id', '$user_id', '$book_id', '$title', '$owner_name', '$contact', '$course', '$semester', '$book_condition', '$price', NOW(), '$address')";
            if (!$conn->query($insertOrder)) {
                echo "<script>alert('Error placing order.');</script>";
                return;
            }
        }

        // Remove books from inventory
        $deleteBooks = "DELETE FROM books WHERE book_id IN (SELECT book_id FROM cart WHERE user_id = '$user_id')";
        if (!$conn->query($deleteBooks)) {
            echo "<script>alert('Error removing books from inventory.');</script>";
        }

        // Clear the cart
        $conn->query("DELETE FROM cart WHERE user_id = '$user_id'");

        echo "<script>alert('Payment Successful! Your order has been placed.'); window.location='success.php';</script>";
        exit();
    } else {
        echo "<script>alert('No items in the cart!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .checkout-container {
            width: 50%;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        textarea, input {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .radio-group {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
        }

        .card-details {
            display: none;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<div class="checkout-container">
    <h2>Checkout</h2>
    <form method="POST">
        <label>Delivery Address:</label>
        <textarea name="address" required></textarea>

        <label>Payment Method:</label>
        <div class="radio-group">
            <input type="radio" name="payment_method" value="cod" required onclick="toggleCardDetails(false)"> Cash on Delivery (COD)
            <input type="radio" name="payment_method" value="card" required onclick="toggleCardDetails(true)"> Credit/Debit Card
        </div>

        <div class="card-details">
            <label>Card Number:</label>
            <input type="text" name="card_number" placeholder="1234 5678 9012 3456">
            <label>Cardholder Name:</label>
            <input type="text" name="card_name" placeholder="John Doe">
            <label>Expiry Date:</label>
            <input type="text" name="expiry" placeholder="MM/YY">
            <label>CVV:</label>
            <input type="text" name="cvv" placeholder="123">
        </div>

        <button type="submit">Place Order</button>
    </form>
</div>

<script>
    function toggleCardDetails(show) {
        const cardDetails = document.querySelector('.card-details');
        if (show) {
            cardDetails.style.display = "block";
        } else {
            cardDetails.style.display = "none";
        }
    }
</script>

</body>
</html>
