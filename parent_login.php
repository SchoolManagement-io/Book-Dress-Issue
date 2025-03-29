<?php
// Initialize the session
session_start();

// Check if the user is already logged in, if yes then redirect to parent dashboard
if(isset($_SESSION["parent_loggedin"]) && $_SESSION["parent_loggedin"] === true){
    header("location: parent_dashboard.php");
    exit;
}

// Define variables and initialize with empty values
$parentID = $password = "";
$parentID_err = $password_err = $login_err = "";

// Processing form data when form is submitted
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Check if parentID is empty
    if(empty(trim($_POST["parentID"]))){
        $parentID_err = "Please enter your Parent ID.";
    } else{
        $parentID = trim($_POST["parentID"]);
    }
    
    // Check if password is empty
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if(empty($parentID_err) && empty($password_err)){
        // For demo purposes, let's simulate a login with mock data
        // In a real application, this would validate against a database
        if($parentID == "P12345" && $password == "parent123"){
            // Password is correct, so start a new session
            session_start();
            
            // Store data in session variables
            $_SESSION["parent_loggedin"] = true;
            $_SESSION["user_type"] = "parent";
            $_SESSION["parent_id"] = "P12345";
            $_SESSION["parent_name"] = "Sarah Johnson";
            $_SESSION["child_name"] = "Alex Johnson";
            $_SESSION["child_class"] = "Class 5";
            $_SESSION["school_id"] = "SCH123";
            $_SESSION["school_name"] = "Springfield Elementary School";
            
            // Redirect user to parent dashboard page
            header("location: parent_dashboard.php");
        } else{
            // Parent ID or password is invalid, display a generic error message
            $login_err = "Invalid Parent ID or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parent Login - School Inventory Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Animate.css for animations -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/styles.css">
    
    <style>
        .login-container {
            min-height: calc(100vh - 170px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><rect x="0" y="0" width="100" height="100" fill="%23f8f9fa"/><circle cx="20" cy="20" r="5" fill="%23198754" opacity="0.1"/><circle cx="80" cy="80" r="5" fill="%23198754" opacity="0.1"/><circle cx="80" cy="20" r="5" fill="%23198754" opacity="0.1"/><circle cx="20" cy="80" r="5" fill="%23198754" opacity="0.1"/></svg>');
            background-size: 100px;
        }
        
        .login-card {
            max-width: 450px;
            width: 100%;
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            transform: translateY(0);
            animation: fadeInUp 0.5s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            padding: 2rem;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-header-decoration {
            position: absolute;
            bottom: -20px;
            right: -20px;
            opacity: 0.15;
            font-size: 7rem;
        }
        
        .login-body {
            padding: 2rem;
            background-color: white;
        }
        
        .form-floating {
            margin-bottom: 1.5rem;
            position: relative;
        }
        
        .form-floating > label {
            color: var(--secondary);
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
        }
        
        .form-control:focus + label {
            color: var(--primary);
        }
        
        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        
        .btn-login {
            padding: 0.6rem 1.5rem;
            font-weight: 500;
            border-radius: 5px;
            transition: all 0.3s ease;
            display: block;
            width: 100%;
            position: relative;
            overflow: hidden;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(25, 135, 84, 0.3);
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }
        
        .btn-login:hover::after {
            left: 100%;
        }
        
        .login-footer {
            text-align: center;
            padding: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            background-color: rgba(0, 0, 0, 0.01);
        }
        
        .parent-svg {
            max-width: 70px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .parent-svg:hover {
            transform: scale(1.1);
        }
        
        .auth-links {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .auth-links a {
            color: var(--primary);
            text-decoration: none;
            transition: all 0.2s ease;
            position: relative;
            display: inline-block;
        }
        
        .auth-links a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary);
            transition: width 0.3s ease;
        }
        
        .auth-links a:hover {
            color: var(--primary-dark);
        }
        
        .auth-links a:hover::after {
            width: 100%;
        }
        
        /* Password toggle visibility */
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--secondary);
            z-index: 10;
        }
        
        .password-toggle:hover {
            color: var(--primary);
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
        
        /* Media Query for smaller screens */
        @media (max-width: 576px) {
            .login-card {
                margin: 0 1rem;
            }
            
            .login-header, .login-body {
                padding: 1.5rem;
            }
        }
        
        /* Floating particles effect */
        .particles {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }
        
        .particle {
            position: absolute;
            border-radius: 50%;
            background-color: var(--primary);
            opacity: 0.2;
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
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
                        <a class="nav-link active" aria-current="page" href="parent_login.php">Parent Login</a>
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

    <!-- Login Container -->
    <div class="login-container">
        <!-- Particles effect -->
        <div class="particles"></div>
        
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="login-card">
                        <div class="login-header text-center">
                            <!-- Parent SVG Icon -->
                            <svg class="parent-svg" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <circle cx="50" cy="30" r="20" fill="white"/>
                                <path d="M50,50 L50,90 M30,60 L70,60" stroke="white" stroke-width="8" stroke-linecap="round"/>
                                <path d="M20,90 L80,90" stroke="white" stroke-width="8" stroke-linecap="round"/>
                                <circle cx="34" cy="28" r="3" fill="rgba(0,0,0,0.2)"/>
                                <circle cx="66" cy="28" r="3" fill="rgba(0,0,0,0.2)"/>
                                <path d="M40,40 C45,45 55,45 60,40" stroke="rgba(0,0,0,0.2)" stroke-width="2" fill="none"/>
                            </svg>
                            <h2 class="mb-2">Parent Login</h2>
                            <p class="mb-0">Access your child's school supplies</p>
                            <div class="login-header-decoration">
                                <i class="fas fa-user-friends"></i>
                            </div>
                        </div>
                        
                        <div class="login-body">
                            <?php if (!empty($login_err)): ?>
                            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $login_err; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control <?php echo (!empty($parentID_err)) ? 'is-invalid' : ''; ?>" id="parentID" name="parentID" placeholder="Parent ID" value="<?php echo $parentID; ?>" required>
                                    <label for="parentID"><i class="fas fa-id-card me-2"></i>Parent ID</label>
                                    <?php if (!empty($parentID_err)): ?>
                                    <div class="invalid-feedback"><?php echo $parentID_err; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Demo ID: P12345</div>
                                </div>
                                
                                <div class="form-floating mb-4 position-relative">
                                    <input type="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="password" name="password" placeholder="Password" required>
                                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                                    <div class="password-toggle" id="passwordToggle">
                                        <i class="far fa-eye"></i>
                                    </div>
                                    <?php if (!empty($password_err)): ?>
                                    <div class="invalid-feedback"><?php echo $password_err; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Demo password: parent123</div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" value="remember-me" id="rememberMe" name="remember_me">
                                    <label class="form-check-label" for="rememberMe">
                                        Remember me
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                                
                                <div class="auth-links">
                                    <a href="forgot_password.php?type=parent" class="d-block mb-2"><i class="fas fa-key me-1"></i>Forgot Password?</a>
                                </div>
                            </form>
                        </div>
                        
                        <div class="login-footer">
                            <p class="mb-0">Don't have an account? <a href="parent_register.php" class="fw-bold">Register</a></p>
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
        document.addEventListener('DOMContentLoaded', function() {
            // Password visibility toggle
            const passwordToggle = document.getElementById('passwordToggle');
            const passwordInput = document.getElementById('password');
            
            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Toggle eye icon
                    const eyeIcon = this.querySelector('i');
                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
            
            // Form validation with animation
            const form = document.querySelector('form.needs-validation');
            
            if (form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        // Add shake animation to invalid fields
                        const invalidInputs = form.querySelectorAll(':invalid');
                        invalidInputs.forEach(input => {
                            input.parentElement.classList.add('shake');
                            setTimeout(() => {
                                input.parentElement.classList.remove('shake');
                            }, 650);
                        });
                    } else {
                        // Show loading state in button
                        const submitBtn = this.querySelector('button[type="submit"]');
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...';
                        submitBtn.disabled = true;
                        
                        // This timeout is only for demonstration purposes
                        // In a real app, the form submission would handle this
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 2000);
                    }
                    
                    form.classList.add('was-validated');
                });
            }
            
            // Create floating particles
            createParticles();
            
            // Toggle focus class for form fields for animation
            const formFloating = document.querySelectorAll('.form-floating');
            formFloating.forEach(item => {
                const input = item.querySelector('input');
                const label = item.querySelector('label');
                
                input.addEventListener('focus', () => {
                    label.style.color = 'var(--primary)';
                });
                
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        label.style.color = 'var(--secondary)';
                    }
                });
            });
        });
        
        // Create floating particles effect
        function createParticles() {
            const particlesContainer = document.querySelector('.particles');
            
            if (!particlesContainer) return;
            
            const particleCount = 20;
            
            for (let i = 0; i < particleCount; i++) {
                const particle = document.createElement('div');
                particle.classList.add('particle');
                
                // Random size between 5px and 20px
                const size = Math.random() * 15 + 5;
                particle.style.width = `${size}px`;
                particle.style.height = `${size}px`;
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                particle.style.left = `${posX}%`;
                particle.style.top = `${posY}%`;
                
                // Random animation duration between 15s and 30s
                const duration = Math.random() * 15 + 15;
                particle.style.animationDuration = `${duration}s`;
                
                // Random delay
                const delay = Math.random() * 5;
                particle.style.animationDelay = `${delay}s`;
                
                // Random opacity
                const opacity = Math.random() * 0.15 + 0.05;
                particle.style.opacity = opacity;
                
                particlesContainer.appendChild(particle);
            }
        }
    </script>
</body>
</html> 