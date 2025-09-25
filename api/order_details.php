<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'school' && $_SESSION['user_type'] !== 'parent')) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'index.php'
    ]);
    exit;
}

// Set headers for JSON response
header('Content-Type: application/json');

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Order ID is required'
    ]);
    exit;
}

$order_id = intval($_GET['id']);

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

// Get user ID and type from session
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Check if user has access to the order
$access_check_query = "";

if ($user_type === 'school') {
    $access_check_query = "
        SELECT COUNT(*) as count
        FROM orders
        WHERE id = $order_id AND school_id = $user_id
    ";
} else if ($user_type === 'parent') {
    $access_check_query = "
        SELECT COUNT(*) as count
        FROM orders o
        JOIN students s ON o.student_id = s.id
        WHERE o.id = $order_id AND s.parent_id = $user_id
    ";
}

$access_check_result = $conn->query($access_check_query);
$access_check_row = $access_check_result->fetch_assoc();

if ($access_check_row['count'] == 0) {
    echo json_encode([
        'success' => false,
        'message' => 'You do not have access to this order'
    ]);
    $conn->close();
    exit;
}

// Query to get order details
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.student_id,
        o.school_id,
        o.total_amount,
        o.status,
        o.notes as delivery_note,
        o.delivery_address,
        o.created_at,
        o.updated_at,
        s.student_name,
        s.class as student_class,
        s.section as student_section,
        p.parent_name,
        sc.school_name
    FROM 
        orders o
    JOIN 
        students s ON o.student_id = s.id
    LEFT JOIN 
        parents p ON s.parent_id = p.id
    JOIN 
        schools sc ON o.school_id = sc.id
    WHERE 
        o.id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Order not found'
    ]);
    $conn->close();
    exit;
}

$order = $result->fetch_assoc();
$stmt->close();

// Query to get order items
$stmt = $conn->prepare("
    SELECT 
        oi.id,
        oi.inventory_id,
        oi.quantity,
        oi.unit_price,
        oi.total_price,
        i.item_name as name,
        i.category as type
    FROM 
        order_items oi
    JOIN 
        inventory i ON oi.inventory_id = i.id
    WHERE 
        oi.order_id = ?
");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$items_result = $stmt->get_result();

$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = [
        'id' => $item['id'],
        'inventory_id' => $item['inventory_id'],
        'name' => $item['name'],
        'type' => $item['type'],
        'quantity' => (int)$item['quantity'],
        'unit_price' => (float)$item['unit_price'],
        'total_price' => (float)$item['total_price']
    ];
}

$stmt->close();

// Format the order data
$order_data = [
    'id' => $order['id'],
    'order_number' => $order['order_number'],
    'status' => $order['status'],
    'payment_status' => 'N/A', // Default payment status
    'total_amount' => (float)$order['total_amount'],
    'created_at' => $order['created_at'],
    'updated_at' => $order['updated_at'],
    'delivery_note' => $order['delivery_note'],
    'delivery_address' => $order['delivery_address'],
    'student_name' => $order['student_name'],
    'student_class' => $order['student_class'],
    'student_section' => $order['student_section'],
    'parent_name' => $order['parent_name'],
    'school_name' => $order['school_name'],
    'items' => $items
];

// Return success response with order data
echo json_encode([
    'success' => true,
    'order' => $order_data
]);

// Close connection
$conn->close(); 