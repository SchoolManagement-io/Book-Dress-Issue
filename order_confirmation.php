<?php
// Initialize session
session_start();

// Check if parent is logged in, if not redirect to login page
if (!isset($_SESSION["parent_logged_in"]) || $_SESSION["parent_logged_in"] !== true) {
    header("Location: parent_login.php");
    exit();
}

// Get parent information
$parent_email = $_SESSION["parent_email"] ?? "parent@example.com";
$parent_name = $_SESSION["parent_name"] ?? "Parent";

// Get order ID from URL parameter (in a real application, this might be passed after order completion)
$order_id = isset($_GET["order_id"]) ? $_GET["order_id"] : null;

// If no order ID is provided, we'll attempt to get the latest order
// In a real application, this would be a database query to get the latest order
// For demo purposes, we'll simulate having the most recent order ID
if ($order_id === null) {
    // This would be a database query in a real application
    /*
    $sql = "SELECT order_id FROM orders WHERE parent_email = ? ORDER BY order_date DESC LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$parent_email]);
    if ($row = $stmt->fetch()) {
        $order_id = $row['order_id'];
    }
    */
    
    // For demo, we'll use a hardcoded order ID
    $order_id = 'ORD10050';
}

// Check if order exists and belongs to the logged in parent
// In a real application, this would be a database query
$order_found = true; // Assume order found for demo
$order_error = "";

// This would be a database query in a real application
/*
$sql = "SELECT o.*, c.name as child_name, c.class, s.name as school_name 
        FROM orders o
        JOIN children c ON o.child_id = c.id
        JOIN schools s ON c.school_id = s.id
        WHERE o.order_id = ? AND o.parent_email = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id, $parent_email]);
if (!$row = $stmt->fetch()) {
    $order_found = false;
    $order_error = "Order not found or you don't have permission to view it.";
} else {
    $order_details = $row;
}

// Get ordered items
$sql = "SELECT oi.*, i.item_name, i.item_type, i.price 
        FROM order_items oi
        JOIN inventory i ON oi.item_id = i.id
        WHERE oi.order_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$order_id]);
$ordered_items = $stmt->fetchAll();
*/

// Mock order details for demo
$order_details = [
    'order_id' => 'ORD10050',
    'parent_name' => 'Sarah Johnson',
    'child_name' => 'Alex Johnson',
    'class' => 'Class 5',
    'school_name' => 'Springfield Elementary School',
    'order_date' => '2025-04-22',
    'status' => 'Processing'
];

// Mock ordered items for demo
$ordered_items = [
    [
        'item_name' => 'Mathematics Textbook - Grade 5',
        'item_type' => 'Book',
        'quantity' => 1,
        'price' => 25.00,
        'subtotal' => 25.00
    ],
    [
        'item_name' => 'Science Textbook - Grade 5',
        'item_type' => 'Book',
        'quantity' => 1,
        'price' => 28.50,
        'subtotal' => 28.50
    ],
    [
        'item_name' => 'School Uniform (Summer) - Size M',
        'item_type' => 'Dress',
        'quantity' => 2,
        'price' => 35.00,
        'subtotal' => 70.00
    ]
];

// Calculate total price
$total_price = 0;
foreach ($ordered_items as $item) {
    $total_price += $item['subtotal'];
}

// Format the order date
$formatted_date = date('d-m-Y', strtotime($order_details['order_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - School Inventory Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .footer {
            background-color: #343a40;
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        main {
            flex: 1;
        }
        .confirmation-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .badge {
            font-size: 0.8rem;
        }
        .order-header {
            background-color: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .order-success {
            border-left: 5px solid #198754;
            background-color: #f8f9fa;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        .order-details {
            margin-bottom: 2rem;
        }
        .subtotal-row {
            border-top: 1px solid #dee2e6;
            font-weight: bold;
        }
        .print-button {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">School Inventory Management System</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="parent_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- Order Header -->
        <section class="order-header">
            <div class="container">
                <div class="confirmation-container">
                    <h1 class="mb-3">Order Confirmation</h1>
                    <p class="lead">Thank you for your order with the School Inventory Management System.</p>
                </div>
            </div>
        </section>

        <?php if (!$order_found): ?>
        <!-- Error message if order not found -->
        <section class="container mb-4">
            <div class="confirmation-container">
                <div class="alert alert-danger" role="alert">
                    <h4 class="alert-heading">Order Not Found</h4>
                    <p><?php echo $order_error; ?></p>
                    <hr>
                    <p class="mb-0">Please return to your <a href="parent_dashboard.php" class="alert-link">dashboard</a> to view your orders.</p>
                </div>
            </div>
        </section>
        <?php else: ?>
        <!-- Order Success Message -->
        <section class="container mb-4">
            <div class="confirmation-container">
                <div class="order-success">
                    <h4 class="text-success"><i class="fas fa-check-circle me-2"></i>Your order has been successfully placed!</h4>
                    <p class="mb-0">Order ID: <strong><?php echo htmlspecialchars($order_details['order_id']); ?></strong></p>
                </div>
            </div>
        </section>

        <!-- Order Details -->
        <section class="container">
            <div class="confirmation-container">
                <div class="order-details">
                    <h4 class="mb-3">Order Details</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Parent:</strong> <?php echo htmlspecialchars($order_details['parent_name']); ?></p>
                            <p><strong>Child:</strong> <?php echo htmlspecialchars($order_details['child_name']); ?></p>
                            <p><strong>Class:</strong> <?php echo htmlspecialchars($order_details['class']); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>School:</strong> <?php echo htmlspecialchars($order_details['school_name']); ?></p>
                            <p><strong>Order Date:</strong> <?php echo $formatted_date; ?></p>
                            <p><strong>Status:</strong> <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($order_details['status']); ?></span></p>
                        </div>
                    </div>
                </div>

                <!-- Ordered Items -->
                <h4 class="mb-3">Ordered Items</h4>
                <div class="table-responsive mb-4">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Item Name</th>
                                <th>Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordered_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                <td><span class="badge bg-<?php echo $item['item_type'] === 'Book' ? 'info' : 'success'; ?>"><?php echo htmlspecialchars($item['item_type']); ?></span></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>$<?php echo number_format($item['price'], 2); ?></td>
                                <td>$<?php echo number_format($item['subtotal'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="subtotal-row">
                                <td colspan="4" class="text-end">Total:</td>
                                <td>$<?php echo number_format($total_price, 2); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Actions -->
                <div class="d-flex justify-content-between mb-5">
                    <div>
                        <button class="btn btn-outline-secondary print-button" onclick="window.print();">
                            <i class="fas fa-print me-2"></i>Print Order
                        </button>
                        <a href="mailto:support@school-inventory.com" class="btn btn-outline-primary">
                            <i class="fas fa-envelope me-2"></i>Contact Support
                        </a>
                    </div>
                    <a href="parent_dashboard.php" class="btn btn-success">
                        Go to Dashboard<i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </section>
        <?php endif; ?>
    </main>

    <!-- Footer -->
    <footer class="footer text-center mt-auto">
        <div class="container">
            <p class="mb-0">© 2025 School Inventory Management System | All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 