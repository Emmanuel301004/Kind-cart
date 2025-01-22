<?php
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];
$capabilities = $_POST['capabilities'];

$sql = "INSERT INTO users (email, password, capabilities) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $email, $password, $capabilities);

if ($stmt->execute()) {
    echo "Account created successfully.";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
