<?php
include 'db.php';

// Get the form data
$email = $_POST['email'];
$password = $_POST['password'];
$name = isset($_POST['name']) ? $_POST['name'] : null;
$course = isset($_POST['course']) ? $_POST['course'] : null;
$semester = isset($_POST['semester']) ? $_POST['semester'] : null;

// Validate email and password
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "Invalid email address.";
    exit();
}

if (empty($password) || strlen($password) < 8) {
    echo "Password must be at least 8 characters long.";
    exit();
}

// Check if the email already exists
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Email already exists.";
} else {
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (email, password, name, course, semester) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $email, $hashedPassword, $name, $course, $semester);

    if ($stmt->execute()) {
        // Start session and store user data
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id; // Auto-increment ID
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $_SESSION['course'] = $course;
        $_SESSION['semester'] = $semester;

        // Redirect to dashboard
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Close the statement and connection
$stmt->close();
$conn->close();
?>
