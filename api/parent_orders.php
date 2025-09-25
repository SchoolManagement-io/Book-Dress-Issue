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
    
    // Get parent ID from session
    $parent_id = $_SESSION['parent_id'];
    
    // First, get student ID associated with parent
    $stmt = $conn->prepare("
        SELECT 
            s.id AS student_id
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
    $student_id = $student_data['student_id'];
    
    $stmt->close();
    
    // Now get all orders for the student
    $stmt = $conn->prepare("
        SELECT 
            o.id,
            o.order_number,
            o.status,
            o.notes,
            o.total_amount,
            o.created_at,
            COUNT(oi.id) AS item_count
        FROM 
            orders o
        LEFT JOIN 
            order_items oi ON o.id = oi.order_id
        WHERE 
            o.student_id = ?
        GROUP BY
            o.id
        ORDER BY 
            o.created_at DESC
    ");
    
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $orders_result = $stmt->get_result();
    
    // Prepare orders array
    $orders = [];
    
    while ($order = $orders_result->fetch_assoc()) {
        // Get items for this order
        $items_stmt = $conn->prepare("
            SELECT 
                oi.id,
                oi.quantity,
                oi.unit_price,
                oi.total_price,
                i.item_name as name,
                i.category
            FROM 
                order_items oi
            JOIN 
                inventory i ON oi.inventory_id = i.id
            WHERE 
                oi.order_id = ?
        ");
        
        $items_stmt->bind_param("i", $order['id']);
        $items_stmt->execute();
        $items_result = $items_stmt->get_result();
        
        $items = [];
        while ($item = $items_result->fetch_assoc()) {
            $items[] = [
                'id' => $item['id'],
                'name' => $item['name'],
                'category' => $item['category'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'total_price' => $item['total_price']
            ];
        }
        
        $items_stmt->close();
        
        // Format order data
        $orders[] = [
            'id' => $order['id'],
            'order_id' => $order['order_number'],
            'status' => $order['status'],
            'delivery_note' => $order['notes'],
            'total_amount' => $order['total_amount'],
            'created_at' => $order['created_at'],
            'item_count' => $order['item_count'],
            'items' => $items
        ];
    }
    
    $stmt->close();
    $conn->close();
    
    // Return success response with orders
    echo json_encode([
        'success' => true,
        'orders' => $orders
    ]);
    
} catch (Exception $e) {
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit; 