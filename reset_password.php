<?php
session_start();

// If the user is already logged in, redirect to the dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'school') {
    header('Location: school_dashboard.php');
    exit;
}

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header('Location: school_forgot_password.php?error=invalid_token');
    exit;
}

$token = $_GET['token'];
$token_valid = false;
$school_id = '';

// Include database configuration
require_once 'api/config.php';

try {
    // Connect to the database
    $conn = getDbConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }

    // Verify token from the system_logs table (temporary solution for simplified schema)
    $stmt = $conn->prepare("
        SELECT user_id, timestamp 
        FROM system_logs 
        WHERE action = 'password_reset_request' 
        AND CONCAT(user_id, '_', UNIX_TIMESTAMP(timestamp)) = ?
    ");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $token_data = $result->fetch_assoc();
        
        // Check if token is within 24 hours
        $timestamp = new DateTime($token_data['timestamp']);
        $now = new DateTime();
        $diff = $now->diff($timestamp);
        
        if ($diff->days < 1) {
            $token_valid = true;
            $school_id = $token_data['user_id'];
        }
    }
    
    $stmt->close();

    if (!$token_valid) {
        $conn->close();
        header('Location: school_forgot_password.php?error=invalid_token');
        exit;
    }

    // Get school name for display
    $stmt = $conn->prepare("SELECT school_name FROM schools WHERE id = ?");
    $stmt->bind_param("i", $school_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $school_name = '';
    
    if ($result->num_rows === 1) {
        $school_data = $result->fetch_assoc();
        $school_name = $school_data['school_name'];
    }
    
    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    // Log the error
    error_log("Reset password error: " . $e->getMessage());
    
    // Close database connection if it exists
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    
    // Redirect with error
    header('Location: school_forgot_password.php?error=invalid_token');
    exit;
}

