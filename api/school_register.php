<?php
session_start();
header('Content-Type: application/json');

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Required fields
$required_fields = ['registration_code', 'school_name', 'school_id', 'email', 'phone', 'address', 'password', 'confirm_password', 'terms'];

// Check if all required fields are provided
$missing_fields = [];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
        // Terms is a checkbox, so we handle it differently
        if ($field !== 'terms' || !isset($_POST['terms'])) {
            $missing_fields[] = $field;
        }
    }
}

if (count($missing_fields) > 0) {
    header('Location: ../school_register.php?error=invalid_data');
    exit;
}

// Get and sanitize input data
$registration_code = trim($_POST['registration_code']);
$school_name = trim($_POST['school_name']);
$school_id = trim($_POST['school_id']);
$email = trim($_POST['email']);
$phone = trim($_POST['phone']);
$address = trim($_POST['address']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validate school_id format (alphanumeric, min 6 chars)
if (!preg_match('/^[A-Za-z0-9]{6,}$/', $school_id)) {
    header('Location: ../school_register.php?error=invalid_data');
    exit;
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../school_register.php?error=invalid_data');
    exit;
}

// Check if passwords match
if ($password !== $confirm_password) {
    header('Location: ../school_register.php?error=invalid_data');
    exit;
}

// Check password complexity (minimum 8 characters)
if (strlen($password) < 8) {
    header('Location: ../school_register.php?error=invalid_data');
    exit;
}

// Include database configuration
require_once 'config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Check if registration code is valid
    $code_stmt = $conn->prepare("SELECT id FROM school_registration_codes WHERE code = ? AND used = 0 AND expires_at > NOW()");
    $code_stmt->bind_param("s", $registration_code);
    $code_stmt->execute();
    $code_result = $code_stmt->get_result();
    
    if ($code_result->num_rows === 0) {
        $code_stmt->close();
        $conn->close();
        header('Location: ../school_register.php?error=invalid_code');
        exit;
    }
    
    $code_row = $code_result->fetch_assoc();
    $registration_code_id = $code_row['id'];
    $code_stmt->close();

    // Check if school_id already exists
    $check_id_stmt = $conn->prepare("SELECT id FROM schools WHERE school_id = ?");
    $check_id_stmt->bind_param("s", $school_id);
    $check_id_stmt->execute();
    $id_result = $check_id_stmt->get_result();
    
    if ($id_result->num_rows > 0) {
        $check_id_stmt->close();
        $conn->close();
        header('Location: ../school_register.php?error=id_exists');
        exit;
    }
    $check_id_stmt->close();

    // Check if email already exists
    $check_email_stmt = $conn->prepare("SELECT id FROM schools WHERE email = ?");
    $check_email_stmt->bind_param("s", $email);
    $check_email_stmt->execute();
    $email_result = $check_email_stmt->get_result();
    
    if ($email_result->num_rows > 0) {
        $check_email_stmt->close();
        $conn->close();
        header('Location: ../school_register.php?error=email_exists');
        exit;
    }
    $check_email_stmt->close();

    // Set default values
    $status = 'active';
    $created_at = date('Y-m-d H:i:s');

    // Insert new school record with simplified schema
    $insert_stmt = $conn->prepare("
        INSERT INTO schools (
            school_id, school_name, email, mobile, address, 
            password, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $insert_stmt->bind_param(
        "ssssssss",
        $school_id, $school_name, $email, $phone, $address,
        $password, $status, $created_at
    );
    
    $result = $insert_stmt->execute();
    
    if (!$result) {
        throw new Exception("Failed to register school: " . $conn->error);
    }
    
    $school_db_id = $insert_stmt->insert_id;
    $insert_stmt->close();

    // Mark registration code as used
    $update_code_stmt = $conn->prepare("UPDATE school_registration_codes SET used = 1, used_at = NOW(), used_by_school = ? WHERE id = ?");
    $update_code_stmt->bind_param("ii", $school_db_id, $registration_code_id);
    $update_code_stmt->execute();
    $update_code_stmt->close();

    // Log in the system logs
    $log_action = "school_registered";
    $log_details = "School {$school_name} ({$school_id}) registered with code {$registration_code}";
    $log_stmt = $conn->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, 'school', ?, ?, NOW())");
    $log_stmt->bind_param("iss", $school_db_id, $log_action, $log_details);
    $log_stmt->execute();
    $log_stmt->close();

    // Close database connection
    $conn->close();

    // Send welcome email
    $to = $email;
    $subject = 'Welcome to Samridhi Book Dress';
    
    $message = "
    <html>
    <head>
        <title>Welcome to Samridhi Book Dress</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #E91E63; color: white; padding: 10px 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .button { display: inline-block; padding: 10px 20px; background-color: #E91E63; color: white; text-decoration: none; border-radius: 5px; }
            .footer { padding: 20px; text-align: center; font-size: 12px; color: #777; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Samridhi Book Dress - Welcome!</h2>
            </div>
            <div class='content'>
                <p>Dear $school_name,</p>
                <p>Thank you for registering with Samridhi Book Dress. Your account has been created successfully.</p>
                <p>Here are your account details:</p>
                <ul>
                    <li><strong>School ID:</strong> $school_id</li>
                    <li><strong>Email:</strong> $email</li>
                </ul>
                <p>You can now log in to your dashboard and start managing your inventory, students, and orders.</p>
                <p style='text-align: center;'>
                    <a href='https://" . $_SERVER['HTTP_HOST'] . "/school_login.php' class='button'>Login to Dashboard</a>
                </p>
                <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                <p>Regards,<br>Samridhi Book Dress Team</p>
            </div>
            <div class='footer'>
                <p>&copy; " . date('Y') . " Samridhi Book Dress. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Set content-type header for sending HTML email
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: Samridhi Book Dress <noreply@samridhibookdress.com>' . "\r\n";
    
    // Try to send email, but don't block registration if email fails
    try {
        mail($to, $subject, $message, $headers);
    } catch (Exception $e) {
        error_log("Failed to send welcome email: " . $e->getMessage());
    }

    // Redirect to success page
    header('Location: ../school_login.php?success=registration_complete');
    exit;

} catch (Exception $e) {
    // Log the error
    error_log("Registration error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Redirect with error
    header('Location: ../school_register.php?error=db_error');
    exit;
} 