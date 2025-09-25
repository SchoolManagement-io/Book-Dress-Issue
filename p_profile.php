<?php
// Start session
session_start();

// Check if user is logged in and is a parent
if (!isset($_SESSION['parent_id']) || $_SESSION['user_type'] !== 'parent') {
    header("Location: parent_login.php");
    exit();
}

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Get parent data
$parent_id = $_SESSION['parent_id'];
$user_id = $_SESSION['user_id'] ?? null;
$parent_name = '';
$email = '';
$mobile = '';
$student_name = '';
$student_class = '';
$school_name = '';
$notification_settings = [];

try {
    // Connect to the database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Set the character set to UTF-8
    $conn->set_charset('utf8mb4');

    // First get parent information
    $parent_stmt = $conn->prepare("
        SELECT 
            id,
            parent_name, 
            email, 
            mobile,
            notification_settings
        FROM 
            parents
        WHERE 
            parent_id = ?
    ");
    
    if (!$parent_stmt) {
        throw new Exception("Error preparing parent query: " . $conn->error);
    }
    
    $parent_stmt->bind_param("s", $parent_id);
    
    if (!$parent_stmt->execute()) {
        throw new Exception("Error executing parent query: " . $parent_stmt->error);
    }
    
    $parent_result = $parent_stmt->get_result();
    
    if ($parent_result->num_rows === 1) {
        $parent_data = $parent_result->fetch_assoc();
        
        $parent_db_id = $parent_data['id'];
        $parent_name = $parent_data['parent_name'];
        $email = $parent_data['email'];
        $mobile = $parent_data['mobile'];
        
        // Get notification settings if available
        if (!empty($parent_data['notification_settings'])) {
            $notification_settings = json_decode($parent_data['notification_settings'], true);
        }
    } else {
        throw new Exception("Parent not found");
    }
    
    $parent_stmt->close();
    
    // Now get student and school information
    $student_stmt = $conn->prepare("
        SELECT 
            s.student_name,
            s.class,
            sc.school_name
        FROM 
            students s
        LEFT JOIN
            schools sc ON s.school_id = sc.id
        WHERE 
            s.parent_id = ?
    ");
    
    if (!$student_stmt) {
        throw new Exception("Error preparing student query: " . $conn->error);
    }
    
    $student_stmt->bind_param("i", $parent_db_id);
    
    if (!$student_stmt->execute()) {
        throw new Exception("Error executing student query: " . $student_stmt->error);
    }
    
    $student_result = $student_stmt->get_result();
    
    if ($student_result->num_rows > 0) {
        $student_data = $student_result->fetch_assoc();
        $student_name = $student_data['student_name'];
        $student_class = $student_data['class'];
        $school_name = $student_data['school_name'];
    }
    
    $student_stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Error loading parent profile: " . $e->getMessage());
    $error_message = "An error occurred while loading your profile. Please try again later. Error: " . $e->getMessage();
}

// Handle success/error messages
$success_message = '';
if (!isset($error_message)) {
  $error_message = '';
}

if (isset($_SESSION['profile_update_success'])) {
  $success_message = $_SESSION['profile_update_success'];
  unset($_SESSION['profile_update_success']);
}

if (isset($_SESSION['profile_update_error'])) {
  $error_message = $_SESSION['profile_update_error'];
  unset($_SESSION['profile_update_error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Update your parent profile information and credentials">
  <title>My Profile - Samridhi Book Dress</title>
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
            <a class="nav-link" href="parent_dashboard.php"><i class="fas fa-home me-1"></i> Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="parent_orders.php"><i class="fas fa-shopping-bag me-1"></i> My Orders</a>
          </li>
        </ul>
        <ul class="navbar-nav ms-auto">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle active" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
              <span id="user-name"><?php echo htmlspecialchars($parent_name); ?></span>
              <i class="fas fa-user-circle ms-1"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item active" href="p_profile.php"><i class="fas fa-user-cog me-2"></i> Profile</a></li>
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
            <h5 class="card-title"><?php echo htmlspecialchars($student_name); ?></h5>
            <p class="card-text mb-1">Class: <?php echo htmlspecialchars($student_class); ?></p>
            <p class="card-text mb-0">School: <?php echo htmlspecialchars($school_name); ?></p>
          </div>
          <div class="col-md-3 text-center text-md-end">
            <span class="badge bg-info p-2 fs-6">
              <i class="fas fa-id-card me-1"></i> <?php echo htmlspecialchars($parent_id); ?>
            </span>
          </div>
        </div>
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

    <div class="row">
      <div class="col-md-3">
        <div class="card shadow-sm mb-4">
          <div class="card-body">
            <h5 class="mb-3">My Profile</h5>
            <ul class="nav flex-column nav-pills">
              <li class="nav-item">
                <a class="nav-link active" href="#personalInfo" data-bs-toggle="tab">Personal Information</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#passwordTab" data-bs-toggle="tab">Change Password</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" href="#notificationTab" data-bs-toggle="tab">Notification Settings</a>
              </li>
            </ul>
          </div>
        </div>
      </div>
      
      <div class="col-md-9">
        <div class="card shadow-sm">
          <div class="card-body p-4">
            <div class="tab-content">
              <!-- Personal Information Tab -->
              <div class="tab-pane fade show active" id="personalInfo">
                <h4 class="mb-4">Personal Information</h4>
                <form id="personalInfoForm" action="api/update_parent_profile.php" method="post">
                  <input type="hidden" name="form_type" value="personal_info">
                  
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="parentID" class="form-label">Parent ID</label>
                      <input type="text" class="form-control" id="parentID" value="<?php echo htmlspecialchars($parent_id); ?>" readonly>
                    </div>
                    <div class="col-md-6">
                      <label for="parentName" class="form-label">Parent Name</label>
                      <input type="text" class="form-control" id="parentName" name="parent_name" value="<?php echo htmlspecialchars($parent_name); ?>" required>
                    </div>
                  </div>
                  
                  <div class="row mb-3">
                    <div class="col-md-6">
                      <label for="emailAddress" class="form-label">Email Address</label>
                      <input type="email" class="form-control" id="emailAddress" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                    </div>
                    <div class="col-md-6">
                      <label for="mobileNumber" class="form-label">Mobile Number</label>
                      <input type="tel" class="form-control" id="mobileNumber" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>" required>
                    </div>
                  </div>
                  
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-save me-2"></i>Save Changes
                    </button>
                  </div>
                </form>
              </div>
              
              <!-- Password Tab -->
              <div class="tab-pane fade" id="passwordTab">
                <h4 class="mb-4">Change Password</h4>
                <form id="passwordForm" action="api/update_parent_profile.php" method="post">
                  <input type="hidden" name="form_type" value="password">
                  
                  <div class="mb-3">
                    <label for="currentPassword" class="form-label">Current Password</label>
                    <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                  </div>
                  
                  <div class="mb-3">
                    <label for="newPassword" class="form-label">New Password</label>
                    <input type="password" class="form-control" id="newPassword" name="new_password" required>
                    <div class="form-text">Password must be at least 8 characters long</div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="confirmPassword" class="form-label">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                  </div>
                  
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-key me-2"></i>Change Password
                    </button>
                  </div>
                </form>
              </div>
              
              <!-- Notification Settings Tab -->
              <div class="tab-pane fade" id="notificationTab">
                <h4 class="mb-4">Notification Settings</h4>
                <form id="notificationForm" action="api/update_parent_profile.php" method="post">
                  <input type="hidden" name="form_type" value="notifications">
                  
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="emailNotification" name="email_notification" <?php echo (isset($notification_settings['email']) && $notification_settings['email']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="emailNotification">Email Notifications</label>
                    <div class="form-text">Receive notifications via email</div>
                  </div>
                  
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="smsNotification" name="sms_notification" <?php echo (isset($notification_settings['sms']) && $notification_settings['sms']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="smsNotification">SMS Notifications</label>
                    <div class="form-text">Receive notifications via SMS</div>
                  </div>
                  
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="orderNotification" name="order_notification" <?php echo (isset($notification_settings['order']) && $notification_settings['order']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="orderNotification">Order Status Updates</label>
                    <div class="form-text">Receive notifications when your order status changes</div>
                  </div>
                  
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" id="promotionNotification" name="promotion_notification" <?php echo (isset($notification_settings['promotion']) && $notification_settings['promotion']) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="promotionNotification">Promotional Updates</label>
                    <div class="form-text">Receive promotional updates and offers</div>
                  </div>
                  
                  <div class="text-end">
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-bell me-2"></i>Update Notification Settings
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
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

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Password confirmation validation
    document.getElementById('passwordForm')?.addEventListener('submit', function(e) {
      const newPassword = document.getElementById('newPassword').value;
      const confirmPassword = document.getElementById('confirmPassword').value;
      
      if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('The passwords do not match. Please try again.');
      }
    });

    // Logout functionality
    document.getElementById('logout-btn').addEventListener('click', function(e) {
      e.preventDefault();
      
      fetch('api/logout.php', {
        method: 'POST',
        credentials: 'same-origin'
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          window.location.href = data.redirect || 'parent_login.php';
        } else {
          console.error('Logout failed:', data.message);
          window.location.href = 'parent_login.php';
        }
      })
      .catch(error => {
        console.error('Logout failed:', error);
        window.location.href = 'parent_login.php';
      });
    });
    
  </script>
</body>
</html>
