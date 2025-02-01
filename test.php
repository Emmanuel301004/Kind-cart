<?php
session_start();
include 'db.php'; // Database connection file
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("No session user_id found."); // Log an error if user_id is not found in the session
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetching user data from the database
$sql = "SELECT name, email, course, semester FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($user_name, $email, $course, $semester);
if (!$stmt->fetch()) {
    error_log("No data found for user ID: $user_id"); // Log if no data is fetched
    $user_name = $email = $course = $semester = ''; // Set default empty values
}
echo "hello mame";
echo "pokemon";
$stmt->close();
?>