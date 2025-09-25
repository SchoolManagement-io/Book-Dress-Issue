<?php
session_start();
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Check if registration code is provided
if (!isset($_POST['registration_code']) || empty(trim($_POST['registration_code']))) {
    echo json_encode(['status' => 'error', 'message' => 'Registration code is required']);
    exit;
}

// Get and sanitize registration code
$registration_code = trim($_POST['registration_code']);

// Include database configuration
require_once 'config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if registration code is valid
    $stmt = $conn->prepare("SELECT id, code, expires_at FROM school_registration_codes WHERE code = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $registration_code);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        echo json_encode(['status' => 'error', 'message' => 'Invalid or expired registration code']);
        exit;
    }
    
    $code_data = $result->fetch_assoc();
    $stmt->close();
    $conn->close();
    
    // Return success response with code details
    echo json_encode([
        'status' => 'success', 
        'message' => 'Valid registration code',
        'expires_at' => $code_data['expires_at']
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("Registration code verification error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Return error response
    echo json_encode(['status' => 'error', 'message' => 'Server error, please try again later']);
    exit;
}
?> 