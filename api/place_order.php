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

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
    exit();
}

// Get the raw POST data
$json_data = file_get_contents('php://input');
$order_data = json_decode($json_data, true);

// Validate order data
if (!isset($order_data['items']) || empty($order_data['items'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No items in order'
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
    
    // Start transaction
    $conn->begin_transaction();
    
    // Get parent ID and student info
    $parent_id = $_SESSION['parent_id'];
    
    // Get student and school info
    $stmt = $conn->prepare("
        SELECT 
            s.id AS student_id,
            s.school_id
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
    $school_id = $student_data['school_id'];
    
    $stmt->close();
    
    // Get order number
    $order_number = 'ORD' . date('YmdHis') . rand(100, 999);
    
    // Create order record
    $notes = isset($order_data['note']) ? $order_data['note'] : '';
    $delivery_address = isset($order_data['delivery_address']) ? $order_data['delivery_address'] : '';
    $current_time = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("
        INSERT INTO orders 
            (order_number, student_id, school_id, status, notes, delivery_address, total_amount, created_at, updated_at)
        VALUES 
            (?, ?, ?, 'Pending', ?, ?, 0, ?, ?)
    ");
    
    $stmt->bind_param("siissss", $order_number, $student_id, $school_id, $notes, $delivery_address, $current_time, $current_time);
    $stmt->execute();
    $order_id = $conn->insert_id;
    
    if (!$order_id) {
        throw new Exception("Failed to create order record");
    }
    
    $stmt->close();
    
    // Process each order item
    $total_amount = 0;
    
    foreach ($order_data['items'] as $item) {
        // Validate item data
        if (!isset($item['id']) || !isset($item['quantity']) || $item['quantity'] <= 0) {
            continue;
        }
        
        // Get item details and check stock
        $stmt = $conn->prepare("
            SELECT 
                id, 
                item_name as name, 
                price, 
                quantity
            FROM 
                inventory
            WHERE 
                id = ? AND 
                school_id = ?
        ");
        
        $stmt->bind_param("ii", $item['id'], $school_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $stmt->close();
            continue; // Skip if item not found
        }
        
        $inventory_item = $result->fetch_assoc();
        $stmt->close();
        
        // Check if enough stock available
        if ($inventory_item['quantity'] < $item['quantity']) {
            throw new Exception("Not enough stock for item: " . $inventory_item['name']);
        }
        
        // Calculate item total
        $item_price = $inventory_item['price'];
        $item_total = $item_price * $item['quantity'];
        $total_amount += $item_total;
        
        // Add order item
        $stmt = $conn->prepare("
            INSERT INTO order_items 
                (order_id, inventory_id, quantity, unit_price, total_price)
            VALUES 
                (?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param("iiidd", $order_id, $item['id'], $item['quantity'], $item_price, $item_total);
        $stmt->execute();
        $stmt->close();
        
        // Update inventory quantity
        $new_quantity = $inventory_item['quantity'] - $item['quantity'];
        
        $stmt = $conn->prepare("
            UPDATE inventory
            SET quantity = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param("ii", $new_quantity, $item['id']);
        $stmt->execute();
        $stmt->close();
    }
    
    // Update order with total amount
    $stmt = $conn->prepare("
        UPDATE orders
        SET total_amount = ?
        WHERE id = ?
    ");
    
    $stmt->bind_param("di", $total_amount, $order_id);
    $stmt->execute();
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    // Close connection
    $conn->close();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'order_id' => $order_number,
        'message' => 'Order placed successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction if an error occurred
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->rollback();
        $conn->close();
    }
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

exit; 