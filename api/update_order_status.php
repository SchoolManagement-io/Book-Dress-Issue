<?php
// Start session
session_start();

// Check if user is logged in and is a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'school_login.php'
    ]);
    exit;
}

// Set headers for JSON response
header('Content-Type: application/json');

// Get the JSON data from the request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

// Check if required data is provided
if (!isset($data['order_ids']) || !isset($data['status'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order IDs and status are required'
    ]);
    exit;
}

// Validate status
$valid_statuses = ['pending', 'processing', 'ready', 'delivered', 'cancelled'];
if (!in_array($data['status'], $valid_statuses)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid status value'
    ]);
    exit;
}

// Database connection
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

function connectDB($config) {
    $conn = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    
    if ($conn->connect_error) {
        die(json_encode([
            'success' => false,
            'message' => 'Database connection failed: ' . $conn->connect_error
        ]));
    }
    
    // Set character set
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

// Connect to database
$conn = connectDB($db_config);

// Get school ID from session
$school_id = $_SESSION['user_id'];
$new_status = $data['status'];
$order_ids = $data['order_ids'];

// Convert to array if single ID
if (!is_array($order_ids)) {
    $order_ids = [$order_ids];
}

// Validate and sanitize order IDs
$valid_order_ids = [];
foreach ($order_ids as $id) {
    $id = intval($id);
    if ($id > 0) {
        $valid_order_ids[] = $id;
    }
}

if (empty($valid_order_ids)) {
    echo json_encode([
        'success' => false,
        'message' => 'No valid order IDs provided'
    ]);
    $conn->close();
    exit;
}

// Convert valid order IDs to comma-separated string for SQL
$order_ids_str = implode(',', $valid_order_ids);

// Start transaction
$conn->begin_transaction();

try {
    // Check if the orders belong to the school
    $check_query = "
        SELECT id, status
        FROM orders
        WHERE id IN ($order_ids_str) AND school_id = $school_id
    ";
    
    $check_result = $conn->query($check_query);
    
    // Get the orders that belong to the school
    $school_orders = [];
    $current_statuses = [];
    
    while ($row = $check_result->fetch_assoc()) {
        $school_orders[] = $row['id'];
        $current_statuses[$row['id']] = $row['status'];
    }
    
    if (empty($school_orders)) {
        throw new Exception('No orders found for this school.');
    }
    
    // Convert school order IDs to comma-separated string for SQL
    $school_orders_str = implode(',', $school_orders);
    
    // Get notes if provided
    $notes = isset($data['notes']) ? trim($data['notes']) : '';
    
    // Update all orders at once
    $update_query = "
        UPDATE orders 
        SET 
            status = '$new_status',
            updated_at = NOW()
        WHERE 
            id IN ($school_orders_str) AND 
            school_id = $school_id
    ";
    
    $update_result = $conn->query($update_query);
    
    if (!$update_result) {
        throw new Exception("Failed to update order status: " . $conn->error);
    }
    
    // Add notes if provided
    if (!empty($notes)) {
        $notes_entry = date('Y-m-d H:i') . ' - Status changed to ' . $new_status . ': ' . $notes;
        
        foreach ($school_orders as $order_id) {
            $update_notes_query = "
                UPDATE orders
                SET notes = CONCAT(IFNULL(notes, ''), '\n', '$notes_entry')
                WHERE id = $order_id
            ";
            $conn->query($update_notes_query);
        }
    }
    
    // Commit transaction
    $conn->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Order status updated successfully',
        'status' => $new_status,
        'updated_count' => count($school_orders)
    ]);
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

// Close connection
$conn->close(); 