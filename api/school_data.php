<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is authenticated as a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'school_login.php'
    ]);
    exit;
}

// Get school ID from session
$school_id = $_SESSION['user_id'];

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Connect to database
try {
    $mysqli = new mysqli(
        $db_config['host'],
        $db_config['username'],
        $db_config['password'],
        $db_config['database']
    );

    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }

    // Set character set
    $mysqli->set_charset('utf8mb4');
    
    // Get school data
    $stmt = $mysqli->prepare("
        SELECT 
            school_name, 
            email, 
            phone, 
            address, 
            created_at
        FROM schools 
        WHERE id = ?
    ");
    
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("School data not found");
    }
    
    $school_data = $result->fetch_assoc();
    $stmt->close();
    
    // Get inventory statistics
    $inventory_stats = [
        'total_items' => 0,
        'total_value' => 0,
        'books_count' => 0,
        'uniforms_count' => 0,
        'stationary_count' => 0,
        'other_count' => 0,
        'low_stock_count' => 0
    ];
    
    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) as total_items,
            SUM(price * quantity) as total_value,
            SUM(CASE WHEN category = 'Book' THEN 1 ELSE 0 END) as books_count,
            SUM(CASE WHEN category = 'Uniform' THEN 1 ELSE 0 END) as uniforms_count,
            SUM(CASE WHEN category = 'Stationery' THEN 1 ELSE 0 END) as stationary_count,
            SUM(CASE WHEN category = 'Accessories' THEN 1 ELSE 0 END) as other_count,
            SUM(CASE WHEN quantity <= 10 THEN 1 ELSE 0 END) as low_stock_count
        FROM inventory
        WHERE school_id = ?
    ");
    
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $inventory_stats = $result->fetch_assoc();
        
        // Format total value as currency
        $inventory_stats['total_value'] = $inventory_stats['total_value'] ?? 0;
        
        // Convert to integers
        $inventory_stats['total_items'] = (int)$inventory_stats['total_items'];
        $inventory_stats['books_count'] = (int)$inventory_stats['books_count'];
        $inventory_stats['uniforms_count'] = (int)$inventory_stats['uniforms_count'];
        $inventory_stats['stationary_count'] = (int)$inventory_stats['stationary_count'];
        $inventory_stats['other_count'] = (int)$inventory_stats['other_count'];
        $inventory_stats['low_stock_count'] = (int)$inventory_stats['low_stock_count'];
    }
    $stmt->close();
    
    // Get orders statistics
    $orders_stats = [
        'total_orders' => 0,
        'completed_orders' => 0,
        'pending_orders' => 0,
        'processing_orders' => 0,
        'total_sales' => 0,
        'monthly_sales' => 0
    ];
    
    $stmt = $mysqli->prepare("
        SELECT 
            COUNT(*) as total_orders,
            SUM(CASE WHEN status = 'Delivered' THEN 1 ELSE 0 END) as completed_orders,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'Processing' THEN 1 ELSE 0 END) as processing_orders,
            SUM(total_amount) as total_sales,
            SUM(CASE WHEN created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN total_amount ELSE 0 END) as monthly_sales
        FROM orders
        WHERE school_id = ?
    ");
    
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $orders_stats = $result->fetch_assoc();
        
        // Convert to integers/floats
        $orders_stats['total_orders'] = (int)$orders_stats['total_orders'];
        $orders_stats['completed_orders'] = (int)$orders_stats['completed_orders'];
        $orders_stats['pending_orders'] = (int)$orders_stats['pending_orders'];
        $orders_stats['processing_orders'] = (int)$orders_stats['processing_orders'];
        $orders_stats['total_sales'] = (float)$orders_stats['total_sales'];
        $orders_stats['monthly_sales'] = (float)$orders_stats['monthly_sales'];
    }
    $stmt->close();
    
    // Get students count
    $students_count = 0;
    $stmt = $mysqli->prepare("SELECT COUNT(*) as students_count FROM students WHERE school_id = ?");
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $students_count = (int)$row['students_count'];
    }
    $stmt->close();
    
    // Get recent orders
    $recent_orders = [];
    $stmt = $mysqli->prepare("
        SELECT 
            o.id, 
            o.order_number, 
            o.created_at as order_date, 
            o.total_amount, 
            o.status as order_status, 
            s.student_name as student_name,
            s.class as student_class,
            p.parent_name as parent_name
        FROM orders o
        JOIN students s ON o.student_id = s.id
        JOIN parents p ON s.parent_id = p.id
        WHERE o.school_id = ?
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $recent_orders[] = [
            'id' => $row['id'],
            'order_number' => $row['order_number'],
            'order_date' => $row['order_date'],
            'total_amount' => (float)$row['total_amount'],
            'order_status' => $row['order_status'],
            'student_name' => $row['student_name'],
            'student_class' => $row['student_class'],
            'parent_name' => $row['parent_name']
        ];
    }
    $stmt->close();
    
    // Close database connection
    $mysqli->close();
    
    // Return success response with school data and statistics
    echo json_encode([
        'success' => true,
        'school' => $school_data,
        'inventory' => $inventory_stats,
        'orders' => $orders_stats,
        'students_count' => $students_count,
        'recent_orders' => $recent_orders
    ]);
    
} catch (Exception $e) {
    // Log error
    error_log("Error fetching school data: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch school data. Please try again later.'
    ]);
    
    // Close mysqli if it exists
    if (isset($mysqli)) {
        $mysqli->close();
    }
}
?> 