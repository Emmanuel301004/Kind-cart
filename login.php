<?php
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Prepared statement to prevent SQL injection
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify the hashed password
    if (password_verify($password, $user['password'])) {
        // Start the session and store user information
        session_start();
        $_SESSION['user_id'] = $user['id'];  // Assuming 'id' is the user ID field
        $_SESSION['email'] = $user['email']; // Store email in session
        $_SESSION['capabilities'] = ucfirst($user['capabilities']); // Optional: store capabilities

        // Redirect to the dashboard after successful login
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Invalid password. Please try again.";
    }
} else {
    echo "No account found with this email. Please sign up.";
}

$stmt->close();
$conn->close();
?>
