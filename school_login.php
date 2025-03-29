<?php
// Initialize session
session_start();

// Initialize variables
$school_id = "";
$password = "";
$error_message = "";

// Process form data when form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $school_id = trim($_POST["school_id"]);
    $password = $_POST["password"];
    
    // Basic validation
    if (empty($school_id) || empty($password)) {
        $error_message = "Please enter both School ID and password";
    } else {
        // In a real application, you would validate against a database
        // This is a simple mock validation for demonstration purposes
        if ($school_id === "SCH123" && $password === "admin123") {
            // Set session variables
            $_SESSION["school_logged_in"] = true;
            $_SESSION["school_id"] = $school_id;
            $_SESSION["school_name"] = "Springfield Elementary School"; // Would be fetched from DB in real app
            
            // Redirect to dashboard
            header("Location: school_dashboard.php");
            exit();
        } else {
            $error_message = "Invalid School ID or password";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Login - School Inventory Management</title>
    
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
            background-image: url('img/background_pattern.svg');
            background-size: 100px;
            position: relative;
            overflow: hidden;
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
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border: none;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.3);
        }
        
        .btn-login::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: -100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
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
        
        .school-svg {
            max-width: 70px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .school-svg:hover {
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
        
        .auth-links a::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: var(--primary);
            border-radius: 30px;
            z-index: -1;
            opacity: 0;
            transform: scale(0.8);
            transition: all 0.3s ease;
        }
        
        .auth-links a:hover::before {
            opacity: 0.1;
            transform: scale(1.2);
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
        
        .form-error {
            color: var(--danger);
            font-size: 0.875rem;
            margin-top: -1rem;
            margin-bottom: 1rem;
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
        
        /* 3D building hover effect */
        .school-building {
            position: relative;
            transition: all 0.5s ease;
            transform-style: preserve-3d;
            perspective: 500px;
        }
        
        .school-building:hover {
            transform: rotateY(10deg) rotateX(5deg);
        }
        
        .school-building:hover .flag {
            transform: rotate(-5deg) translateY(-2px);
        }
        
        .school-building:hover .window {
            fill: #fff8e1;
        }
        
        .flag {
            transform-origin: bottom;
            transition: all 0.8s ease;
        }
        
        .window {
            transition: fill 0.5s ease;
        }
        
        /* Floating shapes effect */
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: -1;
        }
        
        .floating-shape {
            position: absolute;
            opacity: 0.1;
            animation: float linear infinite;
        }
        
        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
        }
        
        /* Animation for form focus */
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
            animation: pulse-border 1.5s infinite;
        }
        
        @keyframes pulse-border {
            0% { box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
            50% { box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.15); }
            100% { box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25); }
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
                        <a class="nav-link active" aria-current="page" href="school_login.php">School Login</a>
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
        <!-- Floating shapes -->
        <div class="floating-shapes"></div>
        
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="login-card">
                        <div class="login-header text-center">
                            <!-- School SVG Icon -->
                            <svg class="school-svg school-building" viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg">
                                <path d="M50,10L10,30v5h80v-5L50,10z" fill="white"/>
                                <path d="M25,40v30h50V40H25z" fill="white"/>
                                <path d="M15,40v35h70V40H15z M30,70H20V45h10V70z M50,70H35V45h15V70z M65,70H55V45h10V70z M80,70H70V45h10V70z" fill="white" opacity="0.85"/>
                                <path d="M10,80h80v10H10V80z" fill="white"/>
                                <!-- Windows -->
                                <rect x="22" y="50" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="22" y="60" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="37" y="50" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="37" y="60" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="57" y="50" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="57" y="60" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="72" y="50" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <rect x="72" y="60" width="6" height="6" fill="#e7f5ff" class="window"/>
                                <!-- Door -->
                                <rect x="42.5" y="60" width="15" height="20" fill="#6c757d"/>
                                <circle cx="45" cy="70" r="1" fill="#ffc107"/>
                                <!-- Flag -->
                                <line x1="85" y1="15" x2="85" y2="35" stroke="white" stroke-width="1"/>
                                <rect x="85" y="15" width="10" height="5" fill="#dc3545" class="flag"/>
                            </svg>
                            <h2 class="mb-2">School Login</h2>
                            <p class="mb-0">Access your inventory management dashboard</p>
                            <div class="login-header-decoration">
                                <i class="fas fa-school"></i>
                            </div>
                        </div>
                        
                        <div class="login-body">
                            <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__shakeX" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo $error_message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php endif; ?>
                            
                            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="needs-validation" novalidate>
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control <?php echo (!empty($school_id_err)) ? 'is-invalid' : ''; ?>" id="schoolId" name="school_id" placeholder="School ID" value="<?php echo $school_id; ?>" required>
                                    <label for="schoolId"><i class="fas fa-id-card me-2"></i>School ID</label>
                                    <?php if (!empty($school_id_err)): ?>
                                    <div class="invalid-feedback"><?php echo $school_id_err; ?></div>
                                    <?php endif; ?>
                                    <div class="form-text">Demo ID: SCH123</div>
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
                                    <div class="form-text">Demo password: admin123</div>
                                </div>
                                
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" value="remember-me" id="rememberMe" name="remember_me">
                                    <label class="form-check-label" for="rememberMe">
                                        <span class="d-flex align-items-center"><i class="fas fa-user-clock me-2 text-primary"></i>Remember me</span>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-login animate__animated animate__pulse">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login
                                </button>
                                
                                <div class="auth-links">
                                    <a href="forgot_password.php?type=school" class="d-block mb-2"><i class="fas fa-key me-1"></i>Forgot Password?</a>
                                </div>
                            </form>
                        </div>
                        
                        <div class="login-footer">
                            <p class="mb-0">New school? <a href="school_register.php" class="fw-bold">Click here</a> to register</p>
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
            
            // Create floating shapes
            createFloatingShapes();
            
            // Add window lighting effect for school building
            const windows = document.querySelectorAll('.window');
            
            // Initial random lighting
            windows.forEach(window => {
                if (Math.random() > 0.5) {
                    window.setAttribute('fill', '#fff8e1');
                }
            });
            
            // Blinking effect
            setInterval(() => {
                windows.forEach(window => {
                    if (Math.random() > 0.8) {
                        const currentFill = window.getAttribute('fill');
                        const newFill = currentFill === '#fff8e1' ? '#e7f5ff' : '#fff8e1';
                        window.setAttribute('fill', newFill);
                        
                        // Revert back after a random time
                        setTimeout(() => {
                            if (Math.random() > 0.5) {
                                window.setAttribute('fill', currentFill);
                            }
                        }, Math.random() * 2000 + 1000);
                    }
                });
            }, 3000);
            
            // Subtle rotation animation for building
            const schoolBuilding = document.querySelector('.school-building');
            if (schoolBuilding) {
                setInterval(() => {
                    schoolBuilding.style.transform = 'rotateY(2deg) rotateX(1deg)';
                    setTimeout(() => {
                        schoolBuilding.style.transform = 'rotateY(-1deg) rotateX(-0.5deg)';
                    }, 2000);
                    setTimeout(() => {
                        schoolBuilding.style.transform = 'rotateY(0deg) rotateX(0deg)';
                    }, 4000);
                }, 8000);
            }
            
            // Flag waving effect
            const flag = document.querySelector('.flag');
            if (flag) {
                setInterval(() => {
                    flag.style.transform = 'rotate(-3deg)';
                    setTimeout(() => {
                        flag.style.transform = 'rotate(0deg)';
                    }, 1000);
                }, 5000);
            }
            
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
        
        // Create floating shapes effect
        function createFloatingShapes() {
            const shapesContainer = document.querySelector('.floating-shapes');
            
            if (!shapesContainer) return;
            
            const shapeCount = 15;
            const shapes = ['square', 'circle', 'triangle'];
            
            for (let i = 0; i < shapeCount; i++) {
                const shape = document.createElement('div');
                shape.classList.add('floating-shape');
                
                // Random shape type
                const shapeType = shapes[Math.floor(Math.random() * shapes.length)];
                
                // Random size between 10px and 30px
                const size = Math.random() * 20 + 10;
                shape.style.width = `${size}px`;
                shape.style.height = `${size}px`;
                
                // Set shape styles based on type
                if (shapeType === 'circle') {
                    shape.style.borderRadius = '50%';
                } else if (shapeType === 'triangle') {
                    shape.style.width = '0';
                    shape.style.height = '0';
                    shape.style.borderLeft = `${size/2}px solid transparent`;
                    shape.style.borderRight = `${size/2}px solid transparent`;
                    shape.style.borderBottom = `${size}px solid var(--primary)`;
                    shape.style.background = 'transparent';
                } else {
                    shape.style.borderRadius = '3px';
                }
                
                if (shapeType !== 'triangle') {
                    shape.style.backgroundColor = 'var(--primary)';
                }
                
                // Random position
                const posX = Math.random() * 100;
                const posY = Math.random() * 100;
                shape.style.left = `${posX}%`;
                shape.style.top = `${posY}%`;
                
                // Random animation duration between 15s and 30s
                const duration = Math.random() * 15 + 15;
                shape.style.animationDuration = `${duration}s`;
                
                // Random delay
                const delay = Math.random() * 5;
                shape.style.animationDelay = `${delay}s`;
                
                // Random rotation
                const rotation = Math.random() * 360;
                shape.style.transform = `rotate(${rotation}deg)`;
                
                shapesContainer.appendChild(shape);
            }
        }
    </script>
</body>
</html> 