<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Check for required POST data
if (!isset($_POST['parent_id']) || !isset($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'Parent ID and password are required']);
    exit;
}

// Get form data
$parent_id = trim($_POST['parent_id']);
$password = $_POST['password'];
$remember_me = isset($_POST['remember_me']) ? (bool)$_POST['remember_me'] : false;

// Include database configuration
require_once 'config.php';

// Connect to database
try {
    $mysqli = getDbConnection();
    
    if (!$mysqli) {
        throw new Exception("Database connection failed");
    }

    // Prepare and execute query to get parent details
    $stmt = $mysqli->prepare("SELECT id, parent_name, email, password, mobile FROM parents WHERE parent_id = ?");
    $stmt->bind_param("s", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response = ['success' => false, 'message' => 'Invalid parent ID or password.'];
        echo json_encode($response);
        exit;
    }
    
    $parent = $result->fetch_assoc();
    $stmt->close();
    
    // Direct string comparison as requested (no hashing)
    if ($password !== $parent['password']) {
        // Log failed login attempt
        error_log("Failed login attempt for parent ID: $parent_id from IP: " . $_SERVER['REMOTE_ADDR']);
        
        $response = ['success' => false, 'message' => 'Invalid parent ID or password.'];
        echo json_encode($response);
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $parent['id'];
    $_SESSION['parent_id'] = $parent_id;
    $_SESSION['user_type'] = 'parent';
    $_SESSION['parent_name'] = $parent['parent_name'];
    $_SESSION['parent_email'] = $parent['email'];
    $_SESSION['last_activity'] = time();
    
    // If remember me is checked, set longer session expiry
    if ($remember_me) {
        // Set cookie to expire in 30 days
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            session_id(),
            time() + 60*60*24*30,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    
    // Log successful login
    $user_id = $parent['id'];
    $user_type = 'parent';
    $action = 'login';
    $details = json_encode(['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);

    $log_stmt = $mysqli->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, ?, ?, ?, NOW())");
    $log_stmt->bind_param("isss", $user_id, $user_type, $action, $details);
    $log_stmt->execute();
    $log_stmt->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'redirect' => 'parent_dashboard.php',
        'parent' => [
            'name' => $parent['parent_name'],
            'email' => $parent['email'],
            'phone' => $parent['mobile']
        ]
    ]);
    
    // Close connection
    $mysqli->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Login error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred. Please try again later.'
    ]);
    
    // Close mysqli if it exists
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 