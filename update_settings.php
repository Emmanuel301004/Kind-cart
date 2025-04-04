<?php
session_start();
include 'db.php'; // Database connection file

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    error_log("No session user_id found in update_settings.php");
    header("Location: index.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Validate form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Form validation
    if (empty($current_password)) {
        $response['message'] = 'Current password is required';
    } elseif (empty($new_password)) {
        $response['message'] = 'New password is required';
    } elseif ($new_password !== $confirm_password) {
        $response['message'] = 'New passwords do not match';
    } elseif (strlen($new_password) < 8) {
        $response['message'] = 'Password must be at least 8 characters long';
    } else {
        // Get the user's current password from the database
        $sql = "SELECT password FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->bind_result($stored_password);
        $stmt->fetch();
        $stmt->close();
        
        // Verify current password
        if (password_verify($current_password, $stored_password)) {
            // Hash the new password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Update the password in the database
            $update_sql = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("si", $hashed_password, $user_id);
            
            if ($update_stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Password updated successfully';
                
                // Log the password change
                error_log("Password updated for user ID: $user_id");
            } else {
                $response['message'] = 'Error updating password: ' . $conn->error;
                error_log("Error updating password for user ID: $user_id - " . $conn->error);
            }
            
            $update_stmt->close();
        } else {
            $response['message'] = 'Current password is incorrect';
        }
    }
    
    // Handle response
    if ($response['success']) {
        // Set success message in session
        $_SESSION['settings_success'] = $response['message'];
    } else {
        // Set error message in session
        $_SESSION['settings_error'] = $response['message'];
    }
    
    // Redirect back to profile page with settings tab active
    header("Location: profile.php?tab=settings");
    exit();
} else {
    // If not POST request, redirect to profile page
    header("Location: profile.php");
    exit();
}
?>