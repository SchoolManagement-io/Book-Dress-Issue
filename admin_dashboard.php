<?php
session_start();

// Check if user is logged in as an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: admin_login.php");
    exit;
}

// Database connection
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'database' => 'bookdress_db'
];

// Connect to database
try {
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    // Set character set
    $conn->set_charset('utf8mb4');
    
    // Query to get active registration codes
    $codes_query = "SELECT id, code, created_at, expires_at, used, used_at, used_by_school 
                   FROM school_registration_codes 
                   WHERE used = 0 AND expires_at > NOW() 
                   ORDER BY created_at DESC";
    $codes_result = $conn->query($codes_query);
    
    $active_codes = [];
    if ($codes_result && $codes_result->num_rows > 0) {
        while ($row = $codes_result->fetch_assoc()) {
            $active_codes[] = $row;
        }
    }
} catch (Exception $e) {
    error_log("Error retrieving active codes: " . $e->getMessage());
    $active_codes = [];
}

// Initialize message variables
$success_message = '';
$error_message = '';
$generated_code = '';

// Get generated code from session if it exists
if (isset($_SESSION['generated_code'])) {
    $generated_code = $_SESSION['generated_code'];
    $code_expires_at = $_SESSION['code_expires_at'];
    // Clear from session to prevent showing again on refresh
    unset($_SESSION['generated_code']);
    unset($_SESSION['code_expires_at']);
}

// Handle success/error messages from redirects
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'code_generated':
            $success_message = 'Registration code was successfully generated!';
            break;
        case 'logout':
            $success_message = 'You have been successfully logged out.';
            break;
    }
}

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'generation_failed':
            $error_message = 'Failed to generate registration code. Please try again.';
            break;
        case 'server_error':
            $error_message = 'A server error occurred. Please try again later.';
            break;
    }
}

