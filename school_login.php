<?php
// Start session
session_start();

// Check if user is already logged in as a school
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'school') {
    header("Location: school_dashboard.php");
    exit();
}

// Handle login error messages
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $error = 'Invalid school ID or password. Please try again.';
            break;
        case 'account_inactive':
            $error = 'Your account is inactive. Please contact support.';
            break;
        case 'session_expired':
            $error = 'Your session has expired. Please log in again.';
            break;
        default:
            $error = 'An error occurred. Please try again.';
    }
}

// Handle success messages
$success = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'password_reset':
            if (isset($_GET['default_password'])) {
                $success = 'Your password has been reset to <strong>password123</strong>. Please login with this password and change it after logging in.';
            } else {
                $success = 'Your password has been reset successfully. Please log in with your new password.';
            }
            break;
        case 'logout':
            $success = 'You have been logged out successfully.';
            break;
        case 'registration_complete':
        case 'registration':
            $success = 'Your school has been registered successfully. Please log in with your credentials.';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="School Login for Samridhi Book Dress - Log in to manage your inventory, students and orders">
    <title>School Login - Samridhi Book Dress</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/school_login.css">
    <link rel="preload" href="assets/pattern.svg" as="image">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            <!-- Left Side - Login Form -->
            <div class="col-md-5 col-lg-4 order-2 order-md-1 login-form-container d-flex align-items-center">
                <div class="login-form-content w-100 px-4 px-md-5 py-5">
                    <div class="text-center mb-4">
                        <a href="index.php">
                            <img src="assets/logo.svg" alt="Samridhi Book Dress" height="70" class="mb-3">
                        </a>
                        <h1 class="h4 mb-2">School Login</h1>
                        <p class="text-muted">Access your school dashboard</p>
                    </div>
                    
                    <!-- Any validation errors will show here -->
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Success message -->
                    <?php if ($success): ?>
                    <div class="alert alert-success" role="alert">
                        <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="schoolLoginForm" action="api/school_login.php" method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="school_id" class="form-label">School ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-school"></i></span>
                                <input type="text" class="form-control" id="school_id" name="school_id" placeholder="Enter your school ID" required autocomplete="username">
                                <div class="invalid-feedback">
                                    Please enter your school ID.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
                                <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <div class="invalid-feedback">
                                    Please enter your password.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4 d-flex justify-content-between align-items-center">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">Remember me</label>
                            </div>
                            <a href="school_forgot_password.php" class="text-primary small">Forgot password?</a>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-sign-in-alt me-2"></i> Log In
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="mb-0">Don't have an account? <a href="school_register.php" class="text-primary">Register your school</a></p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                        <a href="parent_login.php" class="btn btn-outline-primary btn-sm ms-2">
                            <i class="fas fa-user me-1"></i> Parent Login
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Right Side - Image and Info -->
            <div class="col-md-7 col-lg-8 order-1 order-md-2 login-banner-container d-flex align-items-center">
                <div class="banner-content position-relative z-1 text-white p-5">
                    <div class="mb-4">
                        <h2 class="display-5 fw-bold mb-3">Welcome to Samridhi Book Dress</h2>
                        <p class="lead">Simplify your school inventory management and streamline the ordering process for parents.</p>
                    </div>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-boxes"></i>
                                </div>
                                <h3 class="h5">Inventory Management</h3>
                                <p class="mb-0">Easily manage books, uniforms, and other supplies in one place.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                                <h3 class="h5">Order Processing</h3>
                                <p class="mb-0">Streamline order fulfillment and track delivery status.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h3 class="h5">Student Management</h3>
                                <p class="mb-0">Organize students by class and track their order history.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h3 class="h5">Analytics Dashboard</h3>
                                <p class="mb-0">Get insights with detailed reports and statistics.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="login-footer py-3">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                <div class="mb-2 mb-md-0">
                    <span class="text-muted">&copy; <?php echo date('Y'); ?> Samridhi Book Dress. All rights reserved.</span>
                </div>
                <div>
                    <a href="privacy_policy.php" class="text-muted me-3">Privacy Policy</a>
                    <a href="terms_conditions.php" class="text-muted">Terms & Conditions</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
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
        
        // Toggle password visibility
        const togglePassword = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password');
        
        togglePassword.addEventListener('click', function() {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle eye icon
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
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
        
        // AJAX form submission
        const loginForm = document.getElementById('schoolLoginForm');
        
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            if (!this.checkValidity()) {
                event.stopPropagation();
                this.classList.add('was-validated');
                return;
            }
            
            // Submit form via AJAX
            const formData = new FormData(this);
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    // Show error message
                    const errorAlert = document.createElement('div');
                    errorAlert.className = 'alert alert-danger';
                    errorAlert.role = 'alert';
                    errorAlert.innerHTML = `<i class="fas fa-exclamation-circle me-2"></i> ${data.message}`;
                    
                    // Insert at the top of the form
                    loginForm.parentNode.insertBefore(errorAlert, loginForm);
                    
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
                console.error('Error:', error);
            });
        });
    });
    </script>
</body>
</html> 