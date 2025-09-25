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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage orders for Samridhi Book Dress - Track, update, and fulfill student orders">
  <title>Orders Management - <?php echo htmlspecialchars($school_name); ?></title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/school_orders.css">
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
          <li class="nav-item"><a class="nav-link" href="school_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="school_inventory.php"><i class="fas fa-boxes me-1"></i> Inventory</a></li>
          <li class="nav-item"><a class="nav-link" href="school_students.php"><i class="fas fa-user-graduate me-1"></i> Students</a></li>
          <li class="nav-item"><a class="nav-link active" href="school_orders.php" aria-current="page"><i class="fas fa-shopping-cart me-1"></i> Orders</a></li>
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
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-0 text-gray-800">Orders Management</h1>
        <p class="text-muted mb-0">View and manage student orders</p>
      </div>
      <div>
        <button id="refreshOrdersBtn" class="btn btn-sm btn-outline-primary me-2">
          <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
        <button id="exportOrdersBtn" class="btn btn-sm btn-outline-success">
          <i class="fas fa-file-export me-1"></i> Export
        </button>
      </div>
    </div>

    <!-- Orders Filter -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">Order Filters</h6>
      </div>
      <div class="card-body">
        <form id="orderFiltersForm" class="row g-3">
          <div class="col-md-3">
            <label for="statusFilter" class="form-label">Order Status</label>
            <select class="form-select" id="statusFilter">
              <option value="all" selected>All Statuses</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="ready">Ready for Pickup</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="dateRangeFilter" class="form-label">Date Range</label>
            <select class="form-select" id="dateRangeFilter">
              <option value="all" selected>All Time</option>
              <option value="today">Today</option>
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="custom">Custom Range</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="classFilter" class="form-label">Student Class</label>
            <select class="form-select" id="classFilter">
              <option value="all" selected>All Classes</option>
              <option value="Nursery">Nursery</option>
              <option value="LKG">LKG</option>
              <option value="UKG">UKG</option>
              <?php for($i=1; $i<=12; $i++): ?>
              <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="searchOrders" class="form-label">Search</label>
            <input type="text" class="form-control" id="searchOrders" placeholder="Order #, Student Name...">
          </div>
          <div id="customDateContainer" class="row g-3 mt-1 d-none">
            <div class="col-md-3">
              <label for="startDate" class="form-label">Start Date</label>
              <input type="date" class="form-control" id="startDate">
            </div>
            <div class="col-md-3">
              <label for="endDate" class="form-label">End Date</label>
              <input type="date" class="form-control" id="endDate">
            </div>
          </div>
          <div class="col-12 mt-3">
            <button type="button" id="applyFiltersBtn" class="btn btn-primary">
              <i class="fas fa-filter me-1"></i> Apply Filters
            </button>
            <button type="button" id="resetFiltersBtn" class="btn btn-outline-secondary ms-2">
              <i class="fas fa-undo me-1"></i> Reset
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Orders Statistics -->
    <div class="row mb-4">
      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <div class="loading-spinner small"></div>
                  <span id="totalOrdersCount" class="d-none">0</span>
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <div class="loading-spinner small"></div>
                  <span id="pendingOrdersCount" class="d-none">0</span>
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-clock fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Processing Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <div class="loading-spinner small"></div>
                  <span id="processingOrdersCount" class="d-none">0</span>
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-spinner fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success h-100 py-2">
          <div class="card-body">
            <div class="row no-gutters align-items-center">
              <div class="col mr-2">
                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Delivered Orders</div>
                <div class="h5 mb-0 font-weight-bold text-gray-800">
                  <div class="loading-spinner small"></div>
                  <span id="deliveredOrdersCount" class="d-none">0</span>
                </div>
              </div>
              <div class="col-auto">
                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Orders Table -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">All Orders</h6>
        <div class="btn-group">
          <button type="button" class="btn btn-sm btn-primary" id="bulkProcessBtn" disabled>
            <i class="fas fa-cogs me-1"></i> Process Selected
          </button>
          <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="visually-hidden">Toggle Dropdown</span>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" id="bulkReadyBtn" disabled><i class="fas fa-box me-2"></i>Mark as Ready</button></li>
            <li><button class="dropdown-item" type="button" id="bulkDeliverBtn" disabled><i class="fas fa-truck me-2"></i>Mark as Delivered</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item text-danger" type="button" id="bulkCancelBtn" disabled><i class="fas fa-times me-2"></i>Cancel Selected</button></li>
          </ul>
        </div>
      </div>
      <div class="card-body">
        <div id="ordersLoadingSpinner" class="text-center py-5">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
          <p class="mt-2">Loading orders...</p>
        </div>
        
        <div id="noOrdersMessage" class="alert alert-info d-none">
          No orders found matching your criteria.
        </div>
        
        <div class="table-responsive d-none" id="ordersTableContainer">
          <table class="table table-bordered table-hover" id="ordersTable" width="100%" cellspacing="0">
            <thead>
              <tr>
                <th width="30px">
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAllOrders">
                  </div>
                </th>
                <th>Order #</th>
                <th>Date</th>
                <th>Student Name</th>
                <th>Class</th>
                <th>Items</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody id="ordersTableBody">
              <!-- Orders will be loaded here dynamically -->
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Orders pagination" class="d-none" id="ordersPagination">
          <ul class="pagination justify-content-center mt-4" id="paginationContainer">
            <!-- Pagination will be generated here -->
          </ul>
        </nav>
      </div>
    </div>
  </main>

  <!-- Order Details Modal -->
  <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="orderDetailsModalLabel">
            <i class="fas fa-shopping-cart me-2 text-primary"></i>
            Order #<span id="modal-order-number"></span>
          </h5>
          <span id="modal-order-status" class="badge bg-primary ms-2">Status</span>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-4">
            <div class="col-md-6">
              <h6 class="mb-2">Order Information</h6>
              <p class="mb-1"><strong>Order Date:</strong> <span id="modal-order-date"></span></p>
              <p class="mb-1"><strong>Total Amount:</strong> <span id="modal-order-amount"></span></p>
              <p class="mb-0"><strong>Payment Status:</strong> <span id="modal-payment-status"></span></p>
            </div>
            <div class="col-md-6">
              <h6 class="mb-2">Student Information</h6>
              <p class="mb-1"><strong>Name:</strong> <span id="modal-student-name"></span></p>
              <p class="mb-1"><strong>Class:</strong> <span id="modal-student-class"></span></p>
              <p class="mb-0"><strong>Parent:</strong> <span id="modal-parent-name"></span></p>
            </div>
          </div>
          
          <h6 class="border-top pt-3 mb-3">Order Items</h6>
          <div class="table-responsive">
            <table class="table table-sm">
              <thead>
                <tr>
                  <th>Item</th>
                  <th>Type</th>
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
                  <td colspan="4" class="text-end fw-bold">Total:</td>
                  <td class="text-end fw-bold" id="modal-items-total">₹0.00</td>
                </tr>
              </tfoot>
            </table>
          </div>
          
          <div id="delivery-note-container" class="mt-3 d-none">
            <h6 class="mb-2">Delivery Note</h6>
            <div class="alert alert-info mb-0" id="modal-delivery-note"></div>
          </div>
          
          <div id="delivery-address-container" class="mt-3 d-none">
            <h6 class="mb-2">Delivery Address</h6>
            <div class="alert alert-primary mb-0" id="modal-delivery-address"></div>
          </div>
          
          <div class="border-top mt-4 pt-3">
            <h6 class="mb-3">Update Order Status</h6>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-warning update-status-btn" data-status="pending">
                <i class="fas fa-clock me-1"></i> Pending
              </button>
              <button type="button" class="btn btn-info update-status-btn" data-status="processing">
                <i class="fas fa-spinner me-1"></i> Processing
              </button>
              <button type="button" class="btn btn-primary update-status-btn" data-status="ready">
                <i class="fas fa-box me-1"></i> Ready for Pickup
              </button>
              <button type="button" class="btn btn-success update-status-btn" data-status="delivered">
                <i class="fas fa-check-circle me-1"></i> Delivered
              </button>
              <button type="button" class="btn btn-danger update-status-btn" data-status="cancelled">
                <i class="fas fa-times me-1"></i> Cancelled
              </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="#" id="printOrderBtn" class="btn btn-primary" target="_blank">
            <i class="fas fa-print me-1"></i> Print Order
          </a>
        </div>
      </div>
    </div>
  </div>

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

  <!-- Loading Overlay -->
  <div id="loading-overlay" class="d-none">
    <div class="spinner-overlay" aria-hidden="true"></div>
    <p class="text-white mt-3">Processing<span class="loading-dots"><span></span><span></span><span></span></span></p>
    <span class="sr-only">Processing, please wait</span>
  </div>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/school_orders.js"></script>
</body>
</html> 