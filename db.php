<?php
$host = 'localhost';
$db = 'user_management';
$user = 'root';
$password = '';  // Change this to the password if needed (e.g. 'root' or whatever you set)

// Try adding password here if you have one.
$conn = new mysqli($host, $user, $password, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
