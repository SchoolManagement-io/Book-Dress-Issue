<?php
// Start session
session_start();

// Check if user is logged in and is a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header('Location: school_login.php');
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
        die('Database connection failed: ' . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset('utf8mb4');
    
    return $conn;
}

// Connect to database
$conn = connectDB($db_config);

// Get school ID from session
$school_id = $_SESSION['user_id'];

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

// Get school information for the filename
$school_query = "SELECT name FROM schools WHERE id = $school_id";
$school_result = $conn->query($school_query);
$school_row = $school_result->fetch_assoc();
$school_name = $school_row['name'] ? preg_replace('/[^a-zA-Z0-9]/', '_', $school_row['name']) : 'School';

// Prepare filename with timestamp
$timestamp = date('Y-m-d_H-i-s');
$filename = "{$school_name}_Orders_{$timestamp}.csv";

// Get orders
$query = "
    SELECT 
        o.id,
        o.order_number,
        o.created_at,
        o.status,
        o.payment_status,
        o.payment_method,
        o.total_amount,
        o.delivery_note,
        s.student_name as student_name,
        s.class as student_class,
        s.section as student_section,
        s.roll_number as student_roll,
        p.parent_name as parent_name,
        p.mobile as parent_phone,
        p.email as parent_email,
        (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
    FROM orders o
    LEFT JOIN students s ON o.student_id = s.id
    LEFT JOIN parents p ON s.parent_id = p.id
    WHERE $where_clause
    ORDER BY o.created_at DESC
";

$result = $conn->query($query);

// Check if query was successful
if (!$result) {
    die('Database query failed: ' . $conn->error);
}

// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Create a file pointer connected to the output stream
$output = fopen('php://output', 'w');

// Add UTF-8 BOM to fix Excel display for non-English characters
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Set column headers
$headers = [
    'Order ID',
    'Order Number',
    'Date & Time',
    'Status',
    'Payment Status',
    'Payment Method',
    'Total Amount (₹)',
    'Student Name',
    'Class',
    'Roll Number',
    'Parent Name',
    'Parent Phone',
    'Parent Email',
    'Items Count',
    'Delivery Note'
];

// Output the column headings
fputcsv($output, $headers);

// Output each row of the data
while ($row = $result->fetch_assoc()) {
    $formatted_date = date('d-M-Y H:i:s', strtotime($row['created_at']));
    $student_class = $row['student_class'] . ($row['student_section'] ? '-' . $row['student_section'] : '');
    
    $csv_row = [
        $row['id'],
        $row['order_number'],
        $formatted_date,
        ucfirst($row['status']),
        ucfirst($row['payment_status'] ?? 'N/A'),
        ucfirst($row['payment_method'] ?? 'N/A'),
        $row['total_amount'],
        $row['student_name'],
        $student_class,
        $row['student_roll'],
        $row['parent_name'],
        $row['parent_phone'],
        $row['parent_email'],
        $row['item_count'],
        $row['delivery_note']
    ];
    
    fputcsv($output, $csv_row);
}

// Close connection
$conn->close(); 