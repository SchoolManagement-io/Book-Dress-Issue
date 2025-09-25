<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_type']);

// Get user type and ID for logging
$user_type = $_SESSION['user_type'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

// Log logout event if user was logged in
if ($is_logged_in && $user_id) {
    require_once 'config.php';
    $mysqli = getDbConnection();
    
    if ($mysqli) {
        $action = 'logout';
        $details = json_encode(['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
        
        $log_stmt = $mysqli->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
        $log_stmt->bind_param("isss", $user_id, $user_type, $action, $details);
        $log_stmt->execute();
        $log_stmt->close();
        
        $mysqli->close();
    }
}

// Clear all session variables
$_SESSION = [];

// If a session cookie exists, destroy it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Return success response
echo json_encode([
    'success' => true,
    'message' => 'Logged out successfully',
    'was_logged_in' => $is_logged_in,
    'user_type' => $user_type,
    'redirect' => 'index.php'
]);

exit; 