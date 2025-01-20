<?php
include 'db.php';

// Get the email and password from the POST request
$email = $_POST['email'];
$password = $_POST['password'];

// Check if the email already exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Email already exists, verify the password and log in
    $user = $result->fetch_assoc();
    
    if (password_verify($password, $user['password'])) {
        // Start session or redirect to dashboard
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        echo "Welcome back, " . htmlspecialchars($user['email']) . "!";
        // Redirect to dashboard or homepage (uncomment the next line if needed)
        // header("Location: dashboard.php");
    } else {
        echo "Email already exists, but the password is incorrect.";
    }
} else {
    // Email does not exist, create a new account
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (email, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $hashedPassword);

    if ($stmt->execute()) {
        // Automatically log in the user after signup
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['email'] = $email;
        echo "Account created successfully and logged in!";
        // Redirect to dashboard or homepage (uncomment the next line if needed)
        // header("Location: dashboard.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
