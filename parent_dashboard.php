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
$user_id = $_SESSION['user_id'] ?? $_SESSION['parent_id']; // Use user_id if available
$parent_name = $_SESSION['parent_name'] ?? 'Parent';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Parent Dashboard for Samridhi Book Dress - School Uniform Management System">
  <title>Parent Dashboard - Samridhi Book Dress</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/parent_dashboard.css">
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
            <a class="nav-link active" href="parent_dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="parent_orders.php"><i class="fas fa-shopping-bag me-1"></i> My Orders</a>
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
          <li class="nav-item ms-2">
            <button class="btn btn-light position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
              <i class="fas fa-shopping-cart"></i>
              <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-badge" id="cart-count">0</span>
            </button>
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
            <div class="badge bg-primary p-2 fs-6 mb-2">
              <i class="fas fa-school me-1"></i> <span id="school-id">Loading...</span>
            </div>
            <div class="d-block">
              <span class="badge bg-info p-2 fs-6">
                <i class="fas fa-id-card me-1"></i> <span id="parent-id"><?php echo htmlspecialchars($parent_id); ?></span>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Items Section -->
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white py-3">
        <h5 class="mb-0">
          <i class="fas fa-shopping-basket me-2 text-primary"></i>
          Available Items
        </h5>
      </div>
      <div class="card-body">
        <!-- Category Buttons -->
        <div class="d-flex flex-wrap gap-2 mb-4">
          <button type="button" class="btn btn-outline-primary active category-btn" data-category="all">
            All Items
          </button>
          <button type="button" class="btn btn-outline-primary category-btn" data-category="Book">
            <i class="fas fa-book me-1"></i> Books
          </button>
          <button type="button" class="btn btn-outline-primary category-btn" data-category="Uniform">
            <i class="fas fa-tshirt me-1"></i> Uniform
          </button>
          <button type="button" class="btn btn-outline-primary category-btn" data-category="Stationery">
            <i class="fas fa-pencil-alt me-1"></i> Stationery
          </button>
          <button type="button" class="btn btn-outline-primary category-btn" data-category="Accessories">
            <i class="fas fa-socks me-1"></i> Accessories
          </button>
        </div>

        <!-- Search Bar -->
        <div class="row mb-4">
          <div class="col-md-6">
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="fas fa-search text-muted"></i>
              </span>
              <input type="text" class="form-control" id="search-input" placeholder="Search items...">
              <button class="btn btn-outline-secondary" type="button" id="clear-search">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>
          <div class="col-md-6 d-flex justify-content-md-end align-items-center mt-3 mt-md-0">
            <div class="dropdown me-2">
              <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-sort me-1"></i> Sort
              </button>
              <ul class="dropdown-menu">
                <li><button class="dropdown-item sort-option" data-sort="name-asc">Name (A-Z)</button></li>
                <li><button class="dropdown-item sort-option" data-sort="name-desc">Name (Z-A)</button></li>
                <li><button class="dropdown-item sort-option" data-sort="price-asc">Price (Low to High)</button></li>
                <li><button class="dropdown-item sort-option" data-sort="price-desc">Price (High to Low)</button></li>
              </ul>
            </div>
            <div class="form-check form-switch ms-2">
              <input class="form-check-input" type="checkbox" id="in-stock-only">
              <label class="form-check-label" for="in-stock-only">In stock only</label>
            </div>
          </div>
        </div>

        <!-- Items Grid -->
        <div class="row g-3" id="items-container">
          <div class="col-12 text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading items...</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Cart Offcanvas -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">
        <i class="fas fa-shopping-cart me-2 text-primary"></i>
        Your Cart
      </h5>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
      <div id="empty-cart" class="text-center py-5">
        <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
        <h5>Your cart is empty</h5>
        <p class="text-muted">Add items to get started</p>
      </div>
      
      <div id="cart-items-container" class="d-none">
        <div id="cart-items">
          <!-- Cart items will be loaded here -->
        </div>
        
        <div class="card mt-3">
          <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
              <span>Subtotal:</span>
              <span id="subtotal">₹0.00</span>
            </div>
            <div class="d-grid gap-2">
              <button class="btn btn-primary" id="checkout-btn">
                <i class="fas fa-check me-2"></i>Proceed to Checkout
              </button>
              <button class="btn btn-outline-danger" id="clear-cart-btn">
                <i class="fas fa-trash me-2"></i>Clear Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Confirmation Modal -->
  <div class="modal fade" id="orderConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Confirm Your Order</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            Please review your order details before confirming.
          </div>
          
          <div class="mb-3">
            <h6>Order Summary:</h6>
            <div id="order-items-summary">
              <!-- Order items summary will be shown here -->
            </div>
          </div>
          
          <div class="card mb-3">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between mb-2">
                <span>Total Items:</span>
                <span id="total-items">0</span>
              </div>
              <div class="d-flex justify-content-between">
                <span class="fw-bold">Total Amount:</span>
                <span class="fw-bold" id="total-amount">₹0.00</span>
              </div>
            </div>
          </div>
          
          <div class="mb-3">
            <label for="delivery-address" class="form-label">Delivery Address:</label>
            <textarea class="form-control" id="delivery-address" rows="2" placeholder="Enter your delivery address" required></textarea>
          </div>
          
          <div class="mb-3">
            <label for="delivery-note" class="form-label">Delivery Note (Optional):</label>
            <textarea class="form-control" id="delivery-note" rows="2" placeholder="Any special instructions for delivery"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="confirm-order-btn">
            <i class="fas fa-check me-2"></i>Confirm Order
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Order Success Modal -->
  <div class="modal fade" id="orderSuccessModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title">
            <i class="fas fa-check-circle me-2"></i>
            Order Placed Successfully!
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
          <div class="mb-4">
            <i class="fas fa-shopping-bag fa-4x text-success mb-3"></i>
            <h4>Thank You for Your Order</h4>
            <p class="text-muted">Your order has been placed successfully and is being processed.</p>
          </div>
          
          <div class="alert alert-info">
            <div class="d-flex align-items-center">
              <div class="me-3">
                <i class="fas fa-info-circle fa-2x"></i>
              </div>
              <div class="text-start">
                <h6 class="mb-1">Order ID: <span id="success-order-id">ORD12345</span></h6>
                <p class="mb-0">You can track your order status in the "My Orders" section.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Continue Shopping</button>
          <a href="parent_orders.php" class="btn btn-primary">View My Orders</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Loading Overlay -->
  <div id="loading-overlay" class="position-fixed top-0 left-0 w-100 h-100 d-none" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
    <div class="position-absolute top-50 start-50 translate-middle text-white text-center">
      <div class="spinner-border text-light mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
      <p>Processing...</p>
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
  <script src="js/parent_dashboard.js"></script>
</body>
</html> 