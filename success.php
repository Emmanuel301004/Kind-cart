<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            text-align: center;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
        }
        .success-container {
            width: 90%;
            max-width: 400px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .success-logo {
            width: 80px;
            margin-bottom: 15px;
        }
        .success-message {
            font-size: 1.5rem;
            font-weight: bold;
            color: green;
        }
        .redirect-message {
            margin-top: 15px;
            font-size: 1rem;
            color: gray;
        }
    </style>
    <script>
        setTimeout(() => {
            window.location.href = "buy_books.php";
        }, 4000);
    </script>
</head>
<body>

<div class="success-container">
    <img src="success_logo.png" alt="Payment Successful" class="success-logo">
    <p class="success-message">Payment Successful!</p>
    <p class="redirect-message">Redirecting to books page in 4 seconds...</p>
</div>

</body>
</html>
