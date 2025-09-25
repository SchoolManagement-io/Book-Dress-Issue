<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in
$authenticated = false;
$user_type = null;
$response = [];

// Check for session expiration
$session_timeout = 3600; // 1 hour in seconds
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $session_timeout)) {
    // Session has expired
    session_unset();
    session_destroy();
    session_start();
    
    echo json_encode([
        'authenticated' => false,
        'user_type' => null,
        'session_expired' => true,
        'message' => 'Your session has expired. Please log in again.'
    ]);
    exit;
}

// Update last activity time stamp
$_SESSION['last_activity'] = time();

if (isset($_SESSION['user_type'])) {
    $authenticated = true;
    $user_type = $_SESSION['user_type'];
    
    // Add user-specific information based on user type
    if ($user_type === 'school') {
        $response = [
            'authenticated' => true,
            'user_type' => 'school',
            'user_id' => $_SESSION['user_id'] ?? null,
            'school_name' => $_SESSION['school_name'] ?? null,
            'school_email' => $_SESSION['school_email'] ?? null,
            'last_activity' => $_SESSION['last_activity'] ?? null,
            'inactive_time' => $inactive_time ?? 0
        ];
    } elseif ($user_type === 'parent') {
        $response = [
            'authenticated' => true,
            'user_type' => 'parent',
            'parent_id' => $_SESSION['parent_id'] ?? null,
            'parent_name' => $_SESSION['parent_name'] ?? null,
            'parent_db_id' => $_SESSION['parent_db_id'] ?? null,
            'parent_email' => $_SESSION['parent_email'] ?? null
        ];
    } elseif ($user_type === 'admin') {
        $response = [
            'authenticated' => true,
            'user_type' => 'admin',
            'user_id' => $_SESSION['user_id'] ?? null,
            'admin_email' => $_SESSION['admin_email'] ?? null,
            'admin_username' => $_SESSION['admin_username'] ?? null
        ];
    } else {
        $response = [
            'authenticated' => true,
            'user_type' => $user_type
        ];
    }
} else {
    // User is not logged in
    $response = [
        'authenticated' => false,
        'user_type' => null,
        'message' => 'Not logged in'
    ];
}

// Return JSON response
echo json_encode($response);
?>