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
if (!isset($_POST['school_id']) || !isset($_POST['password'])) {
    echo json_encode(['success' => false, 'message' => 'School ID and password are required']);
    exit;
}

// Get form data
$school_id = trim($_POST['school_id']);
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

    // Prepare and execute query to get school details
    $stmt = $mysqli->prepare("SELECT id, school_name, email, password, address, mobile FROM schools WHERE school_id = ?");
    $stmt->bind_param("s", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $response = ['success' => false, 'message' => 'Invalid school ID or password.'];
        echo json_encode($response);
        exit;
    }
    
    $school = $result->fetch_assoc();
    $stmt->close();
    
    // Direct string comparison as requested (no hashing)
    if ($password !== $school['password']) {
        // Log failed login attempt
        error_log("Failed login attempt for school ID: $school_id from IP: " . $_SERVER['REMOTE_ADDR']);
        
        $response = ['success' => false, 'message' => 'Invalid school ID or password.'];
        echo json_encode($response);
        exit;
    }
    
    // Set session variables
    $_SESSION['user_id'] = $school['id'];
    $_SESSION['user_type'] = 'school';
    $_SESSION['school_name'] = $school['school_name'];
    $_SESSION['school_email'] = $school['email'];
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
    
    // Update last login timestamp
    $user_id = $school['id'];
    $user_type = 'school';
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
        'redirect' => 'school_dashboard.php',
        'school' => [
            'name' => $school['school_name'],
            'email' => $school['email'],
            'address' => $school['address'],
            'phone' => $school['mobile']
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