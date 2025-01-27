<?php
include 'db.php';

<<<<<<< HEAD
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
=======
// Get the email, password, and capabilities from the POST request
$email = $_POST['email'];
$password = $_POST['password'];
$capabilities = isset($_POST['capabilities']) ? $_POST['capabilities'] : null;
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344

// Check if the email already exists in the database
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "Email already exists.";
} else {
<<<<<<< HEAD
    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new user into the database
    $sql = "INSERT INTO users (email, password, name, course, semester) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssss", $email, $hashedPassword, $name, $course, $semester);

    if ($stmt->execute()) {
        // Start session and store user data
=======
    // Hash the password and insert new user into the database
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (email, password, capabilities) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $hashedPassword, $capabilities);

    if ($stmt->execute()) {
>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
        session_start();
        $_SESSION['user_id'] = $stmt->insert_id; // Auto-increment ID
        $_SESSION['email'] = $email;
<<<<<<< HEAD
        $_SESSION['name'] = $name;
        $_SESSION['course'] = $course;
        $_SESSION['semester'] = $semester;

        // Redirect to dashboard
=======
        $_SESSION['capabilities'] = ucfirst($capabilities); 

>>>>>>> 8253c1ffbe1d13399d83cb5bcac285c931b16344
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
