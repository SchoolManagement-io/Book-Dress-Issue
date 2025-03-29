<?php
// Initialize session
session_start();

// BYPASS AUTHENTICATION FOR DIRECT ACCESS
// Instead of redirecting, set demo session variables if not logged in
if (!isset($_SESSION["school_logged_in"]) || $_SESSION["school_logged_in"] !== true) {
    // Set demo session data for direct access
    $_SESSION["school_logged_in"] = true; // Set as logged in
    $_SESSION["school_id"] = "SCH123"; // Demo School ID
    $_SESSION["school_name"] = "Delhi Public School"; // School name
    $_SESSION["user_type"] = "school"; // User type for logout handling
}

// Get school information from session
$school_id = $_SESSION["school_id"] ?? "SCH123";
$school_name = $_SESSION["school_name"] ?? "Delhi Public School";

// Mock data for inventory
$inventory_categories = [
    [
        "name" => "Textbooks",
        "total_items" => 120,
        "low_stock" => 5,
        "icon" => "book",
        "color" => "primary"
    ],
    [
        "name" => "Stationery",
        "total_items" => 350,
        "low_stock" => 12,
        "icon" => "pencil-alt",
        "color" => "info"
    ],
    [
        "name" => "Uniforms",
        "total_items" => 200,
        "low_stock" => 8,
        "icon" => "tshirt",
        "color" => "success"
    ],
    [
        "name" => "Sports",
        "total_items" => 85,
        "low_stock" => 3,
        "icon" => "basketball-ball",
        "color" => "warning"
    ]
];

// Mock data for recent orders
$recent_orders = [
    [
        "id" => "ORD-2023-101",
        "parent" => "Rajesh Sharma",
        "student" => "Aarav Sharma",
        "date" => "2023-08-28",
        "items" => 3,
        "total" => 1890,
        "status" => "Delivered"
    ],
    [
        "id" => "ORD-2023-102",
        "parent" => "Priya Patel",
        "student" => "Ananya Patel",
        "date" => "2023-08-30",
        "items" => 5,
        "total" => 2250,
        "status" => "Processing"
    ],
    [
        "id" => "ORD-2023-103",
        "parent" => "Vikram Mehta",
        "student" => "Arjun Mehta",
        "date" => "2023-09-01",
        "items" => 2,
        "total" => 1550,
        "status" => "Pending"
    ],
    [
        "id" => "ORD-2023-104",
        "parent" => "Neha Kapoor",
        "student" => "Ishaan Kapoor",
        "date" => "2023-09-03",
        "items" => 4,
        "total" => 2750,
        "status" => "Processing"
    ],
    [
        "id" => "ORD-2023-105",
        "parent" => "Amit Singh",
        "student" => "Diya Singh",
        "date" => "2023-09-05",
        "items" => 1,
        "total" => 850,
        "status" => "Pending"
    ]
];

// Mock data for low stock items
$low_stock_items = [
    ["name" => "Grade 5 Science Textbook", "category" => "Textbooks", "stock" => 5, "required" => 30],
    ["name" => "School Shirt (Size L)", "category" => "Uniforms", "stock" => 8, "required" => 25],
    ["name" => "Art Supplies Kit", "category" => "Stationery", "stock" => 4, "required" => 20],
    ["name" => "Basketball", "category" => "Sports", "stock" => 2, "required" => 10],
    ["name" => "Grade 3 Mathematics Textbook", "category" => "Textbooks", "stock" => 7, "required" => 35],
    ["name" => "School Tie", "category" => "Uniforms", "stock" => 9, "required" => 40]
];

// Calculate total inventory items
$total_inventory = array_sum(array_column($inventory_categories, 'total_items'));

// Calculate total low stock items
$total_low_stock = array_sum(array_column($inventory_categories, 'low_stock'));

// Calculate the total number of orders
$total_orders = count($recent_orders);

// Calculate total revenue
$total_revenue = array_sum(array_column($recent_orders, 'total'));

