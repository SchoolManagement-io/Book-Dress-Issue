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

// Initialize variables
$school_id = $_SESSION['user_id'];
$all_students = [];
$class_students = [];
$available_classes = [];
$parent_id_prefix = 'PID';

try {
    // Connect to the database
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Set the character set to UTF-8
    $conn->set_charset('utf8mb4');

    // Get available classes from the database
    $classes_stmt = $conn->prepare("
        SELECT DISTINCT class 
        FROM students 
        WHERE school_id = ? 
        ORDER BY class
    ");
    $classes_stmt->bind_param("s", $school_id);
    $classes_stmt->execute();
    $classes_result = $classes_stmt->get_result();
    
    while ($row = $classes_result->fetch_assoc()) {
        $available_classes[] = $row['class'];
    }
    $classes_stmt->close();

    // Get all students for this school
    $students_stmt = $conn->prepare("
        SELECT s.id, s.student_name, p.parent_id, p.parent_name, 
               p.email, p.mobile, s.class
        FROM students s
        JOIN parents p ON s.parent_id = p.id
        WHERE s.school_id = ?
        ORDER BY s.class, s.student_name
    ");
    $students_stmt->bind_param("i", $school_id); // Ensure the correct data type
    $students_stmt->execute();
    $students_result = $students_stmt->get_result();
    
    while ($row = $students_result->fetch_assoc()) {
        $all_students[] = $row;
        
        // Group students by class
        $class = $row['class'];
        if (!isset($class_students[$class])) {
            $class_students[$class] = [];
        }
        $class_students[$class][] = $row;
    }
    $students_stmt->close();
    
    // Generate next parent ID
    $parent_id_stmt = $conn->prepare("
        SELECT MAX(CAST(SUBSTRING(parent_id, 4) AS UNSIGNED)) AS max_id 
        FROM parents
        WHERE parent_id LIKE 'PID%'
    ");
    $parent_id_stmt->execute();
    $parent_id_result = $parent_id_stmt->get_result();
    $parent_id_row = $parent_id_result->fetch_assoc();
    $next_id = ($parent_id_row['max_id'] ?? 0) + 1;
    $next_parent_id = $parent_id_prefix . str_pad($next_id, 6, '0', STR_PAD_LEFT);
    $parent_id_stmt->close();
    
    $conn->close();
    
} catch (Exception $e) {
    // Log error
    error_log("Error loading students: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    $error_message = "An error occurred while loading student data. Please try again later.";
}

// Handle success/error messages
$success_message = '';
$error_message = '';

if (isset($_SESSION['student_action_success'])) {
    $success_message = $_SESSION['student_action_success'];
    unset($_SESSION['student_action_success']);
}

if (isset($_SESSION['student_action_error'])) {
    $error_message = $_SESSION['student_action_error'];
    unset($_SESSION['student_action_error']);
}

// If error_message was set in the try-catch block, it will override session error
if (isset($error_message) && !empty($error_message)) {
    $_SESSION['student_action_error'] = $error_message;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Manage your school's students and their information - Samridhi Book Dress">
  <title>Students Management - <?php echo isset($_SESSION['school_name']) ? htmlspecialchars($_SESSION['school_name']) : 'School Dashboard'; ?></title>
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
        <img src="<?php echo isset($_SESSION['school_logo']) ? htmlspecialchars($_SESSION['school_logo']) : 'assets/default_school_logo.svg'; ?>" alt="School Logo" id="schoolLogo" height="40" class="me-2 rounded-circle border border-light" width="40">
        <span id="schoolName"><?php echo isset($_SESSION['school_name']) ? htmlspecialchars($_SESSION['school_name']) : 'School Dashboard'; ?></span>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="school_dashboard.php"><i class="fas fa-tachometer-alt me-1"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="school_inventory.php"><i class="fas fa-boxes me-1"></i> Inventory</a></li>
          <li class="nav-item"><a class="nav-link active" href="school_students.php" aria-current="page"><i class="fas fa-user-graduate me-1"></i> Students</a></li>
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
        <h1 class="h3 mb-0 text-gray-800">Student Management</h1>
        <p class="text-muted mb-0">Manage your school's students and parents</p>
      </div>
      <div>
        <button class="btn btn-sm btn-outline-success me-2" id="exportExcel">
          <i class="fas fa-file-excel me-1"></i> Export Excel
        </button>
        <button class="btn btn-sm btn-outline-danger" id="printPDF">
          <i class="fas fa-file-pdf me-1"></i> Print PDF
        </button>
      </div>
    </div>
      
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
    
    <!-- Class Selection Tabs -->
    <div class="card shadow mb-4">
      <div class="card-header py-3 bg-white">
        <ul class="nav nav-tabs card-header-tabs" id="classTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all-students" type="button" role="tab">All Students</button>
          </li>
          
          <?php foreach ($available_classes as $class): ?>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="class<?php echo $class; ?>-tab" data-bs-toggle="tab" data-bs-target="#class<?php echo $class; ?>-students" type="button" role="tab">Class <?php echo $class; ?></button>
          </li>
          <?php endforeach; ?>
          
          <li class="nav-item" role="presentation">
            <button class="nav-link text-success" id="new-student-tab" data-bs-toggle="tab" data-bs-target="#new-student" type="button" role="tab">
              <i class="fas fa-plus-circle me-1"></i> Add New Student
            </button>
          </li>
        </ul>
      </div>
      
      <div class="card-body">
        <!-- Tab Content -->
        <div class="tab-content" id="classTabsContent">
          <!-- All Students -->
          <div class="tab-pane fade show active" id="all-students" role="tabpanel">
            <div class="d-flex justify-content-between my-3">
              <input type="text" class="form-control w-25" id="searchStudents" placeholder="Search students...">
              <div class="text-muted">
                Total Students: <span class="badge bg-primary"><?php echo count($all_students); ?></span>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Parent ID</th>
                    <th>Parent Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Class</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (count($all_students) > 0): ?>
                    <?php foreach ($all_students as $student): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                      <td><?php echo htmlspecialchars($student['parent_id']); ?></td>
                      <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                      <td><?php echo htmlspecialchars($student['email']); ?></td>
                      <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                      <td>Class <?php echo htmlspecialchars($student['class']); ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary me-1" 
                                onclick="editStudent(<?php echo $student['id']; ?>, '<?php echo addslashes($student['student_name']); ?>', '<?php echo addslashes($student['parent_name']); ?>', '<?php echo addslashes($student['email']); ?>', '<?php echo addslashes($student['mobile']); ?>', '<?php echo addslashes($student['parent_id']); ?>', '<?php echo $student['class']; ?>')">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                onclick="if(confirm('Are you sure you want to delete this student?')) window.location.href='api/manage_students.php?action=delete&id=<?php echo $student['id']; ?>'">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="7" class="text-center py-3">No students found. Click "Add New Student" to add one.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          
          <!-- Class-specific Students -->
          <?php foreach ($available_classes as $class): ?>
          <div class="tab-pane fade" id="class<?php echo $class; ?>-students" role="tabpanel">
            <div class="d-flex justify-content-between my-3">
              <h5>Class <?php echo $class; ?> Students</h5>
              <div class="text-muted">
                Total: <span class="badge bg-primary"><?php echo count($class_students[$class] ?? []); ?></span>
              </div>
            </div>
            
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th>Student Name</th>
                    <th>Parent ID</th>
                    <th>Parent Name</th>
                    <th>Email</th>
                    <th>Mobile</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (isset($class_students[$class]) && count($class_students[$class]) > 0): ?>
                    <?php foreach ($class_students[$class] as $student): ?>
                    <tr>
                      <td><?php echo htmlspecialchars($student['student_name']); ?></td>
                      <td><?php echo htmlspecialchars($student['parent_id']); ?></td>
                      <td><?php echo htmlspecialchars($student['parent_name']); ?></td>
                      <td><?php echo htmlspecialchars($student['email']); ?></td>
                      <td><?php echo htmlspecialchars($student['mobile']); ?></td>
                      <td>
                        <button class="btn btn-sm btn-primary me-1" 
                                onclick="editStudent(<?php echo $student['id']; ?>, '<?php echo addslashes($student['student_name']); ?>', '<?php echo addslashes($student['parent_name']); ?>', '<?php echo addslashes($student['email']); ?>', '<?php echo addslashes($student['mobile']); ?>', '<?php echo addslashes($student['parent_id']); ?>', '<?php echo $student['class']; ?>')">
                          <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" 
                                onclick="if(confirm('Are you sure you want to delete this student?')) window.location.href='api/manage_students.php?action=delete&id=<?php echo $student['id']; ?>'">
                          <i class="fas fa-trash"></i>
                        </button>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <tr>
                      <td colspan="6" class="text-center py-3">No students found for Class <?php echo $class; ?>.</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
          <?php endforeach; ?>
          
          <!-- New Student Form -->
          <div class="tab-pane fade" id="new-student" role="tabpanel">
            <form class="student-form card mt-3" id="addStudentForm" action="api/manage_students.php" method="post">
              <div class="card-header py-3 bg-white">
                <h5 class="mb-0 text-primary">Add New Student</h5>
              </div>
              <div class="card-body">
                <input type="hidden" name="action" value="add">
                <div class="row g-3">
                  <div class="col-md-6">
                    <label class="form-label">Student Name</label>
                    <input type="text" name="student_name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Parent Name</label>
                    <input type="text" name="parent_name" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Mobile Number</label>
                    <input type="tel" name="mobile" class="form-control" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Parent ID (Auto-generated)</label>
                    <input type="text" class="form-control" value="<?php echo $next_parent_id; ?>" disabled>
                    <input type="hidden" name="parent_id" value="<?php echo $next_parent_id; ?>">
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text">Minimum 8 characters</div>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Class</label>
                    <select class="form-select" name="class" required>
                      <option value="">Select Class</option>
                      <?php for ($i = 1; $i <= 12; $i++): ?>
                      <option value="<?php echo $i; ?>">Class <?php echo $i; ?></option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label">Student Photo</label>
                    <input type="file" name="photo" class="form-control">
                    <div class="form-text">Optional. Max size: 2MB</div>
                  </div>
                  <div class="col-12 mt-4 text-end">
                    <button type="reset" class="btn btn-secondary me-2">
                      <i class="fas fa-redo me-1"></i> Reset
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <i class="fas fa-plus-circle me-1"></i> Add Student
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Edit Student Modal -->
  <div class="modal fade" id="editStudentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editStudentForm" action="api/manage_students.php" method="post" enctype="multipart/form-data">
          <div class="modal-body">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="student_id" id="editStudentId">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Student Name</label>
                <input type="text" class="form-control" id="editStudentName" name="student_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Parent Name</label>
                <input type="text" class="form-control" id="editParentName" name="parent_name" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" id="editEmail" name="email" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="tel" class="form-control" id="editMobile" name="mobile" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Parent ID</label>
                <input type="text" class="form-control" id="editParentID" disabled>
              </div>
              <div class="col-md-6">
                <label class="form-label">Class</label>
                <select class="form-select" id="editClass" name="class" required>
                  <?php for ($i = 1; $i <= 12; $i++): ?>
                  <option value="<?php echo $i; ?>">Class <?php echo $i; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div class="col-md-6">
                <label class="form-label">New Password (Leave blank to keep current)</label>
                <input type="password" class="form-control" id="editPassword" name="password">
                <div class="form-text">Minimum 8 characters if changing</div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Update Photo</label>
                <input type="file" class="form-control" id="editPhoto" name="photo">
                <div class="form-text">Optional. Max size: 2MB</div>
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
  // Edit student function
  function editStudent(id, studentName, parentName, email, mobile, parentId, studentClass) {
    document.getElementById('editStudentId').value = id;
    document.getElementById('editStudentName').value = studentName;
    document.getElementById('editParentName').value = parentName;
    document.getElementById('editEmail').value = email;
    document.getElementById('editMobile').value = mobile;
    document.getElementById('editParentID').value = parentId;
    document.getElementById('editClass').value = studentClass;
    
    // Open the modal
    var modal = new bootstrap.Modal(document.getElementById('editStudentModal'));
    modal.show();
  }
  
  // Ensure the edit form submits properly
  document.addEventListener('DOMContentLoaded', function() {
    // Add event listener to the edit form
    document.getElementById('editStudentForm').addEventListener('submit', function(e) {
      // Validate form before submission
      if (!this.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      this.classList.add('was-validated');
    });
    
    // Add event listener to delete buttons
    var deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(function(button) {
      button.addEventListener('click', function(e) {
        if (!confirm('Are you sure you want to delete this student?')) {
          e.preventDefault();
        }
      });
    });
  });
  
  // Search functionality
  document.getElementById('searchStudents').addEventListener('input', function() {
    var searchText = this.value.toLowerCase();
    var rows = document.querySelectorAll('#all-students table tbody tr');
    
    rows.forEach(function(row) {
      var studentName = row.cells[0].textContent.toLowerCase();
      var parentId = row.cells[1].textContent.toLowerCase();
      var parentName = row.cells[2].textContent.toLowerCase();
      var email = row.cells[3].textContent.toLowerCase();
      var mobile = row.cells[4].textContent.toLowerCase();
      
      if (studentName.includes(searchText) || 
          parentId.includes(searchText) || 
          parentName.includes(searchText) || 
          email.includes(searchText) || 
          mobile.includes(searchText)) {
        row.style.display = '';
      } else {
        row.style.display = 'none';
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
  </script>
</body>
</html>