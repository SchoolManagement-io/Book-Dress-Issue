<?php
session_start();

// If user is already logged in as admin, redirect to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'admin') {
    header("Location: admin_dashboard.php");
    exit;
}

// Handle error messages
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $error = 'Invalid login credentials. Please try again.';
            break;
        case 'inactive':
            $error = 'Your account is inactive. Please contact support.';
            break;
        default:
            $error = 'An error occurred. Please try again.';
    }
}

if (isset($_SESSION['error_message'])) {
    $error = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Handle success messages
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'logout':
            $success = 'You have been successfully logged out.';
            break;
        default:
            $success = 'Operation completed successfully.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Login - Samridhi Book Dress">
    <title>Admin Login - Samridhi Book Dress</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/school_login.css">
    <link rel="preload" href="assets/pattern.svg" as="image">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Left Side - Login Form -->
            <div class="col-lg-5 login-form-container d-flex align-items-center order-2 order-lg-1">
                <div class="login-form-content w-100 p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <a href="index.php">
                            <img src="assets/logo.svg" alt="Samridhi Book Dress" height="70" class="mb-3">
                        </a>
                        <h1 class="h4 mb-2">Admin Login</h1>
                        <p class="text-muted">Access the administrative portal</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="adminLoginForm" action="api/admin_login.php" method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="adminEmail" class="form-label">Email Address</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input type="email" class="form-control" id="adminEmail" name="admin_email" 
                                       placeholder="Enter admin email" required 
                                       autocomplete="username">
                            </div>
                            <div class="invalid-feedback">Please enter your email address.</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" 
                                       placeholder="Enter password" required 
                                       autocomplete="current-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="invalid-feedback">Please enter your password.</div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Banner -->
            <div class="col-lg-7 login-banner-container d-flex align-items-center order-1 order-lg-2">
                <div class="banner-content position-relative z-1 text-white p-5">
                    <div class="mb-4">
                        <h2 class="display-5 fw-bold mb-3">Admin Portal</h2>
                        <p class="lead">Manage the entire system, create schools, and monitor operations.</p>
                    </div>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <h3 class="h5">User Management</h3>
                                <p class="mb-0">Create and manage admin accounts with specific access levels.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="h5">System Analytics</h3>
                                <p class="mb-0">View comprehensive reports and statistics on system usage.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3 class="h5">Advanced Security</h3>
                                <p class="mb-0">Secure access with multi-level authentication and monitoring.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h3 class="h5">Configuration Tools</h3>
                                <p class="mb-0">Access advanced system settings and configuration options.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="login-footer py-3 d-lg-none">
        <div class="container">
            <div class="text-center">
                <span class="text-muted">&copy; <?php echo date('Y'); ?> Samridhi Book Dress. All rights reserved.</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            document.querySelectorAll('.toggle-password').forEach(button => {
                button.addEventListener('click', function() {
                    const passwordInput = this.previousElementSibling;
                    const icon = this.querySelector('i');
                    
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Form validation
            const forms = document.querySelectorAll('.needs-validation');
            
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
            
            // AJAX form submission
            const loginForm = document.getElementById('adminLoginForm');
            
            loginForm.addEventListener('submit', function(event) {
                event.preventDefault();
                
                if (!this.checkValidity()) {
                    event.stopPropagation();
                    this.classList.add('was-validated');
                    return;
                }
                
                // Submit form via AJAX
                const formData = new FormData(this);
                
                // Show loading indicator
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
                
                // Clear previous errors
                const existingAlerts = document.querySelectorAll('.alert-danger');
                existingAlerts.forEach(alert => alert.remove());
                
                console.log('Submitting to: ' + this.action);
                
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    // If the response is a redirect, follow it
                    if (response.redirected) {
                        console.log('Redirected to:', response.url);
                        window.location.href = response.url;
                        return null;
                    }
                    return response.json().catch(error => {
                        console.error('Error parsing JSON:', error);
                        throw new Error('Invalid response format');
                    });
                })
                .then(data => {
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    
                    if (data === null) {
                        // Already redirected
                        return;
                    }
                    
                    console.log('Response data:', data);
                    
                    if (data.success) {
                        window.location.href = data.redirect || 'admin_dashboard.php';
                    } else {
                        // Show error message
                        const errorAlert = document.createElement('div');
                        errorAlert.className = 'alert alert-danger';
                        errorAlert.role = 'alert';
                        errorAlert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i> ${data.message || 'Invalid login credentials. Please try again.'}`;
                        
                        // Insert at the top of the form
                        const formContent = document.querySelector('.login-form-content');
                        formContent.insertBefore(errorAlert, document.querySelector('#adminLoginForm'));
                        
                        // Auto-hide after 5 seconds
                        setTimeout(() => {
                            errorAlert.classList.add('fade');
                            setTimeout(() => {
                                errorAlert.remove();
                            }, 500);
                        }, 5000);
                    }
                })
                .catch(error => {
                    // Reset button
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                    
                    console.error('Error:', error);
                    
                    // Show generic error message
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger';
                    errorAlert.role = 'alert';
                    errorAlert.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i> An error occurred. Please try again.';
                    
                    // Insert at the top of the form
                    const formContent = document.querySelector('.login-form-content');
                    formContent.insertBefore(errorAlert, document.querySelector('#adminLoginForm'));
                });
            });
            
            // Auto-hide alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('fade');
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 5000);
            });
        });
    </script>
</body>
</html>