// Mock data for students by class
$students_by_class = [
    ["class" => "Class 1", "count" => 45],
    ["class" => "Class 2", "count" => 42],
    ["class" => "Class 3", "count" => 38],
    ["class" => "Class 4", "count" => 40],
    ["class" => "Class 5", "count" => 35],
    ["class" => "Class 6", "count" => 32],
    ["class" => "Class 7", "count" => 30],
    ["class" => "Class 8", "count" => 28],
    ["class" => "Class 9", "count" => 25],
    ["class" => "Class 10", "count" => 22],
    ["class" => "Class 11", "count" => 20],
    ["class" => "Class 12", "count" => 18]
];

// Mock data for monthly revenue
$monthly_revenue = [
    ["month" => "Jan", "revenue" => 125000],
    ["month" => "Feb", "revenue" => 142000],
    ["month" => "Mar", "revenue" => 165000],
    ["month" => "Apr", "revenue" => 185000],
    ["month" => "May", "revenue" => 196000],
    ["month" => "Jun", "revenue" => 148000],
    ["month" => "Jul", "revenue" => 152000],
    ["month" => "Aug", "revenue" => 192000],
    ["month" => "Sep", "revenue" => 205000],
    ["month" => "Oct", "revenue" => 176000],
    ["month" => "Nov", "revenue" => 168000],
    ["month" => "Dec", "revenue" => 147000]
];

// Mock data for upcoming school events
$upcoming_events = [
    [
        'event_name' => 'Parent-Teacher Meeting',
        'event_date' => '2023-09-25',
        'event_time' => '10:00 AM - 1:00 PM',
        'event_location' => 'School Auditorium'
    ],
    [
        'event_name' => 'Annual Sports Day',
        'event_date' => '2023-10-10',
        'event_time' => '8:00 AM - 4:00 PM',
        'event_location' => 'School Playground'
    ],
    [
        'event_name' => 'Independence Day Celebration',
        'event_date' => '2023-08-15',
        'event_time' => '9:00 AM - 11:00 AM',
        'event_location' => 'School Assembly Ground'
    ]
];

