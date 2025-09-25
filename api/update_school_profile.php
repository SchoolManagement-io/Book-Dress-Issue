<?php
session_start();

// Check if the user is logged in as a school
if (!isset($_SESSION['school_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: ../school_login.php');
    exit;
}

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

try {
    // Connect to the database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Set the character set to UTF-8
    $conn->set_charset('utf8mb4');

    // Check which form is being updated
    $form_type = $_POST['form_type'] ?? '';

    $school_id = $_SESSION['school_id'];
    
    // Handle different form submissions
    switch ($form_type) {
        case 'basic':
            updateBasicInfo($conn, $school_id);
            break;
            
        case 'password':
            updatePassword($conn, $school_id);
            break;
            
        case 'address':
            updateAddress($conn, $school_id);
            break;
            
        case 'notifications':
            updateNotifications($conn, $school_id);
            break;
            
        default:
            throw new Exception("Invalid form type");
    }
    
    // Close database connection
    $conn->close();
    
} catch (Exception $e) {
    // Log the error
    error_log("Profile update error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Set error message in session and redirect back
    $_SESSION['profile_update_error'] = $e->getMessage();
    header('Location: ../school_edit_profile.php');
    exit;
}

// Function to update basic info
function updateBasicInfo($conn, $school_id) {
    // Get form data
    $school_name = trim($_POST['school_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    
    // Validate required fields
    if (empty($school_name) || empty($email) || empty($phone)) {
        $response = [
            'success' => false,
            'message' => 'Please fill all required fields'
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response = [
            'success' => false,
            'message' => 'Please enter a valid email address'
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // Update school profile
    $sql = "UPDATE schools SET 
            school_name = ?, 
            email = ?, 
            phone = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $school_name, $email, $phone, $school_id);
    
    if ($stmt->execute()) {
        $_SESSION['school_name'] = $school_name;
        
        $response = [
            'success' => true,
            'message' => 'Profile updated successfully'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to update profile'
        ];
    }
    
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Function to update password
function updatePassword($conn, $school_id) {
    // Get form data
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Handle password change
    if (!empty($current_password) && !empty($new_password) && !empty($confirm_password)) {
        // Get current password from database
        $stmt = $conn->prepare("SELECT password FROM schools WHERE school_id = ?");
        $stmt->bind_param("s", $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $school = $result->fetch_assoc();
        $stmt->close();
        
        $password_stored = $school['password'];
        
        // Check if current password is correct
        if ($current_password !== $password_stored) {
            $result = [
                'success' => false,
                'message' => 'Current password is incorrect.'
            ];
            echo json_encode($result);
            exit;
        }
        
        // Check if new passwords match
        if ($new_password !== $confirm_password) {
            $result = [
                'success' => false,
                'message' => 'New passwords do not match.'
            ];
            echo json_encode($result);
            exit;
        }
        
        // Update password
        $update_stmt = $conn->prepare("UPDATE schools SET password = ? WHERE school_id = ?");
        $update_stmt->bind_param("ss", $new_password, $school_id);
        if (!$update_stmt->execute()) {
            $result = [
                'success' => false,
                'message' => 'Failed to update password: ' . $conn->error
            ];
            echo json_encode($result);
            exit;
        }
        $update_stmt->close();
        
        $password_updated = true;
    }
    
    // Log activity
    logActivity($conn, $school_id, 'password_change', [
        'time' => date('Y-m-d H:i:s')
    ]);
    
    // Set success message and redirect
    $_SESSION['profile_update_success'] = "Password updated successfully";
    header('Location: ../school_edit_profile.php');
    exit;
}

// Function to update address
function updateAddress($conn, $school_id) {
    // Get form data
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    if (empty($address)) {
        $response = [
            'success' => false,
            'message' => 'Please fill all required fields'
        ];
        
        echo json_encode($response);
        exit;
    }
    
    // Update school address
    $sql = "UPDATE schools SET address = ? WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $address, $school_id);
    
    if ($stmt->execute()) {
        $response = [
            'success' => true,
            'message' => 'Address updated successfully'
        ];
    } else {
        $response = [
            'success' => false,
            'message' => 'Failed to update address'
        ];
    }
    
    $stmt->close();
    
    echo json_encode($response);
    exit;
}

// Function to update notification settings
function updateNotifications($conn, $school_id) {
    // Get form data
    $email_notification = isset($_POST['email_notification']) ? true : false;
    $sms_notification = isset($_POST['sms_notification']) ? true : false;
    $order_notification = isset($_POST['order_notification']) ? true : false;
    $payment_notification = isset($_POST['payment_notification']) ? true : false;
    $student_notification = isset($_POST['student_notification']) ? true : false;
    
    // Create notification settings JSON
    $notification_settings = json_encode([
        'email' => $email_notification,
        'sms' => $sms_notification,
        'order' => $order_notification,
        'payment' => $payment_notification,
        'student' => $student_notification
    ]);
    
    // Update notification settings
    $update_stmt = $conn->prepare("UPDATE schools SET notification_settings = ? WHERE school_id = ?");
    $update_stmt->bind_param("ss", $notification_settings, $school_id);
    $result = $update_stmt->execute();
    $update_stmt->close();
    
    if (!$result) {
        throw new Exception("Failed to update notification settings");
    }
    
    // Log activity
    logActivity($conn, $school_id, 'notification_settings_update', [
        'settings' => $notification_settings
    ]);
    
    // Set success message and redirect
    $_SESSION['profile_update_success'] = "Notification settings updated successfully";
    header('Location: ../school_edit_profile.php');
    exit;
}

// Function to log activity
function logActivity($conn, $school_id, $action, $details) {
    $db_id = 0;
    
    // Get database ID for the school
    $stmt = $conn->prepare("SELECT id FROM schools WHERE school_id = ?");
    $stmt->bind_param("s", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $school = $result->fetch_assoc();
        $db_id = $school['id'];
    }
    
    $stmt->close();
    
    // Convert details to JSON
    $details_json = json_encode($details);
    
    // Log the activity
    $log_stmt = $conn->prepare("
        INSERT INTO system_logs (user_id, user_type, action, details, timestamp)
        VALUES (?, 'school', ?, ?, NOW())
    ");
    
    $log_stmt->bind_param("iss", $db_id, $action, $details_json);
    $log_stmt->execute();
    $log_stmt->close();
} 