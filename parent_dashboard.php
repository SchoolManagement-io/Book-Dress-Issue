<?php
// Initialize session
session_start();

// BYPASS AUTHENTICATION FOR DIRECT ACCESS
// Instead of redirecting, set demo session variables if not logged in
if (!isset($_SESSION["parent_logged_in"]) || $_SESSION["parent_logged_in"] !== true) {
    // Set demo session data for direct access
    $_SESSION["parent_logged_in"] = true; // Set as logged in
    $_SESSION["parent_id"] = "P12345"; // Demo Parent ID
    $_SESSION["parent_name"] = "Rajesh Sharma"; // Indian parent name
    $_SESSION["parent_email"] = "rajesh.sharma@gmail.com"; // Demo Email
    $_SESSION["child_name"] = "Aarav Sharma"; // Indian child name
    $_SESSION["child_class"] = "Class 5"; // Demo Class
    $_SESSION["school_id"] = "SCH123"; // Demo School ID
    $_SESSION["school_name"] = "Delhi Public School"; // Indian school name
    $_SESSION["user_type"] = "parent"; // User type for logout handling
}

// Initialize variables for mock data display (in a real application, these would be fetched from the database)
$parent_name = $_SESSION["parent_name"] ?? "Rajesh Sharma";
$parent_email = $_SESSION["parent_email"] ?? "rajesh.sharma@gmail.com";

// Mock child data (in a real application, this would be fetched from the database using parent_id)
$child_name = $_SESSION["child_name"] ?? "Aarav Sharma";
$child_class = $_SESSION["child_class"] ?? "Class 5";
$school_id = $_SESSION["school_id"] ?? "SCH123";
$school_name = $_SESSION["school_name"] ?? "Delhi Public School";

// Mock inventory items (in a real application, these would be fetched from the database)
$inventory_items = [
    [
        'item_id' => 1,
        'item_name' => 'Mathematics Textbook - Grade 5',
        'item_type' => 'Book',
        'price' => 450.00,
        'stock' => 25,
        'image' => 'img/math_textbook.jpg'
    ],
    [
        'item_id' => 2,
        'item_name' => 'Science Textbook - Grade 5',
        'item_type' => 'Book',
        'price' => 520.00,
        'stock' => 18,
        'image' => 'img/science_textbook.jpg'
    ],
    [
        'item_id' => 3,
        'item_name' => 'English Grammar Workbook - Grade 5',
        'item_type' => 'Book',
        'price' => 380.00,
        'stock' => 30,
        'image' => 'img/english_workbook.jpg'
    ],
    [
        'item_id' => 4,
        'item_name' => 'School Uniform (Summer) - Size M',
        'item_type' => 'Uniform',
        'price' => 850.00,
        'stock' => 15,
        'image' => 'img/summer_uniform.jpg'
    ],
    [
        'item_id' => 5,
        'item_name' => 'School Uniform (Winter) - Size M',
        'item_type' => 'Uniform',
        'price' => 1250.00,
        'stock' => 12,
        'image' => 'img/winter_uniform.jpg'
    ],
    [
        'item_id' => 6,
        'item_name' => 'Physical Education Kit - Size M',
        'item_type' => 'Uniform',
        'price' => 750.00,
        'stock' => 20,
        'image' => 'img/pe_kit.jpg'
    ],
    [
        'item_id' => 7,
        'item_name' => 'Geometry Box - Standard',
        'item_type' => 'Stationery',
        'price' => 225.00,
        'stock' => 40,
        'image' => 'img/geometry_box.jpg'
    ],
    [
        'item_id' => 8,
        'item_name' => 'Art Supplies Kit',
        'item_type' => 'Stationery',
        'price' => 520.00,
        'stock' => 22,
        'image' => 'img/art_kit.jpg'
    ],
    [
        'item_id' => 9,
        'item_name' => 'School Diary - 2023-24',
        'item_type' => 'Stationery',
        'price' => 150.00,
        'stock' => 35,
        'image' => 'img/school_diary.jpg'
    ]
];

// Organize items by category
$textbooks = array_filter($inventory_items, function($item) {
    return $item['item_type'] == 'Book';
});

