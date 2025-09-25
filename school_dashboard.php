<?php
// Start session
session_start();

// Check if user is logged in as a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header("Location: school_login.php");
    exit();
}

// Get school information from session
$school_id = $_SESSION['user_id'] ?? '';
$school_name = $_SESSION['school_name'] ?? 'School Dashboard';
$school_logo = $_SESSION['school_logo'] ?? 'assets/default_school_logo.svg';

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

// Get dashboard statistics
$stats = [
    'total_students' => 0,
    'total_inventory' => 0,
    'pending_orders' => 0,
    'total_orders' => 0,
    'recent_orders' => [],
    'low_stock_items' => [],
    'class_distribution' => []
];

// Get total students
$students_query = "SELECT COUNT(*) as count FROM students WHERE school_id = $school_id";
$students_result = $conn->query($students_query);
if ($students_result && $students_result->num_rows > 0) {
    $stats['total_students'] = $students_result->fetch_assoc()['count'];
}

// Get total inventory items
$inventory_query = "SELECT COUNT(*) as count FROM inventory WHERE school_id = $school_id";
$inventory_result = $conn->query($inventory_query);
if ($inventory_result && $inventory_result->num_rows > 0) {
    $stats['total_inventory'] = $inventory_result->fetch_assoc()['count'];
}

// Get order statistics
$orders_query = "
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM orders 
    WHERE school_id = $school_id
";
$orders_result = $conn->query($orders_query);
if ($orders_result && $orders_result->num_rows > 0) {
    $orders_data = $orders_result->fetch_assoc();
    $stats['total_orders'] = $orders_data['total'];
    $stats['pending_orders'] = $orders_data['pending'];
}

// Get recent orders (last 5)
$recent_orders_query = "
    SELECT 
        o.id,
        o.order_number,
        o.created_at,
        o.status,
        o.total_amount,
        s.student_name as student_name,
        s.class as student_class,
        s.section as student_section
    FROM orders o
    LEFT JOIN students s ON o.student_id = s.id
    WHERE o.school_id = $school_id
    ORDER BY o.created_at DESC
    LIMIT 5
";
$recent_orders_result = $conn->query($recent_orders_query);
if ($recent_orders_result && $recent_orders_result->num_rows > 0) {
    while ($order = $recent_orders_result->fetch_assoc()) {
        $stats['recent_orders'][] = $order;
    }
}

// Get low stock items (less than 10 in stock)
$low_stock_query = "
    SELECT 
        id,
        item_name,
        category,
        quantity,
        price
    FROM inventory
    WHERE school_id = $school_id AND quantity < 10
    ORDER BY quantity ASC
    LIMIT 5
";
$low_stock_result = $conn->query($low_stock_query);
if ($low_stock_result && $low_stock_result->num_rows > 0) {
    while ($item = $low_stock_result->fetch_assoc()) {
        $stats['low_stock_items'][] = $item;
    }
}

// Get class distribution
$class_distribution_query = "
    SELECT 
        class,
        COUNT(*) as count
    FROM students
    WHERE school_id = $school_id
    GROUP BY class
    ORDER BY 
        CASE 
            WHEN class = 'Nursery' THEN 0
            WHEN class = 'LKG' THEN 1
            WHEN class = 'UKG' THEN 2
            ELSE CAST(class AS UNSIGNED) + 2
        END
