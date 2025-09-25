<?php
session_start();
header('Content-Type: application/json');

// Enable error logging
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Required fields
$required_fields = ['admin_email', 'password'];

// Check if all required fields are provided
$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        $missing_fields[] = $field;
    }
}

if (count($missing_fields) > 0) {
    echo json_encode(['success' => false, 'message' => 'Email and password are required']);
    exit;
}

// Get and sanitize input data
$admin_email = trim($_POST['admin_email']);
$password = $_POST['password'];

// Log request data (for debugging only - remove in production)
error_log("Admin login attempt: " . $admin_email);

// Include database configuration
require_once 'config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Prepare statement to check if admin exists
    $stmt = $conn->prepare("SELECT id, email, password, username FROM admins WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("s", $admin_email);
    if (!$stmt->execute()) {
        throw new Exception("Execute failed: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        error_log("Admin not found: " . $admin_email);
        $stmt->close();
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    // Debug log
    error_log("Found admin: " . $admin['username'] . ", comparing passwords");
    
    // Direct string comparison (as requested, no hashing)
    if ($password !== $admin['password']) {
        error_log("Password mismatch for: " . $admin_email);
        $conn->close();
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit;
    }
    
    // Log successful authentication
    error_log("Admin authenticated successfully: " . $admin_email);
    
    // Set session variables
    $_SESSION['user_id'] = $admin['id'];
    $_SESSION['user_type'] = 'admin';
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['last_activity'] = time();
    
    // Log activity
    $log_stmt = $conn->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $user_id = $admin['id'];
    $user_type = 'admin';
    $action = 'login';
    $details = json_encode(['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
    $log_stmt->bind_param("isss", $user_id, $user_type, $action, $details);
    $log_stmt->execute();
    $log_stmt->close();
    
    // Close connection
    $conn->close();
    
    // Return JSON success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'admin_dashboard.php',
        'admin' => [
            'username' => $admin['username'],
            'email' => $admin['email']
        ]
    ]);
    exit;

} catch (Exception $e) {
    // Log error
    error_log("Admin login error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Return error response
    echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    exit;
} 