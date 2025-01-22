<?php
session_start();

// Check if the user is logged in, otherwise redirect to login page
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
    <title>Dashboard</title>
    <style>
        /* General Body Styling */
        body {
            background-color: #1e1e1e;
            color: white;
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the Dashboard */
        .dashboard-container {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            padding: 30px 40px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.5);
            text-align: center;
            width: 90%;
            max-width: 500px;
        }

        /* Heading */
        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #4CAF50;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        /* Welcome Message */
        .welcome-message {
            font-size: 18px;
            margin-bottom: 30px;
            color: #f1f1f1;
        }

        /* Button */
        .logout-btn {
            padding: 12px 20px;
            background-color: #FF6347;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #e55347;
        }

        /* Responsive Design for smaller screens */
        @media (max-width: 600px) {
            .dashboard-container {
                padding: 20px 25px;
            }

            h1 {
                font-size: 28px;
            }

            .welcome-message {
                font-size: 16px;
            }

            .logout-btn {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>

<div class="dashboard-container">
    <h1>Welcome to Your Dashboard</h1>
    <p class="welcome-message">
        Welcomes, <?php echo htmlspecialchars($_SESSION['email']); ?>!
    </p>
    <a href="logout.php">
        <button class="logout-btn">Logout</button>
    </a>
</div>

</body>
</html>
