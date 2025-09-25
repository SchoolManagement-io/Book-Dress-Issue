<?php
// Start session
session_start();

// Set headers for JSON response
header('Content-Type: application/json');

// Check if user is authenticated as a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access',
        'redirect' => 'school_login.php'
    ]);
    exit;
}

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get school ID from session
$school_id = $_SESSION['user_id'];

// Get action type (add, update, delete)
$action = $_POST['action'] ?? 'add';

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Connect to database
try {
    $mysqli = new mysqli(
        $db_config['host'],
        $db_config['username'],
        $db_config['password'],
        $db_config['database']
    );

    // Check connection
    if ($mysqli->connect_error) {
        throw new Exception("Database connection failed: " . $mysqli->connect_error);
    }

    // Set character set
    $mysqli->set_charset('utf8mb4');
    
    // Handle different actions
    switch ($action) {
        case 'add':
            addInventoryItem($mysqli, $school_id);
            break;
            
        case 'update':
            updateInventoryItem($mysqli, $school_id);
            break;
            
        case 'delete':
            deleteInventoryItem($mysqli, $school_id);
            break;
            
        default:
            throw new Exception("Invalid action specified");
    }
    
} catch (Exception $e) {
    // Log error
    error_log("Inventory action error: " . $e->getMessage());
    
    // Return error response
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
    
    // Close mysqli if it exists
    if (isset($mysqli)) {
        $mysqli->close();
    }
}

/**
 * Add a new inventory item
 */
function addInventoryItem($mysqli, $school_id) {
    // Get form data
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['item_type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $class = trim($_POST['class'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    
    // Validate required fields
    if (empty($item_name) || empty($category) || $price <= 0 || $quantity < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields with valid values'
        ]);
        exit;
    }
    
    // Generate SKU if not provided
    if (empty($sku)) {
        $category_code = substr(strtoupper($category), 0, 2);
        $random_num = mt_rand(1000, 9999);
        $sku = $school_id . '-' . $category_code . '-' . $random_num;
    }
    
    // Insert the inventory item
    $stmt = $mysqli->prepare("
        INSERT INTO inventory (
            school_id, 
            item_name, 
            category, 
            price, 
            quantity, 
            class, 
            type,
            sku,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    $stmt->bind_param(
        "issdisss", 
        $school_id, 
        $item_name, 
        $category,
        $price, 
        $quantity,
        $class,
        $type,
        $sku
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to add inventory item: " . $stmt->error);
    }
    
    $item_id = $mysqli->insert_id;
    $stmt->close();
    
    // Set success message in session for page redirect
    $_SESSION['inventory_action_success'] = 'Inventory item added successfully';
    
    // Decide whether to return JSON or redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return success response for AJAX request
        echo json_encode([
            'success' => true,
            'item_id' => $item_id,
            'message' => 'Inventory item added successfully'
        ]);
    } else {
        // Redirect for form submission
        header('Location: ../school_inventory.php');
        exit;
    }
}

/**
 * Update an existing inventory item
 */
function updateInventoryItem($mysqli, $school_id) {
    // Get form data
    $item_id = intval($_POST['item_id'] ?? 0);
    $item_name = trim($_POST['item_name'] ?? '');
    $category = trim($_POST['item_type'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    $class = trim($_POST['class'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    
    // Validate required fields
    if (empty($item_id) || empty($item_name) || empty($category) || $price <= 0 || $quantity < 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Please fill all required fields with valid values'
        ]);
        exit;
    }
    
    // Verify the item belongs to this school
    $check_stmt = $mysqli->prepare("SELECT id FROM inventory WHERE id = ? AND school_id = ?");
    $check_stmt->bind_param("ii", $item_id, $school_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception("You do not have permission to update this item");
    }
    
    $check_stmt->close();
    
    // Update the inventory item
    $stmt = $mysqli->prepare("
        UPDATE inventory SET 
            item_name = ?, 
            category = ?, 
            price = ?, 
            quantity = ?, 
            class = ?, 
            type = ?,
            sku = ?,
            updated_at = NOW()
        WHERE id = ? AND school_id = ?
    ");
    
    $stmt->bind_param(
        "ssdisssii", 
        $item_name, 
        $category,
        $price, 
        $quantity,
        $class,
        $type,
        $sku,
        $item_id,
        $school_id
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to update inventory item: " . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception("No changes were made to the inventory item");
    }
    
    // Set success message in session for page redirect
    $_SESSION['inventory_action_success'] = 'Inventory item updated successfully';
    
    // Decide whether to return JSON or redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return success response for AJAX request
        echo json_encode([
            'success' => true,
            'message' => 'Inventory item updated successfully'
        ]);
    } else {
        // Redirect for form submission
        header('Location: ../school_inventory.php');
        exit;
    }
}

/**
 * Delete an inventory item
 */
function deleteInventoryItem($mysqli, $school_id) {
    // Get item ID
    $item_id = intval($_POST['item_id'] ?? 0);
    
    if (empty($item_id)) {
        throw new Exception("Invalid item ID");
    }
    
    // Verify the item belongs to this school
    $check_stmt = $mysqli->prepare("SELECT id FROM inventory WHERE id = ? AND school_id = ?");
    $check_stmt->bind_param("ii", $item_id, $school_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 0) {
        throw new Exception("You do not have permission to delete this item");
    }
    
    $check_stmt->close();
    
    // Check if item is used in any orders
    $orders_stmt = $mysqli->prepare("
        SELECT COUNT(*) as count FROM order_items 
        WHERE inventory_id = ?
    ");
    $orders_stmt->bind_param("i", $item_id);
    $orders_stmt->execute();
    $orders_result = $orders_stmt->get_result();
    $orders_row = $orders_result->fetch_assoc();
    
    if ($orders_row['count'] > 0) {
        throw new Exception("Cannot delete this item as it is associated with one or more orders");
    }
    
    $orders_stmt->close();
    
    // Delete the inventory item
    $stmt = $mysqli->prepare("DELETE FROM inventory WHERE id = ? AND school_id = ?");
    $stmt->bind_param("ii", $item_id, $school_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to delete inventory item: " . $stmt->error);
    }
    
    $affected_rows = $stmt->affected_rows;
    $stmt->close();
    
    if ($affected_rows === 0) {
        throw new Exception("Failed to delete inventory item");
    }
    
    // Set success message in session for page redirect
    $_SESSION['inventory_action_success'] = 'Inventory item deleted successfully';
    
    // Decide whether to return JSON or redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Return success response for AJAX request
        echo json_encode([
            'success' => true,
            'message' => 'Inventory item deleted successfully'
        ]);
    } else {
        // Redirect for form submission
        header('Location: ../school_inventory.php');
        exit;
    }
}
?> 