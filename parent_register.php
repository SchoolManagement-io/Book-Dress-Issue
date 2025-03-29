<?php
// Initialize session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION["parent_logged_in"]) && $_SESSION["parent_logged_in"] === true) {
    header("Location: parent_dashboard.php");
    exit();
}

// Initialize variables
$name = $email = $password = $confirm_password = $child_name = $class = $school_id = "";
$name_err = $email_err = $password_err = $confirm_password_err = $child_name_err = $class_err = $school_id_err = "";
$registration_success = false;

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your name";
    } else {
        $name = trim($_POST["name"]);
    }
    
    // Validate email
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter an email";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email";
    } else {
        $email = trim($_POST["email"]);
        
        // In a real application, you would check if the email exists in the database
        // Here's how it would be done with a database:
        /*
        $sql = "SELECT id FROM parents WHERE email = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $email_err = "This email is already taken";
        }
        */
        
        // For demonstration, we'll just check a simple case
        if ($email === "parent@example.com") {
            $email_err = "This email is already registered in our system";
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
    
    // Validate child's name
    if (empty(trim($_POST["child_name"]))) {
        $child_name_err = "Please enter your child's name";
    } else {
        $child_name = trim($_POST["child_name"]);
    }
    
    // Validate class
    if (empty(trim($_POST["class"]))) {
        $class_err = "Please select your child's class";
    } else {
        $class = trim($_POST["class"]);
    }
    
    // Validate school ID
    if (empty(trim($_POST["school_id"]))) {
        $school_id_err = "Please enter the school ID";
    } else {
        $school_id = trim($_POST["school_id"]);
        
        // In a real application, you would validate that the school ID exists
        // Here's how it might be done:
        /*
        $sql = "SELECT id FROM schools WHERE school_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$school_id]);
        if ($stmt->rowCount() == 0) {
            $school_id_err = "Invalid school ID";
        }
        */
    }
    
    // Check if there are no errors
    if (empty($name_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && 
        empty($child_name_err) && empty($class_err) && empty($school_id_err)) {
        
        // In a real application, you would insert the user into the database
        // Here's how it might be done:
        /*
        try {
            // Hash the password for security
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Begin transaction
            $pdo->beginTransaction();
            
            // Insert parent
            $sql = "INSERT INTO parents (name, email, password) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$name, $email, $hashed_password]);
            $parent_id = $pdo->lastInsertId();
            
            // Insert child information
            $sql = "INSERT INTO children (parent_id, name, class, school_id) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$parent_id, $child_name, $class, $school_id]);
            
            // Commit transaction
            $pdo->commit();
            
            // Set session variables
            $_SESSION["parent_logged_in"] = true;
            $_SESSION["parent_email"] = $email;
            $_SESSION["parent_name"] = $name;
            
            // Redirect to dashboard
            header("Location: parent_dashboard.php");
            exit();
        } catch (Exception $e) {
            // Rollback transaction on error
            $pdo->rollBack();
            $general_err = "An error occurred. Please try again later.";
        }
        */
        
        // For demonstration, we'll just simulate a successful registration
        $registration_success = true;
        
        // Clear the form
        $name = $email = $password = $confirm_password = $child_name = $class = $school_id = "";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Registration - School Inventory Management</title>
    
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
                    <p>Your account has been registered successfully. You can now <a href="parent_login.php" class="alert-link">login</a> to access your dashboard.</p>
                    <hr>
                    <p class="mb-0">You will receive updates about your child's inventory needs and school requirements.</p>
                </div>
                <?php else: ?>
                <div class="card shadow animate__animated animate__fadeInUp">
                    <div class="card-header bg-primary text-white">
                        <h3 class="card-title mb-0 text-center">Parent Registration</h3>
                    </div>
                    <div class="card-body p-4">
                        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="registrationForm">
                            <h5 class="mb-3">Parent Information</h5>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control <?php echo (!empty($name_err)) ? 'is-invalid' : ''; ?>" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                                <?php if (!empty($name_err)): ?>
                                <div class="invalid-feedback"><?php echo $name_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                <?php if (!empty($email_err)): ?>
                                <div class="invalid-feedback"><?php echo $email_err; ?></div>
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
                            
                            <hr class="my-4">
                            <h5 class="mb-3">Child Information</h5>
                            
                            <div class="mb-3">
                                <label for="child_name" class="form-label">Child's Name</label>
                                <input type="text" class="form-control <?php echo (!empty($child_name_err)) ? 'is-invalid' : ''; ?>" id="child_name" name="child_name" value="<?php echo htmlspecialchars($child_name); ?>" required>
                                <?php if (!empty($child_name_err)): ?>
                                <div class="invalid-feedback"><?php echo $child_name_err; ?></div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="class" class="form-label">Class</label>
                                        <select class="form-select <?php echo (!empty($class_err)) ? 'is-invalid' : ''; ?>" id="class" name="class" required>
                                            <option value="" <?php echo empty($class) ? 'selected' : ''; ?>>Select Class</option>
                                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                            <option value="Class <?php echo $i; ?>" <?php echo $class === "Class $i" ? 'selected' : ''; ?>>Class <?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                        <?php if (!empty($class_err)): ?>
                                        <div class="invalid-feedback"><?php echo $class_err; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="school_id" class="form-label">School ID</label>
                                        <input type="text" class="form-control <?php echo (!empty($school_id_err)) ? 'is-invalid' : ''; ?>" id="school_id" name="school_id" value="<?php echo htmlspecialchars($school_id); ?>" required>
                                        <?php if (!empty($school_id_err)): ?>
                                        <div class="invalid-feedback"><?php echo $school_id_err; ?></div>
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
                                    <i class="fas fa-user-plus me-2"></i>Register Account
                                </button>
                            </div>
                        </form>
                        
                        <div class="mt-3 text-center">
                            <p class="mb-0">Already have an account? <a href="parent_login.php" class="text-decoration-none">Login</a></p>
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
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                const childName = document.getElementById('child_name').value.trim();
                const classSelect = document.getElementById('class').value;
                const schoolId = document.getElementById('school_id').value.trim();
                const terms = document.getElementById('terms').checked;
                
                // Validate name
                if (name === '') {
                    isValid = false;
                    showError('name', 'Please enter your name');
                } else {
                    clearError('name');
                }
                
                // Validate email
                if (email === '') {
                    isValid = false;
                    showError('email', 'Please enter your email');
                } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                    isValid = false;
                    showError('email', 'Please enter a valid email address');
                } else {
                    clearError('email');
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
                
                // Validate child's name
                if (childName === '') {
                    isValid = false;
                    showError('child_name', 'Please enter your child\'s name');
                } else {
                    clearError('child_name');
                }
                
                // Validate class
                if (classSelect === '') {
                    isValid = false;
                    showError('class', 'Please select a class');
                } else {
                    clearError('class');
                }
                
                // Validate school ID
                if (schoolId === '') {
                    isValid = false;
                    showError('school_id', 'Please enter the school ID');
                } else {
                    clearError('school_id');
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
                    field.parentNode.insertBefore(feedback, field.nextSibling);
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