// Check if the form is submitted
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate input
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password) || strlen($password) < 8) {
        $error_message = 'Password must be at least 8 characters long.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Passwords do not match.';
    } else {
        try {
            // Connect to the database
            $conn = getDbConnection();
            
            if (!$conn) {
                throw new Exception("Database connection failed");
            }
            
            // Update the password with plain text (no hashing)
            $stmt = $conn->prepare("UPDATE schools SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $password, $school_id);
            $result = $stmt->execute();
            $stmt->close();
            
            if ($result) {
                // Log the password reset in system_logs
                $action = 'password_reset_complete';
                $details = json_encode(['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
                $stmt = $conn->prepare("INSERT INTO system_logs (user_id, user_type, action, details, timestamp) VALUES (?, 'school', ?, ?, NOW())");
                $stmt->bind_param("iss", $school_id, $action, $details);
                $stmt->execute();
                $stmt->close();
                
                $conn->close();
                
                // Set success message
                $success_message = 'Your password has been reset successfully. You can now login with your new password.';
            } else {
                throw new Exception("Failed to update password");
            }
            
        } catch (Exception $e) {
            // Log the error
            error_log("Reset password error: " . $e->getMessage());
            
            // Close database connection if it exists
            if (isset($conn) && $conn instanceof mysqli) {
                $conn->close();
            }
            
            // Set error message
            $error_message = 'An error occurred while resetting your password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Samridhi Book Dress</title>
    
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
                        <h2 class="mt-3 mb-1 fw-bold text-primary">Reset Password</h2>
                        <p class="text-muted">Create a new password for your account</p>
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
                                <div class="mt-3">
                                    <a href="school_login.php" class="btn btn-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i> Go to Login
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!$success_message): ?>
                    <!-- Reset Password Form -->
                    <form id="resetPasswordForm" action="reset_password.php?token=<?php echo $token; ?>" method="POST" class="needs-validation" novalidate>
                        <?php if ($school_name): ?>
                            <div class="mb-4 text-center">
                                <div class="badge bg-primary p-2 px-3 fs-6">
                                    <i class="fas fa-school me-1"></i> <?php echo htmlspecialchars($school_name); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">New Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="passwordStrength" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small id="strengthText" class="form-text text-muted">Password strength</small>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="confirmPassword" class="form-label">Confirm Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm new password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
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
                    <?php endif; ?>
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
                    <h2 class="display-4 fw-bold mb-4">Create a Strong Password</h2>
                    <p class="lead mb-4">A strong password is your first line of defense against unauthorized access to your account.</p>
                    
                    <div class="row g-4 mt-3">
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <h3>Strong Password Tips</h3>
                                <p>Use at least 8 characters with a mix of letters, numbers, and symbols.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-ban"></i>
                                </div>
                                <h3>Avoid Common Patterns</h3>
                                <p>Don't use personal information or common words that are easy to guess.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-random"></i>
                                </div>
                                <h3>Use Unique Passwords</h3>
                                <p>Create different passwords for different accounts to enhance security.</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="feature-item">
                                <div class="feature-icon">
                                    <i class="fas fa-history"></i>
                                </div>
                                <h3>Update Regularly</h3>
                                <p>Change your passwords periodically to maintain account security.</p>
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
            const resetPasswordForm = document.getElementById('resetPasswordForm');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const alertsContainer = document.querySelector('.alerts-container');
            const strengthMeter = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            
            // Auto-hide alerts after 5 seconds (except success message)
            if (alertsContainer) {
                const alerts = alertsContainer.querySelectorAll('.alert-danger');
                alerts.forEach(alert => {
                    setTimeout(() => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.remove();
                        }, 500);
                    }, 5000);
                });
            }
            
            // Toggle password visibility
            const togglePasswordButtons = document.querySelectorAll('.toggle-password');
            togglePasswordButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.previousElementSibling;
                    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                    input.setAttribute('type', type);
                    
                    // Toggle icon
                    const icon = this.querySelector('i');
                    if (type === 'text') {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    } else {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                });
            });
            
            // Password strength meter
            if (passwordInput && strengthMeter && strengthText) {
                passwordInput.addEventListener('input', function() {
                    const password = this.value;
                    const strength = checkPasswordStrength(password);
                    
                    // Update strength meter
                    strengthMeter.style.width = strength.percentage + '%';
                    strengthMeter.className = 'progress-bar ' + strength.class;
                    strengthText.innerText = strength.text;
                });
            }
            
            // Form validation
            if (resetPasswordForm) {
                resetPasswordForm.addEventListener('submit', function(e) {
                    let isValid = true;
                    
                    // Clear previous validation messages
                    clearValidationMessages();
                    
                    // Validate Password
                    if (!passwordInput.value) {
                        showError(passwordInput, 'Password is required');
                        isValid = false;
                    } else if (passwordInput.value.length < 8) {
                        showError(passwordInput, 'Password must be at least 8 characters');
                        isValid = false;
                    }
                    
                    // Validate Confirm Password
                    if (!confirmPasswordInput.value) {
                        showError(confirmPasswordInput, 'Please confirm your password');
                        isValid = false;
                    } else if (passwordInput.value !== confirmPasswordInput.value) {
                        showError(confirmPasswordInput, 'Passwords do not match');
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
                    <div class="text-white mb-2">Resetting Password...</div>
                    <div class="loading-dots mt-2">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
            
            // Check password strength
            function checkPasswordStrength(password) {
                // Default values
                let strength = {
                    percentage: 0,
                    class: 'bg-danger',
                    text: 'Very Weak'
                };
                
                if (!password) {
                    return strength;
                }
                
                let score = 0;
                
                // Length check
                if (password.length >= 8) score += 20;
                if (password.length >= 12) score += 10;
                
                // Complexity checks
                if (/[a-z]/.test(password)) score += 15; // lowercase
                if (/[A-Z]/.test(password)) score += 15; // uppercase
                if (/[0-9]/.test(password)) score += 15; // numbers
                if (/[^A-Za-z0-9]/.test(password)) score += 15; // special characters
                
                // Mixed character types
                const variations = {
                    digits: /\d/.test(password),
                    lower: /[a-z]/.test(password),
                    upper: /[A-Z]/.test(password),
                    nonWords: /\W/.test(password)
                };
                
                let variationCount = 0;
                for (let check in variations) {
                    variationCount += (variations[check] === true) ? 1 : 0;
                }
                
                score += (variationCount - 1) * 10;
                
                // Set strength based on score
                if (score >= 80) {
                    strength = { percentage: 100, class: 'bg-success', text: 'Very Strong' };
                } else if (score >= 60) {
                    strength = { percentage: 75, class: 'bg-info', text: 'Strong' };
                } else if (score >= 40) {
                    strength = { percentage: 50, class: 'bg-warning', text: 'Medium' };
                } else if (score >= 20) {
                    strength = { percentage: 25, class: 'bg-danger', text: 'Weak' };
                } else {
                    strength = { percentage: 10, class: 'bg-danger', text: 'Very Weak' };
                }
                
                return strength;
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