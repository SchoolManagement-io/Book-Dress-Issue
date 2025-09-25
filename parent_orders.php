<?php
// Start session
session_start();

// Check if user is logged in and is a parent
if (!isset($_SESSION['parent_id']) || $_SESSION['user_type'] !== 'parent') {
    header("Location: parent_login.php");
    exit();
}

// Get session data
$parent_id = $_SESSION['parent_id'];
$parent_name = $_SESSION['parent_name'] ?? 'Parent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="View and manage your orders from Samridhi Book Dress">
  <title>My Orders - Samridhi Book Dress</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/parent_orders.css">
  <link rel="preload" href="assets/paisley.svg" as="image" type="image/svg+xml">
  <link rel="icon" href="assets/logo.svg" type="image/svg+xml">
</head>
<body class="bg-light">

  <!-- Navbar -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php">
        <img src="assets/logo.svg" alt="Logo" height="40" class="me-2">
        <strong>Samridhi Book Dress</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link" href="parent_dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link active" href="parent_orders.php"><i class="fas fa-shopping-bag me-1"></i> My Orders</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <span id="user-name"><?php echo htmlspecialchars($parent_name); ?></span>
              <i class="fas fa-user-circle ms-1"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="p_profile.php"><i class="fas fa-user-cog me-2"></i> Profile</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><button class="dropdown-item text-danger" id="logout-btn"><i class="fas fa-sign-out-alt me-2"></i> Logout</button></li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Main Content -->
  <div class="container py-4">
    <!-- Student Info -->
    <div class="card shadow-sm mb-4">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-md-2 text-center text-md-start mb-3 mb-md-0">
            <img src="assets/student.svg" alt="Student" class="img-fluid" style="max-height: 80px;">
          </div>
          <div class="col-md-7 mb-3 mb-md-0">
            <h5 class="card-title" id="student-name">Loading student information...</h5>
            <p class="card-text mb-1" id="student-class">Class: Loading...</p>
            <p class="card-text mb-0" id="student-school">School: Loading...</p>
          </div>
          <div class="col-md-3 text-center text-md-end">
            <span class="badge bg-info p-2 fs-6">
              <i class="fas fa-id-card me-1"></i> <span id="parent-id"><?php echo htmlspecialchars($parent_id); ?></span>
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Section -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <div class="d-flex justify-content-between align-items-center">
          <h5 class="mb-0">
            <i class="fas fa-shopping-bag me-2 text-primary"></i>
            My Orders
            <span class="badge bg-primary ms-2" id="order-count">0</span>
          </h5>
          <a href="parent_dashboard.php" class="btn btn-sm btn-primary">
            <i class="fas fa-plus me-1"></i> New Order
          </a>
        </div>
      </div>
      <div class="card-body">
        <!-- Filters -->
        <div class="row mb-4">
          <div class="col-md-7 mb-3 mb-md-0">
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-outline-primary btn-sm status-filter active" data-status="all">
                All Orders
              </button>
              <button type="button" class="btn btn-outline-warning btn-sm status-filter" data-status="Pending">
                Pending
              </button>
              <button type="button" class="btn btn-outline-info btn-sm status-filter" data-status="Processing">
                Processing
              </button>
              <button type="button" class="btn btn-outline-primary btn-sm status-filter" data-status="Ready">
                Ready
              </button>
              <button type="button" class="btn btn-outline-success btn-sm status-filter" data-status="Delivered">
                Delivered
              </button>
              <button type="button" class="btn btn-outline-danger btn-sm status-filter" data-status="Cancelled">
                Cancelled
              </button>
            </div>
          </div>
          <div class="col-md-5">
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" class="form-control" id="search-input" placeholder="Search orders...">
              <button class="btn btn-outline-secondary" type="button" id="clear-search">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Date Filter -->
        <div class="mb-4">
          <div class="d-flex flex-wrap gap-2">
            <span class="text-muted me-2">Time Period:</span>
            <button type="button" class="btn btn-outline-secondary btn-sm date-filter active" data-period="all-time">
              All Time
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm date-filter" data-period="last-30-days">
              Last 30 Days
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm date-filter" data-period="last-3-months">
              Last 3 Months
            </button>
            <button type="button" class="btn btn-outline-secondary btn-sm date-filter" data-period="last-6-months">
              Last 6 Months
            </button>
          </div>
        </div>

        <!-- Orders Grid -->
        <div class="row" id="orders-container">
          <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading orders...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Details Modal -->
  <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fas fa-shopping-bag me-2 text-primary"></i>
            Order #<span id="modal-order-id"></span>
          </h5>
          <span id="modal-order-status" class="badge bg-primary ms-2 py-2 px-3">Processing</span>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted mb-4">
            <i class="fas fa-calendar me-1"></i> Ordered on: <span id="modal-order-date"></span>
          </p>
          
          <h6 class="border-bottom pb-2 mb-3">Order Items</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Category</th>
                  <th class="text-center">Quantity</th>
                  <th class="text-end">Unit Price</th>
                  <th class="text-end">Total</th>
                </tr>
              </thead>
              <tbody id="modal-order-items">
                <!-- Order items will be loaded here -->
              </tbody>
              <tfoot class="table-group-divider">
                <tr>
                  <td colspan="2" class="text-end fw-bold">Order Summary:</td>
                  <td class="text-center"><span id="modal-items-count">0</span> items</td>
                  <td colspan="2" class="text-end fw-bold">Total: <span id="modal-total-amount">₹0.00</span></td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <div id="modal-delivery-note-container" class="alert alert-info mt-3 d-none">
            <h6 class="mb-1"><i class="fas fa-sticky-note me-2"></i> Delivery Note:</h6>
            <p id="modal-delivery-note" class="mb-0"></p>
          </div>
          
          <div id="modal-delivery-address-container" class="alert alert-primary mt-3 d-none">
            <h6 class="mb-1"><i class="fas fa-map-marker-alt me-2"></i> Delivery Address:</h6>
            <p id="modal-delivery-address" class="mb-0"></p>
          </div>
          
          <div class="alert alert-secondary mt-4">
            <small>
              <i class="fas fa-info-circle me-2"></i>
              For any questions regarding your order, please contact your school administrator.
            </small>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Footer -->
  <footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
      <div class="row">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
          <img src="assets/logo.svg" alt="Logo" height="40" class="me-2 mb-3">
          <p class="mb-1">© <?php echo date('Y'); ?> Samridhi Book Dress. All rights reserved.</p>
          <p class="mb-0 text-muted">A complete solution for school inventory management</p>
        </div>
        <div class="col-md-6 text-center text-md-end">
          <p class="mb-1">For support: support@samridhibookdress.com</p>
          <p class="mb-0">Contact: +91-9876543210</p>
        </div>
      </div>
    </div>
  </footer>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/parent_orders.js"></script>
</body>
</html> 