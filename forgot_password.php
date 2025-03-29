<?php
// Initialize variables
$email = $user_type = "";
$email_err = $user_type_err = $success_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email address.";
    } else {
        $email = trim($_POST["email"]);
        // Check if email format is valid
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    
    // Validate user type
    if (empty($_POST["user_type"])) {
        $user_type_err = "Please select your account type.";
    } else {
        $user_type = $_POST["user_type"];
    }
    
    // If no errors, process the password reset request
    if (empty($email_err) && empty($user_type_err)) {
        // In a real application, you would:
        // 1. Check if the email exists in the database
        // 2. Generate a unique token and store it with the user's record
        // 3. Send an email with a link containing the token to reset password
        
        // For this demo, we'll just show a success message
        $success_message = "If an account with this email exists, password reset instructions have been sent. Please check your inbox.";
        
        // Clear the form
        $email = $user_type = "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - School Inventory Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        .forgot-container {
            min-height: calc(100vh - 170px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }
        
        .forgot-card {
            max-width: 550px;
            width: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .forgot-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .forgot-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        
        .forgot-header-decoration {
            position: absolute;
            bottom: -20px;
            right: -20px;
            opacity: 0.15;
            font-size: 7rem;
        }
        
        .forgot-body {
            padding: 2rem;
            background-color: white;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
        }
        
        .form-floating > label {
            color: var(--secondary);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        
        .btn-forgot {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
        }
        
        .btn-forgot:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .forgot-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background-color: rgba(0, 0, 0, 0.01);
        }
        
        .forgot-icon {
            max-width: 70px;
            margin-bottom: 1rem;
        }
        
        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        .auth-links a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }
        
        .account-type-selector {
            margin-bottom: 2rem;
        }
        
        .account-type-card {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 10px;
            transition: all 0.3s ease;
            padding: 1rem;
            height: 100%;
        }
        
        .account-type-card.selected {
            border-color: var(--primary);
            background-color: rgba(25, 135, 84, 0.05);
        }
        
        .account-type-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }
        
        .account-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }
        
        /* Media Query for smaller screens */
        @media (max-width: 576px) {
            .forgot-card {
                margin: 0 1rem;
            }
            
            .forgot-header, .forgot-body {
                padding: 1.5rem;
            }
        }
        
        /* Animation for success message */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 20px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .animate-fadeInUp {
            animation: fadeInUp 0.5s ease;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="img/logo.svg" alt="School Inventory" height="40">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="parent_login.php">Parent Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="school_login.php">School Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Forgot Password Container -->
    <div class="forgot-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="forgot-card">
                        <div class="forgot-header">
                            <!-- Lock SVG Icon -->
                            <svg class="forgot-icon" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <rect x="25" y="45" width="50" height="40" rx="5" fill="white"/>
                                <rect x="35" y="60" width="30" height="15" rx="2" fill="rgba(0,0,0,0.1)"/>
                                <circle cx="50" cy="67" r="5" fill="white"/>
                                <rect x="47" y="67" width="6" height="8" fill="white"/>
                                <path d="M30,45 V30 C30,20 40,15 50,15 C60,15 70,20 70,30 V45" stroke="white" stroke-width="5" fill="none" stroke-linecap="round"/>
                            </svg>
                            <h2 class="mb-2">Forgot Password</h2>
                            <p class="mb-0">Reset your password to regain access to your account</p>
                            <div class="forgot-header-decoration">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        
                        <div class="forgot-body">
                            <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success alert-dismissible fade show animate-fadeInUp" role="alert">
                                <i class="fas fa-check-circle me-2"></i><?php echo $success_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                <p class="mb-4">Select your account type and enter your email address. We'll send you instructions to reset your password.</p>
                                
                                <div class="account-type-selector">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="account-type-card text-center <?php echo ($user_type == 'school') ? 'selected' : ''; ?>" data-account-type="school">
                                                <div class="account-icon">
                                                    <i class="fas fa-school"></i>
                                                </div>
                                                <h5>School Account</h5>
                                                <p class="mb-0 small">For school administrators</p>
                                                <input type="radio" name="user_type" value="school" class="form-check-input visually-hidden" <?php echo ($user_type == 'school') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="account-type-card text-center <?php echo ($user_type == 'parent') ? 'selected' : ''; ?>" data-account-type="parent">
                                                <div class="account-icon">
                                                    <i class="fas fa-user-friends"></i>
                                                </div>
                                                <h5>Parent Account</h5>
                                                <p class="mb-0 small">For parents and guardians</p>
                                                <input type="radio" name="user_type" value="parent" class="form-check-input visually-hidden" <?php echo ($user_type == 'parent') ? 'checked' : ''; ?>>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($user_type_err)): ?>
                                    <div class="text-danger mt-2 small"><i class="fas fa-exclamation-circle me-1"></i><?php echo $user_type_err; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-floating mb-4">
                                    <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" placeholder="Email address" value="<?php echo $email; ?>" required>
                                    <label for="email"><i class="fas fa-envelope me-2"></i>Email Address</label>
                                    <?php if (!empty($email_err)): ?>
                                    <div class="invalid-feedback"><?php echo $email_err; ?></div>
                                    <?php endif; ?>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-forgot">
                                    <i class="fas fa-paper-plane me-2"></i>Send Reset Instructions
                                </button>
                                
                                <div class="auth-links">
                                    <a href="school_login.php" class="d-inline-block me-3">School Login</a>
                                    <a href="parent_login.php" class="d-inline-block">Parent Login</a>
                                </div>
                            </form>
                        </div>
                        
                        <div class="forgot-footer">
                            <p class="mb-0">Need help? <a href="contact.php" class="fw-bold">Contact Support</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container py-4">
            <p class="mb-0">© 2025 School Inventory Management System | All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/main.js"></script>
    
    <script>
        // Account type selector functionality
        document.addEventListener('DOMContentLoaded', function() {
            const accountTypeCards = document.querySelectorAll('.account-type-card');
            
            accountTypeCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    accountTypeCards.forEach(c => c.classList.remove('selected'));
                    
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    
                    // Check the radio button
                    const radio = this.querySelector('input[type="radio"]');
                    radio.checked = true;
                    
                    // Add subtle animation
                    this.style.transform = 'scale(1.05)';
                    setTimeout(() => {
                        this.style.transform = 'translateY(-3px)';
                    }, 150);
                });
            });
            
            // Form validation with animation
            const form = document.querySelector('form.needs-validation');
            
            form.addEventListener('submit', function(event) {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    // Add shake animation to invalid elements
                    const invalidInputs = form.querySelectorAll(':invalid');
                    invalidInputs.forEach(input => {
                        input.classList.add('animate__animated', 'animate__shakeX');
                        setTimeout(() => {
                            input.classList.remove('animate__animated', 'animate__shakeX');
                        }, 1000);
                    });
                } else {
                    // Add loading state to button
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalButtonText = submitButton.innerHTML;
                    submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending...';
                    submitButton.disabled = true;
                    
                    // Re-enable after 2 seconds for demo purposes
                    // In production, this would be handled by the server response
                    setTimeout(() => {
                        submitButton.innerHTML = originalButtonText;
                        submitButton.disabled = false;
                    }, 2000);
                }
                
                form.classList.add('was-validated');
            });
        });
    </script>
</body>
</html> 