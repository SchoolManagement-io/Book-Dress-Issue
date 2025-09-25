<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in and is a parent
if (!isset($_SESSION['parent_id']) || $_SESSION['user_type'] !== 'parent') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'parent_login.php'
    ]);
    exit;
}

// Get parent ID from session
$parent_id = $_SESSION['parent_id'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get form type
$form_type = $_POST['form_type'] ?? '';

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Connect to database
try {
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset('utf8mb4');
    
    switch ($form_type) {
        case 'personal_info':
            handlePersonalInfoUpdate($conn, $parent_id);
            break;
            
        case 'password':
            handlePasswordUpdate($conn, $parent_id);
            break;
            
        case 'notifications':
            handleNotificationsUpdate($conn, $parent_id);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid form type']);
            break;
    }
    
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Profile update error: " . $e->getMessage());
    
    // Set error message in session
    $_SESSION['profile_update_error'] = 'An error occurred while updating your profile. Please try again later.';
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'redirect' => 'p_profile.php'
    ]);
    
    // Close mysqli if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}

/**
 * Handle personal information update
 */
function handlePersonalInfoUpdate($conn, $parent_id) {
    // Get form data
    $parent_name = trim($_POST['parent_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    
    // Validate required fields
    if (empty($parent_name) || empty($email) || empty($mobile)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    // Validate email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid email address'
        ]);
        return;
    }
    
    // Get parent database ID from parent_id
    $get_id_stmt = $conn->prepare("SELECT id FROM parents WHERE parent_id = ?");
    $get_id_stmt->bind_param("s", $parent_id);
    $get_id_stmt->execute();
    $id_result = $get_id_stmt->get_result();
    
    if ($id_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent not found'
        ]);
        return;
    }
    
    $db_id = $id_result->fetch_assoc()['id'];
    $get_id_stmt->close();
    
    // Update parent information
    $stmt = $conn->prepare("
        UPDATE parents 
        SET parent_name = ?, email = ?, mobile = ? 
        WHERE id = ?
    ");
    
    $stmt->bind_param("sssi", $parent_name, $email, $mobile, $db_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update profile: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Set success message in session
    $_SESSION['profile_update_success'] = 'Your profile has been updated successfully.';
    
    // Update session variables
    $_SESSION['parent_name'] = $parent_name;
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Profile updated successfully',
        'redirect' => 'p_profile.php'
    ]);
}

/**
 * Handle password update
 */
function handlePasswordUpdate($conn, $parent_id) {
    // Get form data
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        echo json_encode([
            'success' => false,
            'message' => 'All fields are required'
        ]);
        return;
    }
    
    // Validate new password
    if (strlen($new_password) < 8) {
        echo json_encode([
            'success' => false,
            'message' => 'Password must be at least 8 characters long'
        ]);
        return;
    }
    
    // Validate password confirmation
    if ($new_password !== $confirm_password) {
        echo json_encode([
            'success' => false,
            'message' => 'New password and confirm password do not match'
        ]);
        return;
    }
    
    // Get current password from database
    $stmt = $conn->prepare("SELECT id, password FROM parents WHERE parent_id = ?");
    $stmt->bind_param("s", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent not found'
        ]);
        return;
    }
    
    $parent = $result->fetch_assoc();
    $db_id = $parent['id'];
    $db_password = $parent['password'];
    
    $stmt->close();
    
    // Verify current password (direct comparison as per current system design)
    if ($current_password !== $db_password) {
        echo json_encode([
            'success' => false,
            'message' => 'Current password is incorrect'
        ]);
        return;
    }
    
    // Update password
    $update_stmt = $conn->prepare("UPDATE parents SET password = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_password, $db_id);
    
    if (!$update_stmt->execute()) {
        throw new Exception("Failed to update password: " . $update_stmt->error);
    }
    
    $update_stmt->close();
    
    // Set success message in session
    $_SESSION['profile_update_success'] = 'Your password has been updated successfully.';
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Password updated successfully',
        'redirect' => 'p_profile.php'
    ]);
}

/**
 * Handle notification settings update
 */
function handleNotificationsUpdate($conn, $parent_id) {
    // Get form data
    $email_notification = isset($_POST['email_notification']) ? 1 : 0;
    $sms_notification = isset($_POST['sms_notification']) ? 1 : 0;
    $order_notification = isset($_POST['order_notification']) ? 1 : 0;
    $promotion_notification = isset($_POST['promotion_notification']) ? 1 : 0;
    
    // Get parent database ID from parent_id
    $get_id_stmt = $conn->prepare("SELECT id FROM parents WHERE parent_id = ?");
    $get_id_stmt->bind_param("s", $parent_id);
    $get_id_stmt->execute();
    $id_result = $get_id_stmt->get_result();
    
    if ($id_result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent not found'
        ]);
        return;
    }
    
    $db_id = $id_result->fetch_assoc()['id'];
    $get_id_stmt->close();
    
    // Create notification settings JSON
    $notification_settings = json_encode([
        'email' => (bool)$email_notification,
        'sms' => (bool)$sms_notification,
        'order' => (bool)$order_notification,
        'promotion' => (bool)$promotion_notification
    ]);
    
    // Update notification settings
    $stmt = $conn->prepare("UPDATE parents SET notification_settings = ? WHERE id = ?");
    $stmt->bind_param("si", $notification_settings, $db_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update notification settings: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Set success message in session
    $_SESSION['profile_update_success'] = 'Your notification settings have been updated successfully.';
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Notification settings updated successfully',
        'redirect' => 'p_profile.php'
    ]);
}
?> 