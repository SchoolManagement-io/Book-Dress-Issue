<?php
session_start();

// Check if user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: ../admin_login.php");
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin_dashboard.php?error=invalid_request');
    exit;
}

// Get expiration days from form, default to 7 if not set
$expiration_days = isset($_POST['expiration_days']) ? (int)$_POST['expiration_days'] : 7;

// Validate expiration days (between 1 and 30)
if ($expiration_days < 1 || $expiration_days > 30) {
    $expiration_days = 7;
}

// Generate unique registration code
function generateRegistrationCode() {
    $prefix = 'REG';
    $random_part = bin2hex(random_bytes(4)); // 8 characters
    return $prefix . $random_part;
}

// Calculate expiration date
$expires_at = date('Y-m-d H:i:s', strtotime("+{$expiration_days} days"));

// Include database configuration
require_once 'config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Generate a unique code
    $registration_code = generateRegistrationCode();
    $admin_id = $_SESSION['user_id'];
    
    // Insert code into database
    $stmt = $conn->prepare("INSERT INTO school_registration_codes (code, created_by, expires_at, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sis", $registration_code, $admin_id, $expires_at);
    
    if ($stmt->execute()) {
        // Log activity
        $log_details = "Registration code {$registration_code} created (valid for {$expiration_days} days)";
        $log_stmt = $conn->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, 'admin', 'registration_code_generated', ?, NOW())");
        $log_stmt->bind_param("is", $admin_id, $log_details);
        $log_stmt->execute();
        $log_stmt->close();
        
        // Success
        $_SESSION['generated_code'] = $registration_code;
        $_SESSION['code_expires_at'] = $expires_at;
        header('Location: ../admin_dashboard.php?success=code_generated');
    } else {
        // Error
        header('Location: ../admin_dashboard.php?error=generation_failed');
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Registration code generation error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Redirect with error
    header('Location: ../admin_dashboard.php?error=server_error');
}

exit;
?> 