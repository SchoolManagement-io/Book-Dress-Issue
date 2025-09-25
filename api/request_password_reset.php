<?php
session_start();
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Check if school_id is provided
if (!isset($_POST['school_id']) || empty(trim($_POST['school_id']))) {
    header('Location: ../school_forgot_password.php?error=invalid_input');
    exit;
}

$school_id = trim($_POST['school_id']);

// Validate school_id format
if (!preg_match('/^[A-Za-z0-9]{6,}$/', $school_id)) {
    header('Location: ../school_forgot_password.php?error=invalid_input');
    exit;
}

// Include database configuration
require_once 'config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if the school ID exists
    $stmt = $conn->prepare("SELECT id, school_name, email, status FROM schools WHERE school_id = ?");
    $stmt->bind_param("s", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // School not found
        $stmt->close();
        $conn->close();
        header('Location: ../school_forgot_password.php?error=not_found');
        exit;
    }

    $school = $result->fetch_assoc();
    $school_db_id = $school['id'];
    
    // Check if the school account is active
    if ($school['status'] !== 'active') {
        $stmt->close();
        $conn->close();
        header('Location: ../school_forgot_password.php?error=not_found');
        exit;
    }
    
    // Reset the password directly to "password123" for localhost usage
    $default_password = "password123";
    
    // Update the school's password
    $update_stmt = $conn->prepare("UPDATE schools SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $default_password, $school_db_id);
    $update_result = $update_stmt->execute();
    $update_stmt->close();
    
    if (!$update_result) {
        throw new Exception("Failed to reset password");
    }
    
    // Log the password reset in system_logs
    $action = 'password_reset_complete';
    $details = json_encode(['reset_method' => 'direct', 'ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
    
    $log_stmt = $conn->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, 'school', ?, ?, NOW())");
    $log_stmt->bind_param("iss", $school_db_id, $action, $details);
    $log_stmt->execute();
    $log_stmt->close();
    
    // Close database connection
    $conn->close();
    
    // Redirect to login page with success message
    header('Location: ../school_login.php?success=password_reset&default_password=true');
    exit;

} catch (Exception $e) {
    // Log the error
    error_log("Password reset error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Redirect with error
    header('Location: ../school_forgot_password.php?error=reset_error');
    exit;
} 