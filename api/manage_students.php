<?php
session_start();

// Check if user is authenticated as a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'school_login.php'
    ]);
    exit;
}

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Initialize variables
$school_id = $_SESSION['user_id'];
$response = ['success' => false, 'message' => ''];

// Define allowed actions
$allowed_actions = ['add', 'edit', 'delete'];

// Determine action type
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if (!in_array($action, $allowed_actions)) {
    $_SESSION['student_action_error'] = "Invalid action requested.";
    header("Location: ../school_students.php");
    exit;
}

try {
    // Connect to the database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set the character set to UTF-8
    $conn->set_charset('utf8mb4');
    
    // Handle each action type
    switch ($action) {
        case 'add':
            addStudent($conn, $school_id);
            break;
            
        case 'edit':
            editStudent($conn, $school_id);
            break;
            
        case 'delete':
            deleteStudent($conn, $school_id);
            break;
    }
    
    // Close the connection
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Student management error: " . $e->getMessage());
    
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Set error message
    $_SESSION['student_action_error'] = "An error occurred: " . $e->getMessage();
    header("Location: ../school_students.php");
    exit;
}

/**
 * Add a new student
 * 
 * @param mysqli $conn Database connection
 * @param string $school_id School ID
 */
function addStudent($conn, $school_id) {
    // Validate required fields
    $required_fields = ['student_name', 'parent_name', 'email', 'mobile', 'password', 'class', 'parent_id'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['student_action_error'] = "All fields are required.";
            header("Location: ../school_students.php");
            exit;
        }
    }
    
    // Sanitize and prepare data
    $student_name = trim($_POST['student_name']);
    $parent_name = trim($_POST['parent_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $password = trim($_POST['password']);
    $class = (int) $_POST['class'];
    $parent_id = trim($_POST['parent_id']);
    
    // Validate class
    if ($class < 1 || $class > 12) {
        $_SESSION['student_action_error'] = "Invalid class selected.";
        header("Location: ../school_students.php");
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['student_action_error'] = "Invalid email format.";
        header("Location: ../school_students.php");
        exit;
    }
    
    // Check if email already exists
    $check_email_stmt = $conn->prepare("SELECT id FROM parents WHERE email = ? AND id NOT IN (SELECT parent_id FROM students WHERE school_id = ?)");
    $check_email_stmt->bind_param("ss", $email, $school_id);
    $check_email_stmt->execute();
    $check_email_result = $check_email_stmt->get_result();
    
    if ($check_email_result->num_rows > 0) {
        $_SESSION['student_action_error'] = "This email is already registered. Please use a different email.";
        $check_email_stmt->close();
        header("Location: ../school_students.php");
        exit;
    }
    $check_email_stmt->close();
    
    // Handle file upload if provided
    $photo_path = null;
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        $upload_dir = "../uploads/students/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        $file_mime_type = mime_content_type($_FILES['photo']['tmp_name']);
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

        if (!in_array($file_mime_type, $allowed_mime_types) || !in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Invalid file type.");
        }

        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $file_name = "student_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Check file size (max 2MB)
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['student_action_error'] = "File size exceeds 2MB limit.";
            header("Location: ../school_students.php");
            exit;
        }
        
        // Allow only certain image file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $_SESSION['student_action_error'] = "Only JPG, JPEG, and PNG files are allowed.";
            header("Location: ../school_students.php");
            exit;
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = "uploads/students/" . $file_name;
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Check if parent already exists
        $parent_exists = false;
        $parent_check_stmt = $conn->prepare("SELECT id FROM parents WHERE parent_id = ?");
        $parent_check_stmt->bind_param("s", $parent_id);
        $parent_check_stmt->execute();
        $parent_result = $parent_check_stmt->get_result();
        
        if ($parent_result->num_rows > 0) {
            $parent_row = $parent_result->fetch_assoc();
            $parent_db_id = $parent_row['id'];
            $parent_exists = true;
        }
        $parent_check_stmt->close();
        
        if (!$parent_exists) {
            // Insert parent data
            $parent_stmt = $conn->prepare("
                INSERT INTO parents (parent_id, parent_name, email, mobile, password, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            $parent_stmt->bind_param("sssss", $parent_id, $parent_name, $email, $mobile, $password);
            $parent_stmt->execute();
            $parent_db_id = $conn->insert_id;
            $parent_stmt->close();
        }
        
        // Insert student data
        $student_stmt = $conn->prepare("
            INSERT INTO students (student_name, school_id, parent_id, class, photo, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        $student_stmt->bind_param("ssiss", $student_name, $school_id, $parent_db_id, $class, $photo_path);
        $student_stmt->execute();
        $student_id = $conn->insert_id;
        $student_stmt->close();
        
        // Log activity
        logActivity($conn, $school_id, 'add_student', "Added new student: $student_name, Class $class");
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['student_action_success'] = "Student $student_name has been successfully added.";
        header("Location: ../school_students.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
}

/**
 * Edit an existing student
 * 
 * @param mysqli $conn Database connection
 * @param string $school_id School ID
 */
function editStudent($conn, $school_id) {
    // Check required fields
    if (!isset($_POST['student_id']) || empty($_POST['student_id'])) {
        $_SESSION['student_action_error'] = "Student ID is required.";
        header("Location: ../school_students.php");
        exit;
    }
    
    $student_id = (int) $_POST['student_id'];
    
    // Validate required fields
    $required_fields = ['student_name', 'parent_name', 'email', 'mobile', 'class'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            $_SESSION['student_action_error'] = "All fields are required.";
            header("Location: ../school_students.php");
            exit;
        }
    }
    
    // Sanitize and prepare data
    $student_name = trim($_POST['student_name']);
    $parent_name = trim($_POST['parent_name']);
    $email = trim($_POST['email']);
    $mobile = trim($_POST['mobile']);
    $class = (int) $_POST['class'];
    $password = isset($_POST['password']) && !empty($_POST['password']) 
        ? trim($_POST['password']) 
        : null;
    
    // Validate class
    if ($class < 1 || $class > 12) {
        $_SESSION['student_action_error'] = "Invalid class selected.";
        header("Location: ../school_students.php");
        exit;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['student_action_error'] = "Invalid email format.";
        header("Location: ../school_students.php");
        exit;
    }
    
    // Get student and parent data
    $student_stmt = $conn->prepare("
        SELECT s.id, s.student_name, s.parent_id, s.photo, p.parent_id AS parent_code
        FROM students s
        JOIN parents p ON s.parent_id = p.id
        WHERE s.id = ? AND s.school_id = ?
    ");
    $student_stmt->bind_param("is", $student_id, $school_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows === 0) {
        $student_stmt->close();
        $_SESSION['student_action_error'] = "Student not found or you don't have permission to edit this student.";
        header("Location: ../school_students.php");
        exit;
    }
    
    $student_data = $student_result->fetch_assoc();
    $parent_id = $student_data['parent_id'];
    $current_photo = $student_data['photo'];
    $student_stmt->close();
    
    // Handle file upload if provided
    $photo_path = $current_photo;
    if (isset($_FILES['photo']) && $_FILES['photo']['size'] > 0) {
        $upload_dir = "../uploads/students/";
        
        // Create directory if it doesn't exist
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_mime_types = ['image/jpeg', 'image/png'];
        $allowed_extensions = ['jpg', 'jpeg', 'png'];

        $file_mime_type = mime_content_type($_FILES['photo']['tmp_name']);
        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);

        if (!in_array($file_mime_type, $allowed_mime_types) || !in_array($file_extension, $allowed_extensions)) {
            throw new Exception("Invalid file type.");
        }

        $file_extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
        $file_name = "student_" . time() . "_" . rand(1000, 9999) . "." . $file_extension;
        $target_file = $upload_dir . $file_name;
        
        // Check file size (max 2MB)
        if ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $_SESSION['student_action_error'] = "File size exceeds 2MB limit.";
            header("Location: ../school_students.php");
            exit;
        }
        
        // Allow only certain image file formats
        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        if (!in_array(strtolower($file_extension), $allowed_extensions)) {
            $_SESSION['student_action_error'] = "Only JPG, JPEG, and PNG files are allowed.";
            header("Location: ../school_students.php");
            exit;
        }
        
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            $photo_path = "uploads/students/" . $file_name;
            
            // Delete old photo if exists
            if ($current_photo && file_exists("../" . $current_photo)) {
                unlink("../" . $current_photo);
            }
        }
    }
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update parent information
        $parent_sql = "UPDATE parents SET parent_name = ?, email = ?, mobile = ?";
        $parent_params = [$parent_name, $email, $mobile];
        $parent_types = "sss";
        
        if ($password !== null) {
            $parent_sql .= ", password = ?";
            $parent_params[] = $password;
            $parent_types .= "s";
        }
        
        $parent_sql .= " WHERE id = ?";
        $parent_params[] = $parent_id;
        $parent_types .= "i";
        
        $parent_stmt = $conn->prepare($parent_sql);
        $parent_stmt->bind_param($parent_types, ...$parent_params);
        $parent_stmt->execute();
        $parent_stmt->close();
        
        // Update student information
        $student_stmt = $conn->prepare("
            UPDATE students SET 
                student_name = ?,
                class = ?,
                photo = ?,
                updated_at = NOW()
            WHERE id = ? AND school_id = ?
        ");
        $student_stmt->bind_param("sissi", $student_name, $class, $photo_path, $student_id, $school_id);
        $student_stmt->execute();
        $student_stmt->close();
        
        // Log activity
        logActivity($conn, $school_id, 'edit_student', "Updated student: $student_name, Class $class");
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['student_action_success'] = "Student $student_name has been successfully updated.";
        header("Location: ../school_students.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
}

/**
 * Delete a student
 * 
 * @param mysqli $conn Database connection
 * @param string $school_id School ID
 */
function deleteStudent($conn, $school_id) {
    // Validate student ID
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        $_SESSION['student_action_error'] = "Student ID is required for deletion.";
        header("Location: ../school_students.php");
        exit;
    }
    
    $student_id = (int) $_GET['id'];
    
    // Get student data
    $student_stmt = $conn->prepare("
        SELECT s.id, s.student_name, s.parent_id, s.photo, p.parent_id AS parent_code,
               (SELECT COUNT(*) FROM students WHERE parent_id = s.parent_id) AS student_count
        FROM students s
        JOIN parents p ON s.parent_id = p.id
        WHERE s.id = ? AND s.school_id = ?
    ");
    $student_stmt->bind_param("is", $student_id, $school_id);
    $student_stmt->execute();
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows === 0) {
        $student_stmt->close();
        $_SESSION['student_action_error'] = "Student not found or you don't have permission to delete this student.";
        header("Location: ../school_students.php");
        exit;
    }
    
    $student_data = $student_result->fetch_assoc();
    $student_name = $student_data['student_name'];
    $parent_id = $student_data['parent_id'];
    $photo = $student_data['photo'];
    $student_count = $student_data['student_count'];
    $student_stmt->close();
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Delete student
        $delete_stmt = $conn->prepare("DELETE FROM students WHERE id = ? AND school_id = ?");
        $delete_stmt->bind_param("is", $student_id, $school_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        // Delete parent if this was the only student
        if ($student_count == 1) {
            $delete_parent_stmt = $conn->prepare("DELETE FROM parents WHERE id = ?");
            $delete_parent_stmt->bind_param("i", $parent_id);
            $delete_parent_stmt->execute();
            $delete_parent_stmt->close();
        }
        
        // Delete photo if exists
        if ($photo && file_exists("../" . $photo)) {
            unlink("../" . $photo);
        }
        
        // Log activity
        logActivity($conn, $school_id, 'delete_student', "Deleted student: $student_name");
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['student_action_success'] = "Student $student_name has been successfully deleted.";
        header("Location: ../school_students.php");
        exit;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        throw $e;
    }
}

/**
 * Log activity
 * 
 * @param mysqli $conn Database connection
 * @param string $school_id School ID
 * @param string $action The action performed
 * @param string $details Details about the action
 */
function logActivity($conn, $school_id, $action, $details) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    
    $details_json = json_encode(['details' => $details, 'ip' => $ip_address, 'user_agent' => $user_agent]);
    
    $log_stmt = $conn->prepare("
        INSERT INTO system_logs (user_id, user_type, action, details, timestamp)
        VALUES (?, 'school', ?, ?, NOW())
    ");
    $log_stmt->bind_param("iss", $school_id, $action, $details_json);
    $log_stmt->execute();
    $log_stmt->close();
}