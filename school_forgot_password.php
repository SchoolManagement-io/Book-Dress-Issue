<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'school') {
    header("Location: school_dashboard.php");
    exit();
}

// Check if a success message needs to be displayed
$success_message = '';
if (isset($_GET['reset_requested']) && $_GET['reset_requested'] == 'true') {
    $success_message = 'Password reset instructions have been sent to your registered email address.';
}

// Check if there's an error message to display
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'not_found':
            $error_message = 'School ID not found. Please check and try again.';
            break;
        case 'reset_error':
            $error_message = 'There was an error resetting your password. Please try again later.';
            break;
        case 'invalid_token':
            $error_message = 'Invalid or expired reset token. Please request a new password reset.';
            break;
        case 'invalid_input':
            $error_message = 'Please provide a valid School ID.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Samridhi Book Dress</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/school_login.css">
</head>
<body>
    <div class="container-fluid p-0">
        <div class="row g-0">
            <!-- Form Side -->
            <div class="col-lg-5 login-form-container d-flex align-items-center">
                <div class="login-form-content p-4 p-md-5">
                    <div class="text-center mb-4">
                        <a href="index.php" class="d-inline-block">
                            <img src="assets/logo.svg" alt="Samridhi Book Dress" width="180">
                        </a>
                        <h2 class="mt-3 mb-1 fw-bold text-primary">Forgot Password</h2>
                        <p class="text-muted">Enter your School ID to reset your password</p>
                    </div>
                    
                    <!-- Alerts Container -->
                    <div class="alerts-container">
                        <?php if ($error_message): ?>
                            <div class="alert alert-danger" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error_message; ?>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($success_message): ?>
                            <div class="alert alert-success" role="alert">
                                <i class="fas fa-check-circle me-2"></i> <?php echo $success_message; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Localhost Notice -->
                    <div class="alert alert-info mb-4" role="alert">
                        <i class="fas fa-info-circle me-2"></i> <strong>For Localhost Testing:</strong> 
                        Your password will be reset to <code>password123</code> when you submit this form.
                    </div>
                    
                    <!-- Forgot Password Form -->
                    <form id="forgotPasswordForm" action="api/request_password_reset.php" method="POST" class="needs-validation" novalidate>
                        <div class="mb-4">
                            <label for="schoolId" class="form-label">School ID</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-school"></i></span>
                                <input type="text" class="form-control" id="schoolId" name="school_id" placeholder="Enter your School ID" required>
                            </div>
                            <div class="form-text">Enter the School ID you registered with.</div>
                        </div>
                        
                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary py-2">
                                <i class="fas fa-key me-2"></i> Reset Password
                            </button>
                        </div>
                        
                        <div class="text-center">
                            <p class="mb-0">
                                <a href="school_login.php" class="text-decoration-none">
                                    <i class="fas fa-arrow-left me-1"></i> Back to Login
                                </a>
                            </p>
                        </div>
                    </form>
                </div>
                
                <!-- Footer -->
                <div class="login-footer py-3 text-center w-100">
                    <div class="container">
                        <p class="mb-0 text-muted">
                            &copy; <?php echo date('Y'); ?> Samridhi Book Dress. All rights reserved.
                            <span class="mx-1">|</span>
                            <a href="#" class="text-decoration-none text-muted">Privacy Policy</a>
                            <span class="mx-1">|</span>
                            <a href="#" class="text-decoration-none text-muted">Terms of Service</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Banner Side -->
            <div class="col-lg-7 login-banner-container d-none d-lg-block">
                <div class="banner-content p-5 text-white h-100 d-flex flex-column justify-content-center">
                    <h2 class="display-4 fw-bold mb-4">Password Recovery</h2>
                    <p class="lead mb-4">Don't worry! It happens to the best of us. We'll help you get back into your account.</p>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Secure Recovery</h3>
                                <p>Our password reset process is secure and protects your account information.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-bolt"></i>
                                </div>
                                <h3>Quick Process</h3>
                                <p>Get back to managing your school's inventory and orders in just a few minutes.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-sync-alt"></i>
                                </div>
                                <h3>Direct Reset</h3>
                                <p>Your password will be instantly reset to a default value you can use to log in.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <h3>Support Available</h3>
                                <p>Need help? Our support team is available to assist you with the process.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get elements
            const forgotPasswordForm = document.getElementById('forgotPasswordForm');
            const schoolIdInput = document.getElementById('schoolId');
            const alertsContainer = document.querySelector('.alerts-container');
            
            // Auto-hide alerts after 5 seconds
            if (alertsContainer) {
                const alerts = alertsContainer.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }, 5000);
                });
            }
            
            // Form validation
            if (forgotPasswordForm) {
                forgotPasswordForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Clear previous validation messages
                    clearValidationMessages();
                    
                    // Validate School ID
                    if (!schoolIdInput.value.trim()) {
                        showError(schoolIdInput, 'School ID is required');
                        isValid = false;
                    } else if (!/^[A-Za-z0-9]{6,}$/.test(schoolIdInput.value.trim())) {
                        showError(schoolIdInput, 'School ID must be at least 6 alphanumeric characters');
                        isValid = false;
                    }
                    
                    // If form is not valid, prevent submission
                    if (!isValid) {
                        e.preventDefault();
                    } else {
                        // Show loading overlay
                        showLoadingOverlay();
                    }
                });
            }
            
            // Show loading overlay
            function showLoadingOverlay() {
                const overlay = document.createElement('div');
                overlay.id = 'loading-overlay';
                overlay.innerHTML = `
                    <div class="spinner-border text-light mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <div class="text-white mb-2">Processing Request...</div>
                    <div class="loading-dots mt-2">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
            
            // Show error message for input
            function showError(input, message) {
                input.classList.add('is-invalid');
                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.innerText = message;
                input.parentNode.appendChild(errorDiv);
            }
            
            // Clear all validation messages
            function clearValidationMessages() {
                const invalidInputs = document.querySelectorAll('.is-invalid');
                const errorMessages = document.querySelectorAll('.invalid-feedback');
                
                invalidInputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });
                
                errorMessages.forEach(message => {
                    message.remove();
                });
            }
        });
    </script>
</body>
</html> 