";
$class_distribution_result = $conn->query($class_distribution_query);
if ($class_distribution_result && $class_distribution_result->num_rows > 0) {
    while ($class = $class_distribution_result->fetch_assoc()) {
        $stats['class_distribution'][] = $class;
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="School Dashboard for Samridhi Book Dress - Manage your inventory, students, and orders">
  <title>Dashboard - <?php echo htmlspecialchars($school_name); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/school_dashboard.css">
  <link rel="preload" href="assets/pattern.svg" as="image">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>
  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 sticky-top">
    <div class="container-fluid">
      <a class="navbar-brand d-flex align-items-center" href="school_dashboard.php">
        <img src="<?php echo htmlspecialchars($school_logo); ?>" alt="School Logo" id="schoolLogo" height="40" class="me-2 rounded-circle border border-light" width="40">
        <span id="schoolName"><?php echo htmlspecialchars($school_name); ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link active" href="school_dashboard.php" aria-current="page"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="school_inventory.php"><i class="fas fa-boxes me-1"></i> Inventory</a></li>
          <li class="nav-item"><a class="nav-link" href="school_students.php"><i class="fas fa-user-graduate me-1"></i> Students</a></li>
          <li class="nav-item"><a class="nav-link" href="school_orders.php"><i class="fas fa-shopping-cart me-1"></i> Orders</a></li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fas fa-user-circle me-1"></i> Account
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="school_edit_profile.php"><i class="fas fa-user-edit me-2"></i>Edit Profile</a></li>
              <li><a class="dropdown-item" href="school_settings.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <main class="container-fluid py-4">
    <!-- Welcome Banner -->
    <div class="row mb-4">
      <div class="col">
        <div class="card welcome-banner">
          <div class="card-body p-4">
            <div class="row align-items-center">
              <div class="col-md-8">
                <h1 class="display-6 mb-2">Welcome back, <?php echo htmlspecialchars($school_name); ?>!</h1>
                <p class="text-muted mb-0">Here's what's happening with your inventory and orders today.</p>
              </div>
              <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <a href="school_inventory.php" class="btn btn-primary me-2">
                  <i class="fas fa-plus-circle me-1"></i> Add Inventory
                </a>
                <a href="school_orders.php" class="btn btn-outline-primary">
                  <i class="fas fa-list me-1"></i> View Orders
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card border-left-primary h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Students</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_students']); ?></div>
                <div class="small text-muted">Registered in your school</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-user-graduate fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card border-left-info h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Inventory Items</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_inventory']); ?></div>
                <div class="small text-muted">Books, uniforms, and supplies</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-boxes fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card border-left-warning h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['pending_orders']); ?></div>
                <div class="small text-muted">Waiting to be processed</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stats-card border-left-success h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_orders']); ?></div>
                <div class="small text-muted">All-time order count</div>
              </div>
              <div class="col-auto">
                <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Content Row -->
    <div class="row">
      <!-- Recent Orders -->
      <div class="col-lg-8 mb-4">
        <div class="card shadow mb-4">
          <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Recent Orders</h6>
            <a href="school_orders.php" class="btn btn-sm btn-primary">View All</a>
          </div>
          <div class="card-body">
            <?php if (count($stats['recent_orders']) > 0): ?>
              <div class="table-responsive">
                <table class="table table-hover">
                  <thead>
                    <tr>
                      <th>Order #</th>
                      <th>Date</th>
                      <th>Student</th>
                      <th>Class</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($stats['recent_orders'] as $order): ?>
                      <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo date('d-M-Y', strtotime($order['created_at'])); ?></td>
                        <td><?php echo htmlspecialchars($order['student_name']); ?></td>
                        <td>
                          <?php 
                            echo htmlspecialchars($order['student_class']) . 
                              ($order['student_section'] ? '-' . htmlspecialchars($order['student_section']) : ''); 
                          ?>
                        </td>
                        <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td>
                          <?php
                            $status_class = 'bg-secondary';
                            if ($order['status'] === 'pending') $status_class = 'bg-warning';
                            else if ($order['status'] === 'processing') $status_class = 'bg-info';
                            else if ($order['status'] === 'ready') $status_class = 'bg-primary';
                            else if ($order['status'] === 'delivered') $status_class = 'bg-success';
                            else if ($order['status'] === 'cancelled') $status_class = 'bg-danger';
                          ?>
                          <span class="badge <?php echo $status_class; ?>">
                            <?php echo ucfirst(htmlspecialchars($order['status'])); ?>
                          </span>
                        </td>
                        <td>
                          <a href="order_print.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-primary" title="View Details">
                            <i class="fas fa-eye"></i>
                          </a>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                <p class="mb-0">No recent orders found.</p>
                <p class="text-muted">Orders will appear here once they are placed.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Low Stock Items -->
      <div class="col-lg-4 mb-4">
        <div class="card shadow mb-4">
          <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Low Stock Alert</h6>
            <a href="school_inventory.php" class="btn btn-sm btn-primary">Manage Inventory</a>
          </div>
          <div class="card-body">
            <?php if (count($stats['low_stock_items']) > 0): ?>
              <div class="list-group low-stock-list">
                <?php foreach($stats['low_stock_items'] as $item): ?>
                  <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between">
                      <h6 class="mb-1"><?php echo htmlspecialchars($item['item_name']); ?></h6>
                      <small class="badge bg-danger"><?php echo $item['quantity']; ?> left</small>
                    </div>
                    <p class="mb-1 small"><?php echo ucfirst(htmlspecialchars($item['category'])); ?> - <?php echo htmlspecialchars($item['id']); ?></p>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                      <small class="text-muted">₹<?php echo number_format($item['price'], 2); ?></small>
                      <a href="school_inventory.php?edit=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">Update Stock</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <p class="mb-0">All items are well-stocked!</p>
                <p class="text-muted">No low stock items at the moment.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Class Distribution -->
        <div class="card shadow">
          <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Student Distribution</h6>
          </div>
          <div class="card-body">
            <?php if (count($stats['class_distribution']) > 0): ?>
              <div class="chart-container">
                <canvas id="classDistributionChart"></canvas>
              </div>
            <?php else: ?>
              <div class="text-center py-4">
                <i class="fas fa-user-graduate fa-3x text-muted mb-3"></i>
                <p class="mb-0">No student data available.</p>
                <p class="text-muted">Add students to see the distribution.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <footer class="bg-white py-4 mt-auto border-top">
    <div class="container">
      <div class="d-flex align-items-center justify-content-between small">
        <div class="text-muted">
          Copyright &copy; <?php echo date('Y'); ?> Samridhi Book Dress. All rights reserved.
        </div>
        <div>
          <a href="privacy_policy.php">Privacy Policy</a>
          &middot;
          <a href="terms_conditions.php">Terms &amp; Conditions</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Initialize class distribution chart if data is available
    <?php if (count($stats['class_distribution']) > 0): ?>
    const classLabels = [];
    const classData = [];
    
    <?php foreach($stats['class_distribution'] as $class): ?>
    classLabels.push('<?php echo addslashes($class['class']); ?>');
    classData.push(<?php echo $class['count']; ?>);
    <?php endforeach; ?>
    
    const ctx = document.getElementById('classDistributionChart').getContext('2d');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: classLabels,
        datasets: [{
          data: classData,
          backgroundColor: [
            '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b',
            '#6f42c1', '#5a5c69', '#858796', '#e83e8c', '#fd7e14',
            '#20c9a6', '#2c9faf', '#f8f9fc', '#f8f9fc', '#f8f9fc'
          ],
          hoverOffset: 4
        }]
      },
      options: {
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              boxWidth: 12
            }
          }
        }
      }
    });
    <?php endif; ?>
    
    // Logout functionality
    document.getElementById('logoutBtn').addEventListener('click', function(e) {
      e.preventDefault();
      
      fetch('api/logout.php')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            window.location.href = 'school_login.php';
          } else {
            alert('Logout failed. Please try again.');
          }
        })
        .catch(error => {
          console.error('Logout error:', error);
          alert('Logout failed. Please try again.');
        });
    });
  });
  </script>
</body>
</html> 