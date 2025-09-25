<?php
session_start();

// Check if user is logged in as a school
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'school') {
    header("Location: school_login.php");
    exit();
}

// Database connection configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Get school data
$school_id = $_SESSION['user_id'];
$school_name = '';
$email = '';
$phone = '';
$address = '';
$logo = 'assets/default_school_logo.svg';
$school_type = '';
$established_year = '';
$website = '';
$pincode = '';
$email_notification = true;
$sms_notification = true;
$order_notification = true;
$payment_notification = true;
$student_notification = false;

try {
    // Connect to the database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Set the character set to UTF-8
    $conn->set_charset('utf8mb4');

    // Get school data
    $stmt = $conn->prepare("SELECT school_name, email, phone, address FROM schools WHERE school_id = ?");
    $stmt->bind_param("s", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $school = $result->fetch_assoc();
        
        $school_name = $school['school_name'];
        $email = $school['email'];
        $phone = $school['phone'];
        $address = $school['address'];
        
        // Get notification settings
        $notification_settings = json_decode($school['notification_settings'] ?? '{}', true);
        $email_notification = $notification_settings['email'] ?? true;
        $sms_notification = $notification_settings['sms'] ?? true;
        $order_notification = $notification_settings['order'] ?? true;
        $payment_notification = $notification_settings['payment'] ?? true;
        $student_notification = $notification_settings['student'] ?? false;
    }
    
    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Error loading school profile: " . $e->getMessage());
}

// Handle success/error messages
$success_message = '';
$error_message = '';

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
  <title>Edit Profile | School Samridhi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Update your school profile information and credentials">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/school_dashboard.css">
  <link rel="preload" href="assets/paisley.svg" as="image">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary px-4">
  <a class="navbar-brand fw-bold" href="#">
    <img src="assets/logo.svg" alt="School Logo" height="40" class="me-2" width="40">
    Samridhi Book Dress
  </a>
  <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse" id="navbarNav">
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="school_dashboard.php">Dashboard</a></li>
      <li class="nav-item"><a class="nav-link" href="school_students.php">Students</a></li>
      <li class="nav-item"><a class="nav-link" href="school_orders.php">Orders</a></li>
      <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle active" data-bs-toggle="dropdown" href="#">Settings</a>
        <ul class="dropdown-menu dropdown-menu-end">
          <li><a class="dropdown-item active" href="school_edit_profile.php">Edit Profile</a></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item" href="api/logout.php">Logout</a></li>
        </ul>
      </li>
    </ul>
  </div>
</nav>

