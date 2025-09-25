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

// Default values for pagination
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get filters from query parameters
$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : null;
$date_range = isset($_GET['date_range']) ? $conn->real_escape_string($_GET['date_range']) : null;
$class_filter = isset($_GET['class']) ? $conn->real_escape_string($_GET['class']) : null;
$search_query = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : null;
$start_date = isset($_GET['start_date']) ? $conn->real_escape_string($_GET['start_date']) : null;
$end_date = isset($_GET['end_date']) ? $conn->real_escape_string($_GET['end_date']) : null;

// Build where clause for filters
$where_clauses = ["o.school_id = '$school_id'"];

if ($status_filter) {
    $where_clauses[] = "o.status = '$status_filter'";
}

if ($class_filter) {
    $where_clauses[] = "s.class = '$class_filter'";
}

if ($search_query) {
    $where_clauses[] = "(o.order_number LIKE '%$search_query%' OR s.name LIKE '%$search_query%' OR p.name LIKE '%$search_query%')";
}

// Date filters
if ($date_range) {
    switch ($date_range) {
        case 'today':
            $where_clauses[] = "DATE(o.created_at) = CURDATE()";
            break;
        case 'yesterday':
            $where_clauses[] = "DATE(o.created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)";
            break;
        case 'this_week':
            $where_clauses[] = "YEARWEEK(o.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'last_week':
            $where_clauses[] = "YEARWEEK(o.created_at, 1) = YEARWEEK(DATE_SUB(CURDATE(), INTERVAL 1 WEEK), 1)";
            break;
        case 'this_month':
            $where_clauses[] = "YEAR(o.created_at) = YEAR(CURDATE()) AND MONTH(o.created_at) = MONTH(CURDATE())";
            break;
        case 'last_month':
            $where_clauses[] = "YEAR(o.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND MONTH(o.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))";
            break;
        case 'custom':
            if ($start_date) {
                $where_clauses[] = "DATE(o.created_at) >= '$start_date'";
            }
            if ($end_date) {
                $where_clauses[] = "DATE(o.created_at) <= '$end_date'";
            }
            break;
    }
}

// Combine where clauses
$where_clause = implode(' AND ', $where_clauses);

// Get order statistics
$statistics_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN o.status = 'processing' THEN 1 ELSE 0 END) as processing,
        SUM(CASE WHEN o.status = 'ready' THEN 1 ELSE 0 END) as ready,
        SUM(CASE WHEN o.status = 'delivered' THEN 1 ELSE 0 END) as delivered,
        SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders o
    WHERE o.school_id = '$school_id'
";

$statistics_result = $conn->query($statistics_query);
$statistics = $statistics_result->fetch_assoc();

// Count total filtered orders for pagination
$count_query = "
    SELECT COUNT(*) as total
    FROM orders o
    LEFT JOIN students s ON o.student_id = s.id
    LEFT JOIN parents p ON s.parent_id = p.id
    WHERE $where_clause
";

$count_result = $conn->query($count_query);
$count_row = $count_result->fetch_assoc();
$total_orders = $count_row['total'];

// Calculate total pages
$total_pages = ceil($total_orders / $limit);

// Adjust page if it exceeds total pages
if ($page > $total_pages && $total_pages > 0) {
    $page = $total_pages;
    $offset = ($page - 1) * $limit;
}

// Get orders
$query = "
    SELECT 
        o.id,
        o.order_number,
        o.created_at,
        o.status,
        o.total_amount,
        s.student_name,
        s.class,
        p.parent_name,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
    FROM orders o
    LEFT JOIN students s ON o.student_id = s.id
    LEFT JOIN parents p ON s.parent_id = p.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
    LIMIT $offset, $limit
";

$result = $conn->query($query);

// Check if query was successful
if (!$result) {
    echo json_encode([
        'success' => false,
        'message' => 'Database query failed: ' . $conn->error
    ]);
    $conn->close();
    exit;
}

// Prepare orders array
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

// Prepare pagination info
$pagination = [
    'current_page' => $page,
    'total_pages' => $total_pages,
    'orders_per_page' => $limit,
    'total_orders' => $total_orders
];

// Prepare response
$response = [
    'success' => true,
    'orders' => $orders,
    'statistics' => $statistics,
    'pagination' => $pagination
];

// Return response
echo json_encode($response);

// Close connection
$conn->close(); 