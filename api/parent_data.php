<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is logged in and is a parent
if (!isset($_SESSION['parent_id']) || $_SESSION['user_type'] !== 'parent') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit();
}

// Database connection configuration
$config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Get parent ID from query params or session
$parent_id = isset($_GET['parent_id']) ? $_GET['parent_id'] : $_SESSION['parent_id'];
$user_id = $_SESSION['user_id'] ?? null;

// Verify that the requested parent ID matches the logged-in parent
if ($parent_id !== $_SESSION['parent_id']) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access to parent data'
    ]);
    exit();
}

try {
    // Connect to database
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // Query to get student, parent, and school information
    $stmt = $conn->prepare("
        SELECT 
            p.id AS parent_id,
            p.parent_id AS parent_code,
            p.parent_name AS parent_name,
            p.email AS parent_email,
            p.mobile AS parent_phone,
            s.id AS student_id,
            s.student_name AS student_name,
            s.class AS student_class,
            s.section AS student_section,
            sc.id AS school_id,
            sc.school_name,
            sc.school_id AS school_code,
            sc.address AS school_address
        FROM 
            parents p
        JOIN 
            students s ON p.id = s.parent_id
        JOIN 
            schools sc ON s.school_id = sc.id
        WHERE 
            p.parent_id = ?
    ");
    
    $stmt->bind_param("s", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Parent not found'
        ]);
        exit();
    }
    
    // Get the parent and associated data
    $userData = $result->fetch_assoc();
    $stmt->close();
    
    // Format the response
    $response = [
        'success' => true,
        'parent' => [
            'id' => $userData['parent_id'],
            'name' => $userData['parent_name'],
            'email' => $userData['parent_email'],
            'phone' => $userData['parent_phone']
        ],
        'student' => [
            'id' => $userData['student_id'],
            'name' => $userData['student_name'],
            'class' => $userData['student_class'],
            'section' => $userData['student_section']
        ],
        'school' => [
            'id' => $userData['school_id'],
            'name' => $userData['school_name'],
            'code' => $userData['school_code'],
            'address' => $userData['school_address']
        ]
    ];
    
    // Return the response
    echo json_encode($response);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit; 