<div class="container my-4">
  <div class="row">
    <div class="col-md-3">
      <div class="profile-sidebar card">
        <div class="card-body">
          <h5 class="mb-3">School Profile</h5>
          <ul class="nav flex-column nav-pills">
            <li class="nav-item">
              <a class="nav-link active" href="#basicInfo" data-bs-toggle="tab">Basic Information</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#passwordTab" data-bs-toggle="tab">Change Password</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#addressTab" data-bs-toggle="tab">Address Details</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#notificationTab" data-bs-toggle="tab">Notification Settings</a>
            </li>
          </ul>
        </div>
      </div>
    </div>
    
    <div class="col-md-9">
      <!-- Success/Error alerts -->
      <?php if ($success_message): ?>
      <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <?php if ($error_message): ?>
      <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <div class="card">
        <div class="card-body p-4">
          <div class="tab-content">
            <!-- Basic Information Tab -->
            <div class="tab-pane fade show active" id="basicInfo">
              <h4 class="mb-4">Basic Information</h4>
              <form id="basicInfoForm" action="api/update_school_profile.php" method="post">
                <input type="hidden" name="form_type" value="basic_info">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="schoolID" class="form-label">School ID</label>
                    <input type="text" class="form-control" id="schoolID" value="<?php echo htmlspecialchars($school_id); ?>" readonly>
                  </div>
                  <div class="col-md-6">
                    <label for="schoolName" class="form-label">School Name</label>
                    <input type="text" class="form-control" id="schoolName" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" required>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="emailAddress" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="emailAddress" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label for="phoneNumber" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="phoneNumber" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="schoolType" class="form-label">School Type</label>
                    <select class="form-select" id="schoolType" name="school_type">
                      <option value="Public" <?php echo $school_type == 'Public' ? 'selected' : ''; ?>>Public</option>
                      <option value="Private" <?php echo $school_type == 'Private' ? 'selected' : ''; ?>>Private</option>
                      <option value="International" <?php echo $school_type == 'International' ? 'selected' : ''; ?>>International</option>
                    </select>
                  </div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="establishedYear" class="form-label">Established Year</label>
                    <input type="number" class="form-control" id="establishedYear" name="established_year" value="<?php echo htmlspecialchars($established_year); ?>">
                  </div>
                  <div class="col-md-6">
                    <label for="website" class="form-label">Website</label>
                    <input type="url" class="form-control" id="website" name="website" value="<?php echo htmlspecialchars($website); ?>">
                  </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">
                  <i class="fas fa-save me-1"></i> Save Changes
                </button>
              </form>
            </div>
            
            <!-- Change Password Tab -->
            <div class="tab-pane fade" id="passwordTab">
              <h4 class="mb-4">Change Password</h4>
              <form id="passwordForm" action="api/update_school_profile.php" method="post">
                <input type="hidden" name="form_type" value="password">
                <div class="mb-3">
                  <label for="currentPassword" class="form-label">Current Password</label>
                  <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                </div>
                
                <div class="mb-3">
                  <label for="newPassword" class="form-label">New Password</label>
                  <input type="password" class="form-control" id="newPassword" name="new_password" required>
                  <div class="mt-2">
                    <div class="progress" style="height: 5px;">
                      <div id="passwordStrength" class="progress-bar bg-danger" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small id="strengthText" class="form-text text-muted">Password strength</small>
                  </div>
                  <div class="form-text">Password must be at least 8 characters with letters, numbers, and special characters.</div>
                </div>
                
                <div class="mb-3">
                  <label for="confirmPassword" class="form-label">Confirm New Password</label>
                  <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">
                  <i class="fas fa-key me-1"></i> Update Password
                </button>
              </form>
            </div>
            
            <!-- Address Details Tab -->
            <div class="tab-pane fade" id="addressTab">
              <h4 class="mb-4">Address Details</h4>
              <form id="addressForm" action="api/update_school_profile.php" method="post">
                <input type="hidden" name="form_type" value="address">
                <div class="mb-3">
                  <label for="address" class="form-label">School Address</label>
                  <textarea class="form-control" id="address" name="address" rows="3" required><?php echo htmlspecialchars($address); ?></textarea>
                  <div class="invalid-feedback">Please enter your school address.</div>
                </div>
                
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="pincode" class="form-label">PIN Code</label>
                    <input type="text" class="form-control" id="pincode" name="pincode" value="<?php echo htmlspecialchars($pincode); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" value="India" readonly>
                  </div>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">
                  <i class="fas fa-map-marker-alt me-1"></i> Save Address
                </button>
              </form>
            </div>
            
            <!-- Notification Settings Tab -->
            <div class="tab-pane fade" id="notificationTab">
              <h4 class="mb-4">Notification Settings</h4>
              <form id="notificationForm" action="api/update_school_profile.php" method="post">
                <input type="hidden" name="form_type" value="notifications">
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="emailNotification" name="email_notification" <?php echo $email_notification ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="emailNotification">Receive Email Notifications</label>
                </div>
                
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="smsNotification" name="sms_notification" <?php echo $sms_notification ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="smsNotification">Receive SMS Notifications</label>
                </div>
                
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="orderNotification" name="order_notification" <?php echo $order_notification ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="orderNotification">New Order Notifications</label>
                </div>
                
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="paymentNotification" name="payment_notification" <?php echo $payment_notification ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="paymentNotification">Payment Notifications</label>
                </div>
                
                <div class="mb-3 form-check">
                  <input type="checkbox" class="form-check-input" id="studentNotification" name="student_notification" <?php echo $student_notification ? 'checked' : ''; ?>>
                  <label class="form-check-label" for="studentNotification">Student Registration Notifications</label>
                </div>
                
                <button type="submit" class="btn btn-primary mt-3">
                  <i class="fas fa-bell me-1"></i> Save Settings
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="d-none">
  <div class="spinner-border text-light" aria-hidden="true"></div>
  <p class="text-white mt-3">Please wait<span class="loading-dots"><span>.</span><span>.</span><span>.</span></span></p>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/school_edit_profile.js"></script>
</body>
</html> 