$uniforms = array_filter($inventory_items, function($item) {
    return $item['item_type'] == 'Uniform';
});

$stationery = array_filter($inventory_items, function($item) {
    return $item['item_type'] == 'Stationery';
});

// Mock order history (in a real application, these would be fetched from the database)
$order_history = [
    [
        'order_id' => 'ORD10045',
        'item_name' => 'English Grammar Workbook - Grade 5',
        'item_type' => 'Book',
        'quantity' => 1,
        'order_date' => '2025-04-15',
        'amount' => 380.00,
        'status' => 'Delivered'
    ],
    [
        'order_id' => 'ORD10036',
        'item_name' => 'School Uniform (Summer) - Size M',
        'item_type' => 'Uniform',
        'quantity' => 2,
        'order_date' => '2025-04-10',
        'amount' => 1700.00,
        'status' => 'Processing'
    ],
    [
        'order_id' => 'ORD10022',
        'item_name' => 'Mathematics Textbook - Grade 5',
        'item_type' => 'Book',
        'quantity' => 1,
        'order_date' => '2025-03-28',
        'amount' => 450.00,
        'status' => 'Delivered'
    ],
    [
        'order_id' => 'ORD10018',
        'item_name' => 'Geometry Box - Standard',
        'item_type' => 'Stationery',
        'quantity' => 1,
        'order_date' => '2025-03-15',
        'amount' => 225.00,
        'status' => 'Delivered'
    ],
    [
        'order_id' => 'ORD10010',
        'item_name' => 'School Diary - 2023-24',
        'item_type' => 'Stationery',
        'quantity' => 1,
        'order_date' => '2025-03-10',
        'amount' => 150.00,
        'status' => 'Delivered'
    ]
];

// Calculate total spending
$total_spending = array_sum(array_column($order_history, 'amount'));

// Calculate upcoming school events (in a real application, these would be fetched from the database)
$upcoming_events = [
    [
        'event_name' => 'Parent-Teacher Meeting',
        'event_date' => '2025-04-25',
        'event_time' => '10:00 AM - 1:00 PM',
        'event_location' => 'School Auditorium'
    ],
    [
        'event_name' => 'Annual Sports Day',
        'event_date' => '2025-05-10',
        'event_time' => '8:00 AM - 4:00 PM',
        'event_location' => 'School Playground'
    ],
    [
        'event_name' => 'Summer Vacation Begins',
        'event_date' => '2025-05-20',
        'event_time' => 'After School',
        'event_location' => '-'
    ]
];

