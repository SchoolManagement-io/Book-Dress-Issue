<?php
// Start session
session_start();

// Determine user type for redirection
$user_type = $_SESSION['user_type'] ?? '';

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

// Redirect based on user type
if ($user_type === 'admin') {
    header('Location: admin_login.php?success=logout');
} elseif ($user_type === 'school') {
    header('Location: school_login.php?success=logout');
} else {
    header('Location: index.php');
}
exit;
?> 