<?php
// Initialize session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION["school_logged_in"]) && $_SESSION["school_logged_in"] === true) {
    header("Location: school_dashboard.php");
    exit();
}

// Initialize variables
$school_name = $school_address = $admin_name = $admin_email = $password = $confirm_password = $school_id = $phone = "";
$school_name_err = $school_address_err = $admin_name_err = $admin_email_err = $password_err = $confirm_password_err = $school_id_err = $phone_err = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate school name
    if (empty(trim($_POST["school_name"]))) {
        $school_name_err = "Please enter school name";
    } else {
        $school_name = trim($_POST["school_name"]);
    }
    
    // Validate school address
    if (empty(trim($_POST["school_address"]))) {
        $school_address_err = "Please enter school address";
    } else {
        $school_address = trim($_POST["school_address"]);
    }
    
    // Validate administrator name
    if (empty(trim($_POST["admin_name"]))) {
        $admin_name_err = "Please enter administrator name";
    } else {
        $admin_name = trim($_POST["admin_name"]);
    }
    
    // Validate admin email
    if (empty(trim($_POST["admin_email"]))) {
        $admin_email_err = "Please enter an email";
    } elseif (!filter_var(trim($_POST["admin_email"]), FILTER_VALIDATE_EMAIL)) {
        $admin_email_err = "Please enter a valid email";
    } else {
        $admin_email = trim($_POST["admin_email"]);
        
        // In a real application, you would check if the email exists in the database
        // For demonstration, we'll just check a simple case
        if ($admin_email === "school@example.com") {
            $admin_email_err = "This email is already registered in our system";
        }
    }
    
    // Validate phone
    if (empty(trim($_POST["phone"]))) {
        $phone_err = "Please enter contact phone number";
    } else {
        $phone = trim($_POST["phone"]);
    }
    
    // Validate school ID (optional - system might generate it automatically)
    if (empty(trim($_POST["school_id"]))) {
        $school_id_err = "Please enter a unique school ID";
    } else {
        $school_id = trim($_POST["school_id"]);
        
        // In a real application, you would check if the ID exists in the database
        // For demonstration, we'll just check a simple case
        if ($school_id === "SCH123") {
            $school_id_err = "This school ID is already registered";
        }
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password";
    } elseif (strlen(trim($_POST["password"])) < 8) {
        $password_err = "Password must have at least 8 characters";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm password";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Passwords did not match";
        }
    }
    
    // Check if there are no errors
    if (empty($school_name_err) && empty($school_address_err) && empty($admin_name_err) && 
        empty($admin_email_err) && empty($password_err) && empty($confirm_password_err) && 
        empty($school_id_err) && empty($phone_err)) {
        
        // In a real application, you would insert the school into the database
        // For demonstration, we'll just simulate a successful registration
        $registration_success = true;
        
        // Clear the form
        $school_name = $school_address = $admin_name = $admin_email = $password = $confirm_password = $school_id = $phone = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Registration - School Inventory Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Hind:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        .register-container {
            max-width: 700px;
            margin: 0 auto;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 1.5rem 0;
            margin-top: auto;
        }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: var(--gray-100);
        }
        main {
            flex: 1;
        }
        .invalid-feedback {
            display: block;
        }
        .card {
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 15px;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 1.5rem;
        }
        .card-title {
            font-weight: 600;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        /* Shake animation for errors */
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.6s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <img src="img/logo.svg" alt="School Logo" height="40" class="me-2">
                School Inventory Management System
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

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <div class="register-container">
                <?php if ($registration_success): ?>
                <div class="alert alert-success animate__animated animate__fadeIn" role="alert">
                    <h4 class="alert-heading"><i class="fas fa-check-circle me-2"></i>Registration Successful!</h4>
                    <p>Your school has been registered successfully. You can now <a href="school_login.php" class="alert-link">login</a> to access your dashboard.</p>
                    <hr>
                    <p class="mb-0">Your School ID is: <strong><?php echo htmlspecialchars($school_id); ?></strong>. Please keep this ID for future reference.</p>
                </div>
                <?php else: ?>
                <div class="card shadow animate__animated animate__fadeInUp">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0 text-center">School Registration</h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm">
                            <h5 class="mb-3">School Information</h5>
                            
                            <div class="mb-3">
                                <label for="school_name" class="form-label">School Name</label>
                                <input type="text" class="form-control <?php echo (!empty($school_name_err)) ? 'is-invalid' : ''; ?>" id="school_name" name="school_name" value="<?php echo htmlspecialchars($school_name); ?>" required>
                                <?php if (!empty($school_name_err)): ?>
                                <div class="invalid-feedback"><?php echo $school_name_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="school_address" class="form-label">School Address</label>
                                <textarea class="form-control <?php echo (!empty($school_address_err)) ? 'is-invalid' : ''; ?>" id="school_address" name="school_address" rows="2" required><?php echo htmlspecialchars($school_address); ?></textarea>
                                <?php if (!empty($school_address_err)): ?>
                                <div class="invalid-feedback"><?php echo $school_address_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="school_id" class="form-label">School ID (Unique Identifier)</label>
                                        <input type="text" class="form-control <?php echo (!empty($school_id_err)) ? 'is-invalid' : ''; ?>" id="school_id" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" required>
                                        <?php if (!empty($school_id_err)): ?>
                                        <div class="invalid-feedback"><?php echo $school_id_err; ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Create a unique ID for your school (e.g., SCHOOL123)</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Contact Phone</label>
                                        <input type="tel" class="form-control <?php echo (!empty($phone_err)) ? 'is-invalid' : ''; ?>" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required>
                                        <?php if (!empty($phone_err)): ?>
                                        <div class="invalid-feedback"><?php echo $phone_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Administrator Information</h5>
                            
                            <div class="mb-3">
                                <label for="admin_name" class="form-label">Administrator Name</label>
                                <input type="text" class="form-control <?php echo (!empty($admin_name_err)) ? 'is-invalid' : ''; ?>" id="admin_name" name="admin_name" value="<?php echo htmlspecialchars($admin_name); ?>" required>
                                <?php if (!empty($admin_name_err)): ?>
                                <div class="invalid-feedback"><?php echo $admin_name_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="admin_email" class="form-label">Administrator Email</label>
                                <input type="email" class="form-control <?php echo (!empty($admin_email_err)) ? 'is-invalid' : ''; ?>" id="admin_email" name="admin_email" value="<?php echo htmlspecialchars($admin_email); ?>" required>
                                <?php if (!empty($admin_email_err)): ?>
                                <div class="invalid-feedback"><?php echo $admin_email_err; ?></div>
                                <?php endif; ?>
                                <div class="form-text">We'll never share your email with anyone else.</div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" name="password" required>
                                            <i class="password-toggle fas fa-eye-slash position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePasswordVisibility('password')"></i>
                                        </div>
                                        <?php if (!empty($password_err)): ?>
                                        <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                        <?php endif; ?>
                                        <div class="form-text">Password must be at least 8 characters long.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <div class="position-relative">
                                            <input type="password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" id="confirm_password" name="confirm_password" required>
                                            <i class="password-toggle fas fa-eye-slash position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePasswordVisibility('confirm_password')"></i>
                                        </div>
                                        <?php if (!empty($confirm_password_err)): ?>
                                        <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">I agree to the <a href="#">terms and conditions</a></label>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-school me-2"></i>Register School
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p class="mb-0">Already registered your school? <a href="school_login.php" class="text-decoration-none">Login</a></p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer text-center">
        <div class="container">
            <p class="mb-0">© 2023 School Inventory Management System | All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Client-side validation script -->
    <script>
        // Toggle password visibility
        function togglePasswordVisibility(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = passwordField.nextElementSibling;
            
            if (passwordField.type === "password") {
                passwordField.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                passwordField.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('registrationForm');
            
            form.addEventListener('submit', function(e) {
                let isValid = true;
                
                // Get form fields
                const schoolName = document.getElementById('school_name').value.trim();
                const schoolAddress = document.getElementById('school_address').value.trim();
                const schoolId = document.getElementById('school_id').value.trim();
                const phone = document.getElementById('phone').value.trim();
                const adminName = document.getElementById('admin_name').value.trim();
                const adminEmail = document.getElementById('admin_email').value.trim();
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const terms = document.getElementById('terms').checked;
                
                // Validate school name
                if (schoolName === '') {
                    isValid = false;
                    showError('school_name', 'Please enter school name');
                } else {
                    clearError('school_name');
                }
                
                // Validate school address
                if (schoolAddress === '') {
                    isValid = false;
                    showError('school_address', 'Please enter school address');
                } else {
                    clearError('school_address');
                }
                
                // Validate school ID
                if (schoolId === '') {
                    isValid = false;
                    showError('school_id', 'Please enter a unique school ID');
                } else {
                    clearError('school_id');
                }
                
                // Validate phone
                if (phone === '') {
                    isValid = false;
                    showError('phone', 'Please enter contact phone number');
                } else {
                    clearError('phone');
                }
                
                // Validate admin name
                if (adminName === '') {
                    isValid = false;
                    showError('admin_name', 'Please enter administrator name');
                } else {
                    clearError('admin_name');
                }
                
                // Validate admin email
                if (adminEmail === '') {
                    isValid = false;
                    showError('admin_email', 'Please enter administrator email');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(adminEmail)) {
                    isValid = false;
                    showError('admin_email', 'Please enter a valid email address');
                } else {
                    clearError('admin_email');
                }
                
                // Validate password
                if (password === '') {
                    isValid = false;
                    showError('password', 'Please enter a password');
                } else if (password.length < 8) {
                    isValid = false;
                    showError('password', 'Password must be at least 8 characters long');
                } else {
                    clearError('password');
                }
                
                // Validate confirm password
                if (confirmPassword === '') {
                    isValid = false;
                    showError('confirm_password', 'Please confirm your password');
                } else if (password !== confirmPassword) {
                    isValid = false;
                    showError('confirm_password', 'Passwords do not match');
                } else {
                    clearError('confirm_password');
                }
                
                // Validate terms
                if (!terms) {
                    isValid = false;
                    alert('Please agree to the terms and conditions');
                }
                
                if (!isValid) {
                    e.preventDefault();
                }
            });
            
            // Helper functions for validation
            function showError(fieldId, message) {
                const field = document.getElementById(fieldId);
                field.classList.add('is-invalid');
                
                // Add shake animation
                field.classList.add('shake');
                setTimeout(() => {
                    field.classList.remove('shake');
                }, 600);
                
                // Check if error message already exists
                let feedback = field.nextElementSibling;
                if (feedback && feedback.classList.contains('password-toggle')) {
                    feedback = feedback.nextElementSibling;
                }
                if (!feedback || !feedback.classList.contains('invalid-feedback')) {
                    feedback = document.createElement('div');
                    feedback.classList.add('invalid-feedback');
                    
                    if (field.nextElementSibling && field.nextElementSibling.classList.contains('password-toggle')) {
                        field.parentNode.insertBefore(feedback, field.nextElementSibling.nextSibling);
                    } else {
                        field.parentNode.insertBefore(feedback, field.nextSibling);
                    }
                }
                
                feedback.textContent = message;
            }
            
            function clearError(fieldId) {
                const field = document.getElementById(fieldId);
                field.classList.remove('is-invalid');
            }
        });
    </script>
</body>
</html> 