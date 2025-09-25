<?php
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'school') {
    header("Location: school_dashboard.php");
    exit;
}

// Generate unique school ID
function generateSchoolID() {
    // Connect to database
    $db_config = [
        'host' => 'localhost',
        'username' => 'root',
        'password' => 'root',
        'database' => 'bookdress_db'
    ];
    
    try {
        $conn = new mysqli($db_config['host'], $db_config['username'], $db_config['password'], $db_config['database']);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Find the highest current school ID
        $sql = "SELECT MAX(CAST(SUBSTRING(school_id, 4) AS UNSIGNED)) as max_id FROM schools WHERE school_id LIKE 'SCH%'";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        
        // Get next ID number and pad with zeros
        $next_id = ($row['max_id'] ?? 0) + 1;
        $school_id = 'SCH' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
        
        $conn->close();
        return $school_id;
    } catch (Exception $e) {
        // If error, generate a fallback ID with timestamp
        return 'SCH' . substr(time(), -4);
    }
}

$school_id = generateSchoolID();

// Handle error messages
$error = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'id_exists':
            $error = 'School ID already exists. Please try a different one.';
            break;
        case 'email_exists':
            $error = 'Email address already registered. Please use a different email or try to login.';
            break;
        case 'invalid_data':
            $error = 'Invalid registration data. Please check your inputs and try again.';
            break;
        case 'db_error':
            $error = 'A database error occurred. Please try again later.';
            break;
        default:
            $error = 'An error occurred during registration. Please try again.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Register your school with Samridhi Book Dress to manage inventory, students and orders">
    <title>School Registration - Samridhi Book Dress</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/school_register.css">
    <link rel="preload" href="assets/pattern.svg" as="image">
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <style>
        .registration-code-feedback {
            display: none;
            margin-top: 5px;
        }
        .registration-code-valid {
            color: #198754;
        }
        .registration-code-invalid {
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <div class="auth-card">
                    <div class="text-center mb-4">
                        <a href="index.php">
                            <img src="assets/logo.svg" alt="Samridhi Book Dress" height="70" class="mb-3">
                        </a>
                        <h1 class="auth-title">School Registration</h1>
                        <p class="text-muted">Create an account for your school</p>
                    </div>
                    
                    <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> <?php echo $error; ?>
                    </div>
                    <?php endif; ?>
                    
                    <form id="schoolRegisterForm" action="api/school_register.php" method="post" class="needs-validation" novalidate>
                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="registrationCode" class="form-label">Registration Code <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control" id="registrationCode" name="registration_code" 
                                           placeholder="Enter registration code" required>
                                </div>
                                <div class="form-text">You need a valid registration code provided by the admin.</div>
                                <div class="invalid-feedback">Please enter a valid registration code.</div>
                                <div class="registration-code-feedback registration-code-valid">
                                    <i class="fas fa-check-circle me-1"></i> Registration code is valid!
                                </div>
                                <div class="registration-code-feedback registration-code-invalid">
                                    <i class="fas fa-times-circle me-1"></i> <span class="error-message">Invalid registration code.</span>
                                </div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="schoolName" class="form-label">School Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="schoolName" name="school_name" placeholder="Enter full school name" required>
                                <div class="invalid-feedback">Please enter your school name.</div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="schoolId" class="form-label">School ID</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-school"></i></span>
                                    <input type="text" class="form-control non-editable" id="schoolId" name="school_id" 
                                           value="<?php echo htmlspecialchars($school_id); ?>" readonly
                                           autocomplete="username">
                                </div>
                                <div class="form-text">Auto-generated unique school identifier.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter school email address" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid email address.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="Enter school phone number" required>
                                </div>
                                <div class="invalid-feedback">Please enter a valid phone number.</div>
                            </div>
                            
                            <div class="col-md-12">
                                <label for="address" class="form-label">School Address <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="address" name="address" 
                                          rows="2" placeholder="Enter complete school address" required></textarea>
                                <div class="invalid-feedback">Please enter your school address.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Create a password" required 
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="mt-2">
                                    <div class="progress" style="height: 5px;">
                                        <div id="passwordStrength" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small id="strengthText" class="form-text text-muted">Password strength</small>
                                </div>
                                <div class="invalid-feedback">Password must be at least 8 characters.</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" 
                                           placeholder="Confirm your password" required 
                                           autocomplete="new-password">
                                    <button class="btn btn-outline-secondary toggle-password" type="button" tabindex="-1">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="invalid-feedback">Passwords do not match.</div>
                            </div>
                            
                            <div class="col-md-12 mt-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                    <label class="form-check-label" for="terms">
                                        I agree to the <a href="terms_conditions.php" target="_blank">Terms & Conditions</a> and <a href="privacy_policy.php" target="_blank">Privacy Policy</a>
                                    </label>
                                    <div class="invalid-feedback">You must agree to our terms and conditions.</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-user-plus me-2"></i> Register School
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p>Already have an account? <a href="school_login.php" class="text-primary">Log in</a></p>
                        <a href="index.php" class="btn btn-outline-secondary btn-sm mt-2">
                            <i class="fas fa-home me-1"></i> Back to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loading-overlay" class="d-none">
        <div class="indian-spinner">
            <img src="assets/logo-icon.svg" alt="Loading" width="60">
        </div>
        <div class="loading-text mt-3">
            <h5>Registering School</h5>
            <div class="loading-dots">
                <span>.</span><span>.</span><span>.</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JavaScript -->
    <script src="js/school_register.js"></script>

    <!-- Custom JS -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Registration code verification
        const registrationCodeInput = document.getElementById('registrationCode');
        const validFeedback = document.querySelector('.registration-code-valid');
        const invalidFeedback = document.querySelector('.registration-code-invalid');
        const errorMessageSpan = document.querySelector('.error-message');
        let codeVerificationTimeout;
        
        registrationCodeInput.addEventListener('input', function() {
            const code = this.value.trim();
            
            // Clear both feedback elements
            validFeedback.style.display = 'none';
            invalidFeedback.style.display = 'none';
            
            // Clear any existing timeout
            if (codeVerificationTimeout) {
                clearTimeout(codeVerificationTimeout);
            }
            
            if (code.length < 4) {
                return;
            }
            
            // Set a timeout to verify after user stops typing
            codeVerificationTimeout = setTimeout(function() {
                // Create form data
                const formData = new FormData();
                formData.append('registration_code', code);
                
                // Send AJAX request
                fetch('api/verify_registration_code.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        validFeedback.style.display = 'block';
                        invalidFeedback.style.display = 'none';
                    } else {
                        validFeedback.style.display = 'none';
                        invalidFeedback.style.display = 'block';
                        errorMessageSpan.textContent = data.message;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    validFeedback.style.display = 'none';
                    invalidFeedback.style.display = 'block';
                    errorMessageSpan.textContent = 'Error checking registration code.';
                });
            }, 500);
        });
        
        // Password visibility toggle
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
</body>
</html> 