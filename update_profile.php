<?php
session_start();
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $_POST['name'];
$course = $_POST['course'];
$semester = $_POST['semester'];

// Update the database with new details
$sql = "UPDATE users SET name = ?, course = ?, semester = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssi", $name, $course, $semester, $user_id);

if ($stmt->execute()) {
    header("Location: profile.php?success=1");
    exit();
} else {
    echo "Error updating profile: " . $conn->error;
}

$stmt->close();
$conn->close();
?>
