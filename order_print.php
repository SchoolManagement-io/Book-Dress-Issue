<?php
// Start session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] !== 'school' && $_SESSION['user_type'] !== 'parent')) {
    header('Location: index.php');
    exit;
}

// Check if order ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ' . ($_SESSION['user_type'] === 'school' ? 'school_orders.php' : 'parent_orders.php'));
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
        die('Database connection failed: ' . $conn->connect_error);
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
    header('Location: ' . ($_SESSION['user_type'] === 'school' ? 'school_orders.php' : 'parent_orders.php'));
    exit;
}

// Get order details
$stmt = $conn->prepare("
    SELECT 
        o.id,
        o.order_number,
        o.total_amount,
        o.status,
        o.notes,
        o.created_at,
        o.updated_at,
        s.student_name as student_name,
        s.class as student_class,
        p.parent_name as parent_name,
        p.email as parent_email,
        p.mobile as parent_phone,
        sc.school_name,
        sc.address as school_address,
        sc.phone as school_phone
    FROM 
        orders o
    JOIN 
        students s ON o.student_id = s.id
    JOIN 
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
    die("Order not found");
}

$order = $result->fetch_assoc();
$stmt->close();

// Format order date
$order_date = date('d-M-Y h:i A', strtotime($order['created_at']));

// Format student class
$student_class = $order['student_class'];

// Format currency
$total_amount = number_format($order['total_amount'], 2);

// Get order items
$items_query = "
    SELECT 
        oi.id,
        oi.quantity,
        oi.unit_price,
        i.item_name,
        i.type,
        i.sku
    FROM order_items oi
    JOIN inventory i ON oi.inventory_id = i.id
    WHERE oi.order_id = $order_id
";

$items_result = $conn->query($items_query);

// Prepare items array
$items = [];
while ($item = $items_result->fetch_assoc()) {
    $items[] = $item;
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order #<?php echo $order['order_number']; ?> - Receipt</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #333;
            font-size: 14px;
        }
        
        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        
        .receipt-header img {
            max-height: 80px;
            margin-bottom: 10px;
        }
        
        .school-info {
            margin-bottom: 15px;
        }
        
        .order-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .order-items {
            margin-bottom: 20px;
        }
        
        .table th {
            background-color: #f1f1f1;
        }
        
        .receipt-footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #666;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-weight: 500;
            text-transform: capitalize;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #d1ecf1; color: #0c5460; }
        .status-ready { background-color: #cce5ff; color: #004085; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .delivery-note {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-top: 20px;
            font-style: italic;
        }
        
        .print-buttons {
            margin-bottom: 20px;
            text-align: center;
        }
        
        @media print {
            body {
                background-color: #fff;
            }
            .receipt-container {
                box-shadow: none;
                margin: 0;
                padding: 15px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="print-buttons no-print">
            <button class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Print Receipt
            </button>
            <a href="<?php echo $_SESSION['user_type'] === 'school' ? 'school_orders.php' : 'parent_orders.php'; ?>" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i> Back to Orders
            </a>
        </div>
        
        <div class="receipt-header">
            <?php if (!empty($order['school_logo'])): ?>
                <img src="<?php echo htmlspecialchars($order['school_logo']); ?>" alt="<?php echo htmlspecialchars($order['school_name']); ?> Logo">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($order['school_name']); ?></h2>
            <p><?php echo nl2br(htmlspecialchars($order['school_address'])); ?></p>
            <p>Phone: <?php echo htmlspecialchars($order['school_phone']); ?></p>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="school-info">
                    <h5>Order Details</h5>
                    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                    <p><strong>Date & Time:</strong> <?php echo $order_date; ?></p>
                    <p>
                        <strong>Status:</strong> 
                        <span class="status-badge status-<?php echo strtolower($order['status']); ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </p>
                    <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method'] ?? 'N/A'); ?></p>
                    <p><strong>Payment Status:</strong> <?php echo ucfirst($order['payment_status'] ?? 'N/A'); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="student-info">
                    <h5>Student Information</h5>
                    <p><strong>Name:</strong> <?php echo htmlspecialchars($order['student_name']); ?></p>
                    <p><strong>Class:</strong> <?php echo htmlspecialchars($student_class); ?></p>
                    <p><strong>Roll Number:</strong> <?php echo htmlspecialchars($order['student_roll'] ?? 'N/A'); ?></p>
                    <p><strong>Parent Name:</strong> <?php echo htmlspecialchars($order['parent_name']); ?></p>
                    <p><strong>Parent Contact:</strong> <?php echo htmlspecialchars($order['parent_phone']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="order-items">
            <h5>Order Items</h5>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th>SKU</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $subtotal = 0;
                    foreach ($items as $index => $item): 
                        $item_total = $item['quantity'] * $item['unit_price'];
                        $subtotal += $item_total;
                    ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                        <td><?php echo ucfirst($item['type'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['sku']); ?></td>
                        <td class="text-center"><?php echo $item['quantity']; ?></td>
                        <td class="text-end">₹<?php echo number_format($item['unit_price'], 2); ?></td>
                        <td class="text-end">₹<?php echo number_format($item_total, 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="6" class="text-end">Subtotal:</th>
                        <th class="text-end">₹<?php echo number_format($subtotal, 2); ?></th>
                    </tr>
                    <!-- Additional costs could be added here if needed -->
                    <tr>
                        <th colspan="6" class="text-end">Total Amount:</th>
                        <th class="text-end">₹<?php echo $total_amount; ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <?php if (!empty($order['notes'])): ?>
        <div class="delivery-note">
            <h6>Delivery Note:</h6>
            <p><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
        </div>
        <?php endif; ?>
        
        <div class="receipt-footer">
            <p>This is an electronically generated receipt and does not require signature.</p>
            <p>For any queries regarding this order, please contact the school office.</p>
            <p>Thank you for your order!</p>
            <p>Printed on: <?php echo date('d-M-Y h:i A'); ?></p>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 