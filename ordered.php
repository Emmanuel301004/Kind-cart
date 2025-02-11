<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f5f5f5;
            padding: 50px;
        }
        .container {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: inline-block;
        }
        h1 {
            color: #2ecc71;
        }
        p {
            font-size: 18px;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "buy_books.php";
        }, 3000); // Redirect after 3 seconds
    </script>
</head>
<body>
    <div class="container">
        <h1>Payment Successful</h1>
        <p>Your order has been placed successfully.</p>
        <p>Redirecting to the Buy Books page...</p>
    </div>
</body>
</html>
