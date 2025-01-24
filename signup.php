<?php
include 'db.php';

// Get the email, password, and capabilities from the POST request
$email = $_POST['email'];
$password = $_POST['password'];
$capabilities = isset($_POST['capabilities']) ? $_POST['capabilities'] : null;

// Check if the email already exists in the database
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Email already exists.";
} else {
    // Hash the password and insert new user into the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password, capabilities) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $hashedPassword, $capabilities);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id;
        $_SESSION['email'] = $email;
        $_SESSION['capabilities'] = ucfirst($capabilities); 

        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
?>
