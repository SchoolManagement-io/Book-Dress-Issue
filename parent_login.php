<?php
// Start session
session_start();

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['parent_id'])) {
    header("Location: parent_dashboard.php");
    exit();
}

// Handle error and success messages
$login_error = isset($_GET['error']) ? true : false;
$error_message = '';

if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'invalid_credentials':
            $error_message = 'Invalid Parent ID or password. Please try again.';
            break;
        case 'missing_fields':
            $error_message = 'Please enter both Parent ID and password.';
            break;
        default:
            $error_message = 'An error occurred. Please try again.';
    }
}

$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'logout':
            $success_message = 'You have been logged out successfully.';
            break;
        default:
            $success_message = '';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Parent login for Samridhi Book Dress Issue System - Order books and uniforms for your child">
  <title>Parent Login - Samridhi Book Dress</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="preload" href="assets/pattern.svg" as="image" />
  <link rel="preload" href="assets/rangoli.svg" as="image" />
  <link rel="stylesheet" href="css/parent_login.css" />
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body class="bg-light">

  <!-- 🌟 Navbar Start -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3">
    <div class="container">
      <a class="navbar-brand d-flex align-items-center" href="index.php" aria-label="Samridhi Book Dress home">
        <img src="assets/logo.svg" alt="Samridhi Logo" height="40" class="me-2" width="40" />
        <strong>Samridhi Book Dress</strong>
      </a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-home me-1" aria-hidden="true"></i> Home</a></li>
          <li class="nav-item"><a class="nav-link" href="school_login.php"><i class="fas fa-school me-1" aria-hidden="true"></i> School</a></li>
          <li class="nav-item"><a class="nav-link active" href="parent_login.php" aria-current="page"><i class="fas fa-users me-1" aria-hidden="true"></i> Parent</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- 🌟 Navbar End -->

  <!-- 🔐 Login Form Start -->
  <main class="login-wrapper content-visibility-auto">
    <div class="rangoli-decoration top-left"></div>
    <div class="rangoli-decoration bottom-right"></div>
    
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-6">
          <div class="auth-card">
            <div class="text-center mb-4">
              <img src="assets/logo.svg" alt="School Samridhi Logo" width="80" height="80" class="mb-3">
              <h2 class="auth-title">Parent Login</h2>
              <p class="text-muted">Access your parent dashboard</p>
            </div>
            
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
            
            <form id="loginForm" method="POST" action="api/parent_login.php">
              <div class="mb-3">
                <label for="parent_id" class="form-label">Parent ID</label>
                <input type="text" class="form-control" id="parent_id" name="parent_id" required autocomplete="username">
                <div class="invalid-feedback">Please enter your Parent ID</div>
              </div>
              
              <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                  <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                  <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
                <div class="invalid-feedback">
                  Please enter your password
                </div>
              </div>
              
              <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                <label class="form-check-label" for="remember_me">Remember me</label>
              </div>
              
              <div class="d-grid gap-2 mb-3">
                <button type="submit" class="btn btn-primary btn-lg">Login</button>
              </div>
              
              <div class="d-flex justify-content-center">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
              </div>
              
              <div class="text-center mt-4">
                <a href="school_login.php" class="btn btn-outline-secondary">
                  <i class="fas fa-school me-2"></i>School Login
                </a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!-- 🔐 Login Form End -->

  <!-- 📞 Footer Start -->
  <footer class="bg-dark text-white pt-5 pb-3">
    <div class="container">
      <div class="row text-center text-md-start">
        <div class="col-md-4 mb-4">
          <h2 class="h5">Contact Us</h2>
          <p><i class="fas fa-envelope me-2" aria-hidden="true"></i>support@samridhibookdress.com</p>
          <p><i class="fas fa-phone me-2" aria-hidden="true"></i>+91-9876543210</p>
          <p><i class="fas fa-map-marker-alt me-2" aria-hidden="true"></i>123 Education Street, New Delhi</p>
        </div>
        <div class="col-md-4 mb-4">
          <h2 class="h5">Quick Links</h2>
          <ul class="list-unstyled">
            <li><a href="index.php" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>Home</a></li>
            <li><a href="school_login.php" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>School Login</a></li>
            <li><a href="parent_login.php" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>Parent Login</a></li>
            <li><a href="#" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>Parent Registration</a></li>
          </ul>
        </div>
        <div class="col-md-4 mb-4">
          <h2 class="h5">Connect With Us</h2>
          <div class="mt-4">
            <h3 class="h6">© <?php echo date('Y'); ?> Samridhi Book Dress</h3>
            <p>All rights reserved</p>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <!-- 📞 Footer End -->

  <!-- Forgot Password Modal -->
  <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Forgot Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p>Please contact your school administrator to reset your password. The school admin can reset your password from their dashboard.</p>
          
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> For security reasons, parents cannot reset their passwords directly.
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Understood</button>
        </div>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    
    togglePassword.addEventListener('click', function() {
      const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
      passwordInput.setAttribute('type', type);
      
      // Toggle eye icon
      this.querySelector('i').classList.toggle('fa-eye');
      this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Form validation with AJAX submission
    const loginForm = document.getElementById('loginForm');
    
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
          const errorToast = document.createElement('div');
          errorToast.className = 'position-fixed bottom-0 end-0 p-3';
          errorToast.style.zIndex = '5';
          errorToast.innerHTML = `
            <div class="toast align-items-center text-white bg-danger border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
              <div class="d-flex">
                <div class="toast-body">
                  <i class="fas fa-exclamation-circle me-2"></i> ${data.message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
              </div>
            </div>
          `;
          
          document.body.appendChild(errorToast);
          
          // Remove toast after 5 seconds
          setTimeout(() => {
            errorToast.remove();
          }, 5000);
        }
      })
      .catch(error => {
        console.error('Error:', error);
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