// Get schools data
$schools_data = [];
try {
    $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }
    
    $query = "SELECT id AS school_id, school_name, created_at 
              FROM schools 
              WHERE status = 'active' 
              ORDER BY created_at DESC";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $schools_data[] = $row;
        }
    }
    
    $conn->close();
} catch (Exception $e) {
    error_log("Error fetching school data: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Samridhi Book Dress</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #E91E63;
            --primary-dark: #C2185B;
            --secondary-color: #2196F3;
            --text-color: #333;
            --light-gray: #f5f5f5;
            --dark-gray: #555;
            --success-color: #4CAF50;
            --danger-color: #F44336;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            color: var(--text-color);
            background-color: #f8f9fc;
            padding-top: 60px;
        }
        
        .navbar {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .card {
            border: none;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            margin-bottom: 20px;
        }
        
        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
            padding: 0.75rem 1.25rem;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .id-card {
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            color: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
        
        .id-card .title {
            font-size: 0.8rem;
            opacity: 0.8;
            margin-bottom: 0;
        }
        
        .id-card .value {
            font-size: 1.4rem;
            font-weight: 600;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#">
            <img src="assets/logo.svg" alt="Samridhi Logo" height="30" class="me-2">
            Admin Panel
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION['admin_email']); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#" id="logoutBtn"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content -->
<div class="container py-4">
    <!-- Page Heading -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">School Management</h1>
    </div>
    
    <?php if ($success_message): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <!-- Registration Code Generation -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-code me-1"></i> Generate School Registration Code
                </div>
                <div class="card-body">
                    <p>Create a one-time use school registration code for new school registration.</p>
                    <form method="post" action="api/generate_registration_code.php">
                        <div class="mb-3">
                            <label for="expiration_days" class="form-label">Expiration (days)</label>
                            <input type="number" class="form-control" id="expiration_days" name="expiration_days" value="7" min="1" max="30">
                            <div class="form-text">Number of days before the code expires</div>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus-circle me-2"></i> Generate New Registration Code
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Active Registration Codes -->
            <?php if (count($active_codes) > 0): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-list me-1"></i> Active Registration Codes
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Code</th>
                                    <th>Created</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($active_codes as $code): ?>
                                <tr>
                                    <td><code class="fw-bold"><?php echo $code['code']; ?></code></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($code['created_at'])); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($code['expires_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($generated_code): ?>
        <div class="col-lg-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <i class="fas fa-check-circle me-1"></i> New Registration Code Generated
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> This registration code is shown only once. Please save it now.
                    </div>
                    <div class="id-card mb-3">
                        <div class="text-center mb-2">
                            <p class="title mb-0">SCHOOL REGISTRATION CODE</p>
                            <h3 class="value mb-0 display-6"><?php echo $generated_code; ?></h3>
                            <p class="mt-3 text-light">Expires on: <?php echo date('M d, Y H:i', strtotime($code_expires_at)); ?></p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between mt-3">
                        <button type="button" class="btn btn-outline-primary" onclick="copyToClipboard('<?php echo $generated_code; ?>')">
                            <i class="fas fa-copy me-2"></i> Copy Code
                        </button>
                        <button type="button" class="btn btn-outline-success" onclick="printRegistrationCode('<?php echo $generated_code; ?>', '<?php echo $code_expires_at; ?>')">
                            <i class="fas fa-print me-2"></i> Print Code
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Registered Schools -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-school me-1"></i> Registered Schools
                </div>
                <div class="card-body">
                    <?php if (count($schools_data) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>School ID</th>
                                    <th>School Name</th>
                                    <th>Registration Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($schools_data as $school): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($school['school_id']); ?></td>
                                    <td><?php echo htmlspecialchars($school['school_name']); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($school['created_at'])); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                    <p class="text-muted">No schools registered yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Copy to clipboard function
        window.copyToClipboard = function(text) {
            navigator.clipboard.writeText(text)
                .then(() => {
                    // Show a temporary success message
                    const alertHtml = `<div class="alert alert-success alert-dismissible fade show copy-alert" role="alert">
                        <i class="fas fa-check-circle me-2"></i> Registration code copied to clipboard!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>`;
                    
                    const alertContainer = document.createElement('div');
                    alertContainer.innerHTML = alertHtml;
                    document.querySelector('.container').prepend(alertContainer.firstChild);
                    
                    // Auto dismiss after 3 seconds
                    setTimeout(() => {
                        const alert = document.querySelector('.copy-alert');
                        if (alert) {
                            alert.remove();
                        }
                    }, 3000);
                })
                .catch(err => {
                    console.error('Could not copy text: ', err);
                    alert('Failed to copy registration code. Please try again or copy manually.');
                });
        };
        
        // Print registration code function
        window.printRegistrationCode = function(code, expires_at) {
            const originalContent = document.body.innerHTML;
            const expiresDate = new Date(expires_at).toLocaleString();
            
            document.body.innerHTML = `
                <div style="max-width: 500px; margin: 20px auto; padding: 20px; font-family: Arial, sans-serif;">
                    <h2 style="text-align: center; margin-bottom: 20px;">School Registration Code</h2>
                    <div style="background: linear-gradient(45deg, #6a11cb, #2575fc); color: white; border-radius: 10px; padding: 20px; text-align: center;">
                        <p style="font-size: 0.8rem; opacity: 0.8; margin-bottom: 5px;">REGISTRATION CODE</p>
                        <h1 style="font-size: 2rem; font-weight: 600; margin-bottom: 10px;">${code}</h1>
                        <p style="margin-top: 15px;">Expires on: ${expiresDate}</p>
                    </div>
                    <p style="text-align: center; margin-top: 20px; font-size: 12px;">
                        Generated on ${new Date().toLocaleString()} • One-time use only
                    </p>
                    <p style="text-align: center; margin-top: 20px;">
                        <strong>Instructions:</strong><br>
                        1. Provide this code to the school administrator<br>
                        2. The code can only be used once for school registration<br>
                        3. The code will expire on the date shown above
                    </p>
                </div>
            `;
            
            window.print();
            document.body.innerHTML = originalContent;
            location.reload();
        };
        
        // Handle logout
        document.getElementById('logoutBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            fetch('api/logout.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.redirect) {
                        window.location.href = data.redirect;
                    } else {
                        console.error('Logout failed:', data.message);
                        alert('Logout failed. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    alert('Logout failed due to a network error. Please try again.');
                });
        });
    });
</script>
</body>
</html>