// Process order form submission
$order_success = false;
$order_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_order"])) {
    // In a real application, you would validate input and insert into the database
    
    // Check if at least one item is selected
    $items_ordered = false;
    
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'quantity_') === 0 && $value > 0) {
            $items_ordered = true;
            break;
        }
    }
    
    if (!$items_ordered) {
        $order_error = "कृपया कम से कम एक आइटम चुनें। (Please select at least one item with quantity greater than 0)";
    } else {
        // In a real application, you would process the order here
        // Example of database insertion:
        /*
        try {
            // Begin transaction
            $pdo->beginTransaction();
            
            // Generate order ID
            $order_id = 'ORD' . rand(10000, 99999);
            $order_date = date('Y-m-d');
            
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'quantity_') === 0 && $value > 0) {
                    // Extract item_id from the field name
                    $item_id = substr($key, 9); // Remove 'quantity_' prefix
                    $quantity = intval($value);
                    
                    // Insert order item
                    $sql = "INSERT INTO orders (order_id, parent_id, item_id, quantity, order_date, status) 
                            VALUES (?, ?, ?, ?, ?, 'Processing')";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$order_id, $_SESSION['parent_id'], $item_id, $quantity, $order_date]);
                }
            }
            
            // Commit transaction
            $pdo->commit();
            $order_success = true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $order_error = "आपके ऑर्डर को प्रोसेस करते समय एक त्रुटि हुई है। कृपया बाद में पुन: प्रयास करें।";
        }
        */
        
        // For demonstration, simulate successful order
        $order_success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Dashboard - School Inventory Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Hind:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <!-- Sticky Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container-fluid px-4">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="img/logo.svg" alt="Logo" height="40" class="me-2">
                <span>School Inventory</span>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-shopping-cart me-1"></i> My Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-calendar-alt me-1"></i> School Calendar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fas fa-headset me-1"></i> Support</a>
                    </li>
                </ul>
                
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                            <?php
                            // Get parent initials
                            $names = explode(' ', $parent_name);
                            $initials = '';
                            foreach ($names as $name) {
                                $initials .= strtoupper(substr($name, 0, 1));
                            }
                            echo $initials;
                            ?>
                        </div>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars($parent_name); ?></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>My Profile</a></li>
                        <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Main Content -->
    <div class="container py-4">
        <!-- Dashboard Header -->
        <div class="row mb-4">
            <div class="col-md-8">
                <h1 class="dashboard-greeting" id="greeting"></h1>
                <p class="text-muted">Here's everything you need for <?php echo htmlspecialchars($child_name); ?>'s educational needs.</p>
                <div class="d-flex align-items-center mt-3">
                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-graduation-cap text-primary"></i>
                    </div>
                    <div>
                        <div class="text-muted">Student</div>
                        <div class="fs-5 fw-medium"><?php echo htmlspecialchars($child_name); ?> • Class <?php echo htmlspecialchars($child_class); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 d-flex justify-content-md-end align-items-center mt-4 mt-md-0">
                <button class="btn btn-primary me-2">
                    <i class="fas fa-shopping-cart me-2"></i>View Cart
                </button>
                <button class="btn btn-outline-primary">
                    <i class="fas fa-history me-2"></i>Order History
                </button>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-4">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="stats-card-icon bg-info">
                                    <i class="fas fa-shopping-bag"></i>
                                </div>
                                <div class="text-info">
                                    <i class="fas fa-sync-alt"></i> Flip for details
                                </div>
                            </div>
                            <div class="stats-card-title">My Orders</div>
                            <div class="stats-card-value counter-value" data-target="<?php echo count($order_history); ?>"><?php echo count($order_history); ?></div>
                            <div class="stats-card-increase">
                                <i class="fas fa-arrow-up me-1"></i> <?php echo count(array_filter($order_history, function($order) { return $order['status'] === 'Pending'; })); ?> pending
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <h4>Order Summary</h4>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Completed:</span>
                                    <span><?php echo count(array_filter($order_history, function($order) { return $order['status'] === 'Delivered'; })); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Processing:</span>
                                    <span><?php echo count(array_filter($order_history, function($order) { return $order['status'] === 'Processing'; })); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Pending:</span>
                                    <span><?php echo count(array_filter($order_history, function($order) { return $order['status'] === 'Pending'; })); ?></span>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-light">View All Orders</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="stats-card-icon bg-success">
                                    <i class="fas fa-rupee-sign"></i>
                                </div>
                                <div class="text-success">
                                    <i class="fas fa-sync-alt"></i> Flip for details
                                </div>
                            </div>
                            <div class="stats-card-title">Total Spent</div>
                            <div class="stats-card-value">₹<span class="counter-value" data-target="<?php echo $total_spending; ?>" data-prefix="₹"><?php echo number_format($total_spending); ?></span></div>
                            <div class="stats-card-increase">
                                <i class="fas fa-clock me-1"></i> This academic year
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <h4>Spending Breakdown</h4>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Textbooks:</span>
                                    <span>₹<?php echo number_format($total_spending * 0.45); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Uniforms:</span>
                                    <span>₹<?php echo number_format($total_spending * 0.35); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Stationery:</span>
                                    <span>₹<?php echo number_format($total_spending * 0.20); ?></span>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-light">View Detailed Report</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="flip-card">
                    <div class="flip-card-inner">
                        <div class="flip-card-front">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="stats-card-icon bg-warning">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="text-warning">
                                    <i class="fas fa-sync-alt"></i> Flip for details
                                </div>
                            </div>
                            <div class="stats-card-title">School Events</div>
                            <div class="stats-card-value counter-value" data-target="5">5</div>
                            <div class="stats-card-increase">
                                <i class="fas fa-calendar-alt me-1"></i> Upcoming events
                            </div>
                        </div>
                        <div class="flip-card-back">
                            <h4>Upcoming Events</h4>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sports Day:</span>
                                    <span>May 15</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Annual Day:</span>
                                    <span>Jun 10</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>PTM:</span>
                                    <span>May 25</span>
                                </div>
                            </div>
                            <a href="#" class="btn btn-sm btn-light">View School Calendar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Dashboard Content -->
        <div class="row g-4">
            <!-- Left Column - Inventory Items -->
            <div class="col-lg-8">
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Shop Inventory</h3>
                        <div class="d-flex">
                            <div class="search-box me-2">
                                <i class="fas fa-search"></i>
                                <input type="text" class="form-control" placeholder="Search items..." id="inventory-search">
                            </div>
                            <button class="btn btn-primary btn-sm">
                                <i class="fas fa-filter me-1"></i> Filter
                            </button>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <!-- Category Tabs -->
                        <ul class="nav nav-tabs mb-4" id="inventoryTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-tab-toggle="tab-all">All Items</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab-toggle="tab-textbooks">Textbooks</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab-toggle="tab-stationery">Stationery</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-tab-toggle="tab-uniforms">Uniforms</a>
                            </li>
                        </ul>
                        
                        <!-- Tab Content -->
                        <div class="tab-content">
                            <!-- All Items Tab -->
                            <div class="tab-pane active" id="tab-all">
                                <div class="row g-4">
                                    <?php 
                                    // Combine all inventory items
                                    $all_items = array_merge($textbooks, $stationery, $uniforms);
                                    
                                    foreach ($all_items as $item): 
                                    ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="inventory-item hover-card" data-item-id="<?php echo $item['item_id']; ?>">
                                            <div class="inventory-item-img-wrapper">
                                                <img src="<?php echo $item['image']; ?>" class="inventory-item-img" alt="<?php echo htmlspecialchars($item['item_name']); ?>">
                                                <?php if ($item['stock'] < 10): ?>
                                                <span class="inventory-item-badge low-stock">Low Stock</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="inventory-item-content">
                                                <h4 class="inventory-item-title"><?php echo htmlspecialchars($item['item_name']); ?></h4>
                                                <div class="inventory-item-price">₹<?php echo number_format($item['price'], 2); ?></div>
                                                <div class="inventory-item-stock">
                                                    <?php if ($item['stock'] > 0): ?>
                                                    <span class="text-success"><i class="fas fa-check-circle me-1"></i> In Stock</span>
                                                    <?php else: ?>
                                                    <span class="text-danger"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>
                                                    <?php endif; ?>
                                                </div>
                                                <button class="btn btn-primary w-100 mt-3 add-to-cart-btn" <?php echo $item['stock'] === 0 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Category-specific tabs -->
                            <div class="tab-pane" id="tab-textbooks">
                                <!-- Textbooks content - will be populated with JavaScript -->
                            </div>
                            
                            <div class="tab-pane" id="tab-stationery">
                                <!-- Stationery content - will be populated with JavaScript -->
                            </div>
                            
                            <div class="tab-pane" id="tab-uniforms">
                                <!-- Uniforms content - will be populated with JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Spending Analysis & Recent Orders -->
            <div class="col-lg-4">
                <!-- Spending Analysis -->
                <div class="dashboard-card mb-4">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Spending Analysis</h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="chart-container">
                            <canvas id="spendingChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="dashboard-card mb-4">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Recent Orders</h3>
                        <a href="#" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <?php if (empty($order_history)): ?>
                        <div class="dashboard-card-empty">
                            <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                            <p>No orders yet</p>
                            <button class="btn btn-sm btn-primary">Start Shopping</button>
                        </div>
                        <?php else: ?>
                        <div class="recent-orders-list">
                            <?php 
                            // Sort orders by date, newest first
                            usort($order_history, function($a, $b) {
                                return strtotime($b['order_date']) - strtotime($a['order_date']);
                            });
                            
                            // Show only the 3 most recent orders
                            $recent_orders = array_slice($order_history, 0, 3);
                            
                            foreach ($recent_orders as $order): 
                            ?>
                            <div class="recent-order-item">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="fw-medium"><?php echo htmlspecialchars($order['order_id']); ?></div>
                                    <span class="order-badge badge-<?php echo strtolower($order['status']); ?>">
                                        <?php echo htmlspecialchars($order['status']); ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between text-muted small mb-2">
                                    <div><?php echo date('d M Y', strtotime($order['order_date'])); ?></div>
                                    <div><?php echo $order['quantity']; ?> items</div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fs-5 fw-medium">₹<?php echo number_format($order['amount'], 2); ?></div>
                                    <a href="#" class="btn btn-sm btn-outline-primary">View</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Upcoming Events -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Upcoming Events</h3>
                        <a href="#" class="btn btn-sm btn-outline-primary">Calendar</a>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="upcoming-events-list">
                            <div class="upcoming-event-item">
                                <div class="upcoming-event-date">15 <span>May</span></div>
                                <div class="upcoming-event-content">
                                    <h4 class="upcoming-event-title">Annual Sports Day</h4>
                                    <p class="upcoming-event-text">All students must wear sports uniform.</p>
                                    <div class="upcoming-event-meta">
                                        <span><i class="fas fa-map-marker-alt me-1"></i> School Ground</span>
                                        <span><i class="fas fa-clock me-1"></i> 9:00 AM</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="upcoming-event-item">
                                <div class="upcoming-event-date">25 <span>May</span></div>
                                <div class="upcoming-event-content">
                                    <h4 class="upcoming-event-title">Parent-Teacher Meeting</h4>
                                    <p class="upcoming-event-text">Discuss your child's progress report.</p>
                                    <div class="upcoming-event-meta">
                                        <span><i class="fas fa-map-marker-alt me-1"></i> Class <?php echo htmlspecialchars($child_class); ?></span>
                                        <span><i class="fas fa-clock me-1"></i> 10:30 AM</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="upcoming-event-item">
                                <div class="upcoming-event-date">10 <span>Jun</span></div>
                                <div class="upcoming-event-content">
                                    <h4 class="upcoming-event-title">Annual Day Celebration</h4>
                                    <p class="upcoming-event-text">Cultural performances by students.</p>
                                    <div class="upcoming-event-meta">
                                        <span><i class="fas fa-map-marker-alt me-1"></i> School Auditorium</span>
                                        <span><i class="fas fa-clock me-1"></i> 5:00 PM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-light mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5 class="mb-3">Contact Support</h5>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-phone-alt me-2 text-primary"></i>
                        <span>+91 98765 43210</span>
                    </div>
                    <div class="d-flex align-items-center mb-2">
                        <i class="fas fa-envelope me-2 text-primary"></i>
                        <span>support@schoolinventory.com</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <i class="fas fa-clock me-2 text-primary"></i>
                        <span>Mon-Fri: 9:00 AM - 5:00 PM</span>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary"><i class="fas fa-chevron-right me-1 text-primary"></i> Dashboard</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary"><i class="fas fa-chevron-right me-1 text-primary"></i> Orders</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none text-secondary"><i class="fas fa-chevron-right me-1 text-primary"></i> School Calendar</a></li>
                        <li><a href="#" class="text-decoration-none text-secondary"><i class="fas fa-chevron-right me-1 text-primary"></i> Help Center</a></li>
                    </ul>
                </div>
                
                <div class="col-md-3">
                    <h5 class="mb-3">School App</h5>
                    <p class="small mb-3">Download our mobile app for a better experience.</p>
                    <div class="d-flex">
                        <a href="#" class="btn btn-sm btn-outline-secondary me-2">
                            <i class="fab fa-google-play me-1"></i> Play Store
                        </a>
                        <a href="#" class="btn btn-sm btn-outline-secondary">
                            <i class="fab fa-apple me-1"></i> App Store
                        </a>
                    </div>
                </div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="small text-secondary mb-2 mb-md-0">
                    &copy; <?php echo date('Y'); ?> School Inventory Management System. All rights reserved.
                </div>
                <div>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="text-secondary me-3"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-secondary"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set dynamic greeting
            const greeting = document.getElementById('greeting');
            if (greeting) {
                // Extract parent name for the greeting
                const parentName = "<?php echo explode(' ', $parent_name)[0]; ?>";
                greeting.textContent = getIndianGreeting(parentName);
            }
            
            // Initialize spending chart
            const spendingCtx = document.getElementById('spendingChart').getContext('2d');
            const spendingChart = new Chart(spendingCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Textbooks', 'Uniforms', 'Stationery'],
                    datasets: [{
                        data: [45, 35, 20], // Percentage breakdown
                        backgroundColor: [
                            '#0d6efd', // Textbooks
                            '#6f42c1', // Uniforms
                            '#20c997', // Stationery
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                font: {
                                    size: 12
                                },
                                padding: 15
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    const amount = Math.round(<?php echo $total_spending; ?> * value / 100);
                                    return `${context.label}: ${percentage}% (₹${amount.toLocaleString('en-IN')})`;
                                }
                            }
                        }
                    }
                }
            });
            
            // Load category tabs
            const categories = {
                'textbooks': <?php echo json_encode($textbooks); ?>,
                'stationery': <?php echo json_encode($stationery); ?>,
                'uniforms': <?php echo json_encode($uniforms); ?>
            };
            
            Object.keys(categories).forEach(category => {
                const tabContent = document.getElementById(`tab-${category}`);
                if (tabContent) {
                    let html = '<div class="row g-4">';
                    
                    categories[category].forEach(item => {
                        html += `
                        <div class="col-md-6 col-lg-6">
                            <div class="inventory-item hover-card" data-item-id="${item.item_id}">
                                <div class="inventory-item-img-wrapper">
                                    <img src="${item.image}" class="inventory-item-img" alt="${item.item_name}">
                                    ${item.stock < 10 ? '<span class="inventory-item-badge low-stock">Low Stock</span>' : ''}
                                </div>
                                <div class="inventory-item-content">
                                    <h4 class="inventory-item-title">${item.item_name}</h4>
                                    <div class="inventory-item-price">₹${item.price.toLocaleString('en-IN')}</div>
                                    <div class="inventory-item-stock">
                                        ${item.stock > 0 ? 
                                            '<span class="text-success"><i class="fas fa-check-circle me-1"></i> In Stock</span>' : 
                                            '<span class="text-danger"><i class="fas fa-times-circle me-1"></i> Out of Stock</span>'
                                        }
                                    </div>
                                    <button class="btn btn-primary w-100 mt-3 add-to-cart-btn" ${item.stock === 0 ? 'disabled' : ''}>
                                        <i class="fas fa-shopping-cart me-2"></i>Add to Cart
                                    </button>
                                </div>
                            </div>
                        </div>
                        `;
                    });
                    
                    html += '</div>';
                    tabContent.innerHTML = html;
                }
            });
            
            // Animate counters
            animateCounters();
            
            // Item search functionality
            const searchInput = document.getElementById('inventory-search');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase();
                    const items = document.querySelectorAll('.inventory-item');
                    
                    items.forEach(item => {
                        const title = item.querySelector('.inventory-item-title').textContent.toLowerCase();
                        if (title.includes(query)) {
                            item.style.display = '';
                        } else {
                            item.style.display = 'none';
                        }
                    });
                });
            }
            
            // Add to cart functionality
            const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    
                    const item = this.closest('.inventory-item');
                    const itemId = item.getAttribute('data-item-id');
                    const itemName = item.querySelector('.inventory-item-title').textContent;
                    
                    // Change button text and style temporarily
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check me-2"></i>Added to Cart';
                    this.classList.remove('btn-primary');
                    this.classList.add('btn-success');
                    
                    // Show notification
                    showNotification(`"${itemName}" has been added to your cart.`, 'success');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        this.innerHTML = originalHtml;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-primary');
                    }, 2000);
                });
            });
            
            // Check for festivals and display notification
            const festival = getIndianFestival();
            if (festival) {
                showNotification(`Happy ${festival.name}! ${festival.message}`, 'success');
            }
        });
    </script>
</body>
</html> 