// Mock data for inventory distribution (for pie chart)
$inventory_distribution = [
    ["category" => "Textbooks", "percentage" => 35],
    ["category" => "Stationery", "percentage" => 25],
    ["category" => "Uniforms", "percentage" => 20],
    ["category" => "Sports", "percentage" => 10],
    ["category" => "Electronics", "percentage" => 5],
    ["category" => "Other", "percentage" => 5]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Dashboard - Inventory Management System</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
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
    
    <style>
        /* Custom styles for school dashboard */
        body {
            background-color: var(--gray-100);
        }
        
        .sidebar {
            background-color: var(--gray-900);
            color: white;
            min-height: 100vh;
            padding-top: 1.5rem;
            position: fixed;
            top: 0;
            left: 0;
            width: 250px;
            transition: var(--transition-base);
            z-index: var(--z-index-fixed);
        }
        
        .sidebar-header {
            padding: 0 1.5rem 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            align-items: center;
        }
        
        .sidebar-header img {
            width: 40px;
            height: 40px;
            margin-right: 10px;
        }
        
        .sidebar-header h5 {
            color: white;
            margin-bottom: 0;
            font-size: 1.1rem;
        }
        
        .sidebar-school-info {
            font-size: 0.8rem;
            opacity: 0.7;
        }
        
        .sidebar-menu {
            padding: 1.5rem 0;
        }
        
        .sidebar-menu a {
            display: block;
            padding: 0.75rem 1.5rem;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: var(--transition-base);
            position: relative;
        }
        
        .sidebar-menu a::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 0;
            background-color: var(--primary);
            transition: width 0.2s ease;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.05);
        }
        
        .sidebar-menu a.active::before {
            width: 4px;
        }
        
        .sidebar-menu i {
            margin-right: 0.5rem;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            padding: 2rem;
            margin-left: 250px;
        }
        
        .page-title {
            margin-bottom: 2rem;
        }
        
        .user-greeting {
            font-weight: 500;
            color: var(--gray-600);
        }
        
        .stats-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-sm);
            padding: 1.5rem;
            height: 100%;
            transition: var(--transition-base);
            position: relative;
            overflow: hidden;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--box-shadow);
        }
        
        .stats-card-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 15px;
            margin-bottom: 1.25rem;
            color: white;
        }
        
        .stats-card-title {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--gray-600);
            margin-bottom: 0.5rem;
        }
        
        .stats-card-value {
            font-size: 1.75rem;
            font-weight: 600;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
            font-family: var(--font-family-sans-serif);
        }
        
        .stats-card-increase {
            font-size: 0.875rem;
            color: var(--success);
            display: flex;
            align-items: center;
        }
        
        .dashboard-card {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow-sm);
            height: 100%;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .dashboard-card-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .dashboard-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .dashboard-card-body {
            padding: 1.5rem;
        }
        
        .dashboard-card-empty {
            text-align: center;
            padding: 3rem 0;
            color: var(--gray-500);
        }
        
        .inventory-category {
            display: flex;
            align-items: center;
            padding: 1rem;
            border-radius: var(--border-radius);
            transition: var(--transition-base);
            margin-bottom: 1rem;
            background-color: var(--gray-100);
        }
        
        .inventory-category:hover {
            transform: translateX(5px);
            background-color: var(--gray-200);
        }
        
        .inventory-category-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            color: white;
        }
        
        .inventory-category-details {
            flex: 1;
        }
        
        .inventory-category-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .inventory-category-status {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .inventory-category-status-item {
            font-size: 0.875rem;
            display: flex;
            align-items: center;
        }
        
        .inventory-category-status-item i {
            margin-right: 0.25rem;
        }
        
        .order-item {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            transition: var(--transition-base);
        }
        
        .order-item:hover {
            background-color: var(--gray-100);
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .order-badge {
            padding: 0.35rem 0.65rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .badge-pending {
            background-color: rgba(255, 193, 7, 0.15);
            color: var(--warning);
        }
        
        .badge-processing {
            background-color: rgba(13, 202, 240, 0.15);
            color: var(--info);
        }
        
        .badge-delivered {
            background-color: rgba(25, 135, 84, 0.15);
            color: var(--success);
        }
        
        .low-stock-item {
            padding: 1rem;
            border-left: 3px solid var(--warning);
            margin-bottom: 1rem;
            background-color: rgba(255, 193, 7, 0.05);
            border-radius: 0 var(--border-radius) var(--border-radius) 0;
            transition: var(--transition-base);
        }
        
        .low-stock-item:hover {
            transform: translateX(5px);
            background-color: rgba(255, 193, 7, 0.1);
        }
        
        .low-stock-item-name {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .low-stock-item-details {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        
        .low-stock-item-status {
            font-size: 0.875rem;
            color: var(--gray-700);
        }
        
        .low-stock-item-category {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 50px;
            background-color: rgba(25, 135, 84, 0.1);
            color: var(--primary);
        }
        
        .progress-thin {
            height: 0.5rem;
            border-radius: 1rem;
        }
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        /* Mobile sidebar toggle */
        .sidebar-toggle {
            background-color: var(--primary);
            color: var(--white);
            border: none;
            border-radius: var(--border-radius-circle);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            z-index: var(--z-index-fixed);
            box-shadow: var(--box-shadow);
            transition: var(--transition-base);
        }
        
        .sidebar-toggle:hover {
            background-color: var(--primary-dark);
        }
        
        /* Search box */
        .search-box {
            position: relative;
            max-width: 300px;
        }
        
        .search-box input {
            padding-left: 2.5rem;
            border-radius: var(--border-radius-pill);
            border: 1px solid var(--gray-300);
            transition: var(--transition-base);
        }
        
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        
        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-500);
        }
        
        /* Responsive styles */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .sidebar.show {
                transform: translateX(0);
            }
            
            .sidebar-backdrop {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: calc(var(--z-index-fixed) - 1);
                display: none;
            }
            
            .sidebar-backdrop.show {
                display: block;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Mobile Sidebar Toggle Button -->
    <button class="sidebar-toggle d-lg-none" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Backdrop (Mobile) -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <img src="img/logo.svg" alt="School Logo">
            <div>
                <h5><?php echo htmlspecialchars($school_name); ?></h5>
                <div class="sidebar-school-info">School ID: <?php echo htmlspecialchars($school_id); ?></div>
            </div>
        </div>
        
        <nav class="sidebar-menu">
            <a href="school_dashboard.php" class="active">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>
            <a href="#">
                <i class="fas fa-book"></i> Inventory
            </a>
            <a href="#">
                <i class="fas fa-shopping-cart"></i> Orders
            </a>
            <a href="#">
                <i class="fas fa-users"></i> Students
            </a>
            <a href="#">
                <i class="fas fa-chart-bar"></i> Reports
            </a>
            <a href="#">
                <i class="fas fa-cog"></i> Settings
            </a>
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="page-title mb-0" id="greeting">Welcome back!</h1>
                <p class="text-muted">Here's what's happening with your inventory today.</p>
            </div>
            
            <div class="search-box d-none d-md-block">
                <i class="fas fa-search"></i>
                <input type="text" class="form-control" placeholder="Search...">
            </div>
        </div>
        
        <!-- Stats Row -->
        <div class="row g-4 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="stats-card">
                    <div class="stats-card-icon bg-primary">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <div class="stats-card-title">Total Inventory</div>
                    <div class="stats-card-value counter-value" data-target="<?php echo $total_inventory; ?>"><?php echo $total_inventory; ?></div>
                    <div class="stats-card-increase">
                        <i class="fas fa-arrow-up me-1"></i> 8.3% from last month
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="stats-card">
                    <div class="stats-card-icon bg-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stats-card-title">Low Stock Items</div>
                    <div class="stats-card-value counter-value" data-target="<?php echo $total_low_stock; ?>"><?php echo $total_low_stock; ?></div>
                    <div class="stats-card-increase text-warning">
                        <i class="fas fa-arrow-up me-1"></i> Needs attention
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="stats-card">
                    <div class="stats-card-icon bg-info">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="stats-card-title">Total Orders</div>
                    <div class="stats-card-value counter-value" data-target="<?php echo $total_orders; ?>"><?php echo $total_orders; ?></div>
                    <div class="stats-card-increase">
                        <i class="fas fa-arrow-up me-1"></i> 12.5% from last month
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 col-lg-3">
                <div class="stats-card">
                    <div class="stats-card-icon bg-success">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <div class="stats-card-title">Total Revenue</div>
                    <div class="stats-card-value">₹<span class="counter-value" data-target="<?php echo $total_revenue; ?>" data-prefix="₹"><?php echo number_format($total_revenue); ?></span></div>
                    <div class="stats-card-increase">
                        <i class="fas fa-arrow-up me-1"></i> 5.7% from last month
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Rows -->
        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Revenue Chart -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Monthly Revenue</h3>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary active">Monthly</button>
                            <button type="button" class="btn btn-outline-secondary">Quarterly</button>
                            <button type="button" class="btn btn-outline-secondary">Yearly</button>
                        </div>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="chart-container">
                            <canvas id="revenueChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Recent Orders</h3>
                        <a href="#" class="btn btn-sm btn-primary">View All</a>
                    </div>
                    <div class="dashboard-card-body p-0">
                        <?php if (empty($recent_orders)): ?>
                        <div class="dashboard-card-empty">
                            <i class="fas fa-shopping-bag fa-3x mb-3 text-muted"></i>
                            <p>No recent orders</p>
                        </div>
                        <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Student</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($order['id']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center me-2" style="width: 35px; height: 35px;">
                                                    <?php
                                                    // Get initials from student name
                                                    $names = explode(' ', $order['student']);
                                                    $initials = '';
                                                    foreach ($names as $name) {
                                                        $initials .= strtoupper(substr($name, 0, 1));
                                                    }
                                                    echo $initials;
                                                    ?>
                                                </div>
                                                <div>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($order['student']); ?></div>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($order['parent']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M Y', strtotime($order['date'])); ?></td>
                                        <td>₹<?php echo number_format($order['total']); ?></td>
                                        <td>
                                            <span class="order-badge badge-<?php echo strtolower($order['status']); ?>">
                                                <?php echo htmlspecialchars($order['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-icon btn-ghost-secondary rounded-circle" type="button" id="dropdownMenuButton_<?php echo $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton_<?php echo $order['id']; ?>">
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-eye me-2"></i>View Details</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-edit me-2"></i>Update Status</a></li>
                                                    <li><a class="dropdown-item" href="#"><i class="fas fa-print me-2"></i>Print Invoice</a></li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Inventory Distribution -->
                <div class="dashboard-card mb-4">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Inventory Distribution</h3>
                    </div>
                    <div class="dashboard-card-body">
                        <div class="chart-container">
                            <canvas id="inventoryChart"></canvas>
                        </div>
                    </div>
                </div>
                
                <!-- Low Stock Items -->
                <div class="dashboard-card">
                    <div class="dashboard-card-header">
                        <h3 class="dashboard-card-title">Low Stock Items</h3>
                        <a href="#" class="btn btn-sm btn-warning">Restock</a>
                    </div>
                    <div class="dashboard-card-body">
                        <?php if (empty($low_stock_items)): ?>
                        <div class="dashboard-card-empty">
                            <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                            <p>All items are in stock</p>
                        </div>
                        <?php else: ?>
                        <?php foreach ($low_stock_items as $item): ?>
                        <div class="low-stock-item">
                            <div class="low-stock-item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                            <div class="low-stock-item-details">
                                <span class="low-stock-item-category"><?php echo htmlspecialchars($item['category']); ?></span>
                                <span class="low-stock-item-status">
                                    <strong><?php echo $item['stock']; ?></strong> / <?php echo $item['required']; ?>
                                </span>
                            </div>
                            <div class="progress progress-thin mt-2">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo ($item['stock'] / $item['required']) * 100; ?>%" aria-valuenow="<?php echo $item['stock']; ?>" aria-valuemin="0" aria-valuemax="<?php echo $item['required']; ?>"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set dynamic greeting based on time of day
            const greeting = document.getElementById('greeting');
            if (greeting) {
                // Extract school name for the greeting
                const schoolName = "<?php echo explode(' ', $school_name)[0]; ?>";
                greeting.textContent = getIndianGreeting(schoolName);
            }
            
            // Mobile sidebar toggle
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                    sidebarBackdrop.classList.toggle('show');
                });
            }
            
            if (sidebarBackdrop) {
                sidebarBackdrop.addEventListener('click', function() {
                    sidebar.classList.remove('show');
                    sidebarBackdrop.classList.remove('show');
                });
            }
            
            // Initialize revenue chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            
            // Prepare data from PHP
            const months = <?php echo json_encode(array_column($monthly_revenue, 'month')); ?>;
            const revenues = <?php echo json_encode(array_column($monthly_revenue, 'revenue')); ?>;
            
            const revenueChart = new Chart(revenueCtx, {
                type: 'bar',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Monthly Revenue (₹)',
                        data: revenues,
                        backgroundColor: 'rgba(25, 135, 84, 0.2)',
                        borderColor: '#198754',
                        borderWidth: 1,
                        borderRadius: 5,
                        isCurrency: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN');
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return 'Revenue: ₹' + context.parsed.y.toLocaleString('en-IN');
                                }
                            }
                        }
                    }
                }
            });
            
            // Initialize inventory distribution chart
            const inventoryCtx = document.getElementById('inventoryChart').getContext('2d');
            
            // Prepare data from PHP
            const categories = <?php echo json_encode(array_column($inventory_distribution, 'category')); ?>;
            const percentages = <?php echo json_encode(array_column($inventory_distribution, 'percentage')); ?>;
            
            const inventoryChart = new Chart(inventoryCtx, {
                type: 'doughnut',
                data: {
                    labels: categories,
                    datasets: [{
                        data: percentages,
                        backgroundColor: [
                            '#0d6efd', // Primary - Textbooks
                            '#6f42c1', // Purple - Stationery
                            '#198754', // Success - Uniforms
                            '#fd7e14', // Orange - Sports
                            '#0dcaf0', // Info - Electronics
                            '#adb5bd'  // Secondary - Other
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
                                    return context.label + ': ' + context.parsed + '%';
                                }
                            }
                        }
                    }
                }
            });
            
            // Initialize festival notifications
            const festival = getIndianFestival();
            
            if (festival) {
                showNotification(`Happy ${festival.name}! ${festival.message}`, 'success');
            }
        });
    </script>
</body>
</html> 