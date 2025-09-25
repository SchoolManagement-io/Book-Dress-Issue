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

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Initialize variables
$all_inventory = [];
$categories = [];

try {
    // Connect to database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset('utf8mb4');
    
    // Get all inventory items for this school
    $inventory_query = "
        SELECT id, item_name, category, quantity, price, class, sku, type
        FROM inventory 
        WHERE school_id = ?
        ORDER BY category, item_name
    ";
    
    $inventory_stmt = $conn->prepare($inventory_query);
    $inventory_stmt->bind_param("i", $school_id);
    $inventory_stmt->execute();
    $inventory_result = $inventory_stmt->get_result();
    
    while ($row = $inventory_result->fetch_assoc()) {
        $all_inventory[] = $row;
        
        // Collect unique categories
        if (!in_array($row['category'], $categories)) {
            $categories[] = $row['category'];
        }
    }
    
    $inventory_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Error loading inventory: " . $e->getMessage());
    
    // Close connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    $error_message = "An error occurred while loading inventory data. Please try again later.";
}

// Handle success/error messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['inventory_action_success'])) {
    $success_message = $_SESSION['inventory_action_success'];
    unset($_SESSION['inventory_action_success']);
}

if (isset($_SESSION['inventory_action_error'])) {
    $error_message = $_SESSION['inventory_action_error'];
    unset($_SESSION['inventory_action_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage your school's inventory of books, uniforms, and supplies">
  <title>Inventory Management - <?php echo htmlspecialchars($school_name); ?></title>
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
          <li class="nav-item"><a class="nav-link" href="school_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link active" href="school_inventory.php" aria-current="page"><i class="fas fa-boxes me-1"></i> Inventory</a></li>
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
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h1 class="h3 mb-0 text-gray-800">Inventory Management</h1>
        <p class="text-muted mb-0">Manage your books, uniforms, and school supplies</p>
      </div>
      <div>
        <button class="btn btn-sm btn-outline-success me-2" data-bs-toggle="modal" data-bs-target="#exportInventoryModal">
          <i class="fas fa-file-export me-1"></i> Export
        </button>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addInventoryModal">
          <i class="fas fa-plus-circle me-1"></i> Add Item
        </button>
      </div>
    </div>

    <!-- Success/Error alerts -->
    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
      <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
      <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Inventory Filter and Search -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white">
        <h6 class="m-0 font-weight-bold text-primary">Filter Inventory</h6>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label for="categoryFilter" class="form-label">Category</label>
            <select class="form-select" id="categoryFilter">
              <option value="all" selected>All Categories</option>
              <?php foreach ($categories as $category): ?>
              <option value="<?php echo htmlspecialchars($category); ?>"><?php echo htmlspecialchars($category); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label for="classFilter" class="form-label">Class</label>
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
            <label for="stockFilter" class="form-label">Stock Status</label>
            <select class="form-select" id="stockFilter">
              <option value="all" selected>All Items</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
          <div class="col-md-3">
            <label for="searchInventory" class="form-label">Search</label>
            <input type="text" class="form-control" id="searchInventory" placeholder="Item name, SKU...">
          </div>
        </div>
      </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Inventory Items</h6>
        <span class="badge bg-primary"><?php echo count($all_inventory); ?> Items</span>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-bordered table-hover" id="inventoryTable">
            <thead>
              <tr>
                <th>Item Name</th>
                <th>Category</th>
                <th>Type</th>
                <th>SKU</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Class</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($all_inventory) > 0): ?>
                <?php foreach ($all_inventory as $item): ?>
                <tr>
                  <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                  <td><?php echo htmlspecialchars($item['category']); ?></td>
                  <td><?php echo htmlspecialchars($item['type'] ?? 'N/A'); ?></td>
                  <td><?php echo htmlspecialchars($item['sku'] ?? 'N/A'); ?></td>
                  <td>₹<?php echo number_format($item['price'], 2); ?></td>
                  <td>
                    <?php 
                      $quantity = $item['quantity'];
                      $class = 'text-success';
                      if ($quantity <= 5) {
                        $class = 'text-danger';
                      } elseif ($quantity <= 10) {
                        $class = 'text-warning';
                      }
                      echo '<span class="fw-bold ' . $class . '">' . $quantity . '</span>';
                    ?>
                  </td>
                  <td><?php echo htmlspecialchars($item['class'] ?? 'All'); ?></td>
                  <td>
                    <button class="btn btn-sm btn-primary edit-item" data-id="<?php echo $item['id']; ?>">
                      <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger delete-item" data-id="<?php echo $item['id']; ?>">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="8" class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="mb-0">No inventory items found.</p>
                    <p class="text-muted">Click "Add Item" to add your first inventory item.</p>
                  </td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <!-- Add Inventory Modal -->
  <div class="modal fade" id="addInventoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Inventory Item</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="addInventoryForm" action="api/add_inventory.php" method="post">
          <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="item_name" class="form-label">Item Name</label>
                <input type="text" class="form-control" id="item_name" name="item_name" required>
              </div>
              <div class="col-md-6">
                <label for="item_type" class="form-label">Category</label>
                <select class="form-select" id="item_type" name="item_type" required>
                  <option value="">Select Category</option>
                  <option value="Book">Book</option>
                  <option value="Notebook">Notebook</option>
                  <option value="Uniform">Uniform</option>
                  <option value="Stationery">Stationery</option>
                  <option value="Accessory">Accessory</option>
                  <option value="Other">Other</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="price" class="form-label">Price (₹)</label>
                <input type="number" class="form-control" id="price" name="price" min="0" step="0.01" required>
              </div>
              <div class="col-md-6">
                <label for="quantity" class="form-label">Quantity</label>
                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
              </div>
              <div class="col-md-6">
                <label for="class" class="form-label">Class (Optional)</label>
                <select class="form-select" id="class" name="class">
                  <option value="">Any Class</option>
                  <option value="Nursery">Nursery</option>
                  <option value="LKG">LKG</option>
                  <option value="UKG">UKG</option>
                  <?php for($i=1; $i<=12; $i++): ?>
                  <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label for="type" class="form-label">Type (Optional)</label>
                <input type="text" class="form-control" id="type" name="type" placeholder="e.g. Hardcover, Winter, etc.">
              </div>
              <div class="col-12">
                <label for="description" class="form-label">Description (Optional)</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Add Item</button>
          </div>
        </form>
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

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Filter functionality
    const searchInput = document.getElementById('searchInventory');
    const categoryFilter = document.getElementById('categoryFilter');
    const classFilter = document.getElementById('classFilter');
    const stockFilter = document.getElementById('stockFilter');
    const tableRows = document.querySelectorAll('#inventoryTable tbody tr');
    
    function filterTable() {
      const searchTerm = searchInput.value.toLowerCase();
      const category = categoryFilter.value;
      const classValue = classFilter.value;
      const stockStatus = stockFilter.value;
      
      tableRows.forEach(row => {
        const itemName = row.cells[0].textContent.toLowerCase();
        const itemCategory = row.cells[1].textContent;
        const itemClass = row.cells[6].textContent;
        const quantity = parseInt(row.cells[5].textContent);
        
        let showRow = true;
        
        // Search filter
        if (searchTerm && !itemName.includes(searchTerm) && !row.cells[3].textContent.toLowerCase().includes(searchTerm)) {
          showRow = false;
        }
        
        // Category filter
        if (category !== 'all' && itemCategory !== category) {
          showRow = false;
        }
        
        // Class filter
        if (classValue !== 'all' && itemClass !== classValue && itemClass !== 'All') {
          showRow = false;
        }
        
        // Stock status filter
        if (stockStatus === 'in_stock' && quantity <= 0) {
          showRow = false;
        } else if (stockStatus === 'low_stock' && (quantity > 10 || quantity <= 0)) {
          showRow = false;
        } else if (stockStatus === 'out_of_stock' && quantity > 0) {
          showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
      });
    }
    
    // Add event listeners to filters
    searchInput.addEventListener('input', filterTable);
    categoryFilter.addEventListener('change', filterTable);
    classFilter.addEventListener('change', filterTable);
    stockFilter.addEventListener('change', filterTable);
    
    // Edit item functionality
    const editButtons = document.querySelectorAll('.edit-item');
    editButtons.forEach(button => {
      button.addEventListener('click', function() {
        const itemId = this.getAttribute('data-id');
        
        // Create edit modal
        const editModal = document.createElement('div');
        editModal.className = 'modal fade';
        editModal.id = 'editInventoryModal';
        editModal.tabIndex = '-1';
        editModal.setAttribute('aria-hidden', 'true');
        
        // Get the item details from the table row
        const row = this.closest('tr');
        const itemName = row.cells[0].textContent.trim();
        const category = row.cells[1].textContent.trim();
        const type = row.cells[2].textContent.trim();
        const sku = row.cells[3].textContent.trim();
        const price = row.cells[4].textContent.replace('₹', '').trim();
        const quantity = row.cells[5].textContent.trim();
        const itemClass = row.cells[6].textContent.trim();
        
        // Create modal content
        editModal.innerHTML = `
          <div class="modal-dialog modal-lg">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">Edit Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
              </div>
              <form id="editInventoryForm" action="api/add_inventory.php" method="post">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="item_id" value="${itemId}">
                <div class="modal-body">
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label for="edit_item_name" class="form-label">Item Name</label>
                      <input type="text" class="form-control" id="edit_item_name" name="item_name" value="${itemName}" required>
                    </div>
                    <div class="col-md-6">
                      <label for="edit_item_type" class="form-label">Category</label>
                      <select class="form-select" id="edit_item_type" name="item_type" required>
                        <option value="Book" ${category === 'Book' ? 'selected' : ''}>Book</option>
                        <option value="Notebook" ${category === 'Notebook' ? 'selected' : ''}>Notebook</option>
                        <option value="Uniform" ${category === 'Uniform' ? 'selected' : ''}>Uniform</option>
                        <option value="Stationery" ${category === 'Stationery' ? 'selected' : ''}>Stationery</option>
                        <option value="Accessory" ${category === 'Accessory' ? 'selected' : ''}>Accessory</option>
                        <option value="Other" ${category === 'Other' ? 'selected' : ''}>Other</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="edit_price" class="form-label">Price (₹)</label>
                      <input type="number" class="form-control" id="edit_price" name="price" min="0" step="0.01" value="${price}" required>
                    </div>
                    <div class="col-md-6">
                      <label for="edit_quantity" class="form-label">Quantity</label>
                      <input type="number" class="form-control" id="edit_quantity" name="quantity" min="0" value="${quantity}" required>
                    </div>
                    <div class="col-md-6">
                      <label for="edit_class" class="form-label">Class (Optional)</label>
                      <select class="form-select" id="edit_class" name="class">
                        <option value="" ${itemClass === 'All' ? 'selected' : ''}>Any Class</option>
                        <option value="Nursery" ${itemClass === 'Nursery' ? 'selected' : ''}>Nursery</option>
                        <option value="LKG" ${itemClass === 'LKG' ? 'selected' : ''}>LKG</option>
                        <option value="UKG" ${itemClass === 'UKG' ? 'selected' : ''}>UKG</option>
                        ${Array.from({length: 12}, (_, i) => `<option value="${i+1}" ${itemClass === (i+1).toString() ? 'selected' : ''}>${i+1}</option>`).join('')}
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="edit_type" class="form-label">Type (Optional)</label>
                      <input type="text" class="form-control" id="edit_type" name="type" value="${type === 'N/A' ? '' : type}" placeholder="e.g. Hardcover, Winter, etc.">
                    </div>
                    <div class="col-md-6">
                      <label for="edit_sku" class="form-label">SKU</label>
                      <input type="text" class="form-control" id="edit_sku" name="sku" value="${sku === 'N/A' ? '' : sku}">
                    </div>
                    <div class="col-12">
                      <label for="edit_description" class="form-label">Description (Optional)</label>
                      <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
              </form>
            </div>
          </div>
        `;
        
        // Add modal to document body
        document.body.appendChild(editModal);
        
        // Show the modal
        const modal = new bootstrap.Modal(document.getElementById('editInventoryModal'));
        modal.show();
        
        // Remove modal from DOM after it's hidden
        editModal.addEventListener('hidden.bs.modal', function() {
          document.body.removeChild(editModal);
        });
      });
    });
    
    // Delete item functionality
    const deleteButtons = document.querySelectorAll('.delete-item');
    deleteButtons.forEach(button => {
      button.addEventListener('click', function() {
        const itemId = this.getAttribute('data-id');
        const itemName = this.closest('tr').cells[0].textContent.trim();
        
        if (confirm(`Are you sure you want to delete "${itemName}"? This action cannot be undone.`)) {
          // Show loading indicator
          const row = this.closest('tr');
          const originalContent = row.innerHTML;
          row.innerHTML = `<td colspan="8" class="text-center"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Deleting...</td>`;
          
          // Send AJAX request to delete the item
          fetch('api/add_inventory.php', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            },
            body: `action=delete&item_id=${itemId}`
          })
          .then(response => response.json())
          .then(data => {
            if (data.success) {
              // Show success message
              const alertHtml = `
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                  <i class="fas fa-check-circle me-2"></i> ${data.message}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              `;
              
              // Insert alert at the top of the page
              const alertContainer = document.createElement('div');
              alertContainer.innerHTML = alertHtml;
              document.querySelector('.card.shadow.mb-4').before(alertContainer.firstChild);
              
              // Remove the row with animation
              row.style.transition = 'opacity 0.5s';
              row.style.opacity = '0';
              setTimeout(() => {
                row.remove();
                
                // Update item count
                const itemCountBadge = document.querySelector('.badge.bg-primary');
                if (itemCountBadge) {
                  const currentCount = parseInt(itemCountBadge.textContent);
                  itemCountBadge.textContent = `${currentCount - 1} Items`;
                }
              }, 500);
            } else {
              // Show error message
              const alertHtml = `
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                  <i class="fas fa-exclamation-circle me-2"></i> ${data.message}
                  <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
              `;
              
              // Insert alert at the top of the page
              const alertContainer = document.createElement('div');
              alertContainer.innerHTML = alertHtml;
              document.querySelector('.card.shadow.mb-4').before(alertContainer.firstChild);
              
              // Restore original row
              row.innerHTML = originalContent;
              
              // Auto dismiss after 5 seconds
              setTimeout(() => {
                const alert = document.querySelector('.alert-danger');
                if (alert) {
                  const bsAlert = new bootstrap.Alert(alert);
                  bsAlert.close();
                }
              }, 5000);
            }
          })
          .catch(error => {
            console.error('Error deleting item:', error);
            
            // Show error message
            const alertHtml = `
              <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i> A network error occurred. Please try again.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            `;
            
            // Insert alert at the top of the page
            const alertContainer = document.createElement('div');
            alertContainer.innerHTML = alertHtml;
            document.querySelector('.card.shadow.mb-4').before(alertContainer.firstChild);
            
            // Restore original row
            row.innerHTML = originalContent;
          });
        }
      });
    });
    
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