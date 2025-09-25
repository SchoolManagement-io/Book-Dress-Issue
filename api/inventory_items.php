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

try {
    // Connect to database
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Set charset
    $conn->set_charset("utf8mb4");
    
    // First, get the student's school ID and class
    $parent_id = $_SESSION['parent_id'];
    
    $stmt = $conn->prepare("
        SELECT 
            s.school_id,
            s.class
        FROM 
            students s
        JOIN 
            parents p ON s.parent_id = p.id
        WHERE 
            p.parent_id = ?
    ");
    
    $stmt->bind_param("s", $parent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("No student found for parent ID: " . $parent_id);
    }
    
    $student_data = $result->fetch_assoc();
    $school_id = $student_data['school_id'];
    $student_class = $student_data['class'];
    
    $stmt->close();
    
    // Query to get inventory items for the school and student's class
    $stmt = $conn->prepare("
        SELECT 
            i.id, 
            i.item_name, 
            i.category, 
            i.price, 
            i.quantity, 
            i.class,
            i.type,
            i.sku,
            i.school_id
        FROM 
            inventory i
        WHERE 
            i.school_id = ? AND 
            (i.class = ? OR i.class IS NULL OR i.class = '')
    ");
    
    $stmt->bind_param("is", $school_id, $student_class);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $items = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $items[] = [
                'id' => $row['id'],
                'name' => $row['item_name'],
                'category' => $row['category'],
                'price' => (float)$row['price'],
                'quantity' => (int)$row['quantity'],
                'class' => $row['class'],
                'type' => $row['type'] ?? '',
                'sku' => $row['sku'] ?? '',
                'school_id' => $row['school_id'],
                'in_stock' => $row['quantity'] > 0,
                'description' => "Class: " . ($row['class'] ?? 'All') . 
                                 ", Type: " . ($row['type'] ?? 'Standard') . 
                                 ", SKU: " . ($row['sku'] ?? 'N/A')
            ];
        }
    }
    
    $stmt->close();
    $conn->close();
    
    // Return inventory items
    echo json_encode([
        'success' => true,
        'items' => $items
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit; 