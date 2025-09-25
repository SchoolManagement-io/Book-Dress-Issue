<?php
// Start the session
session_start();

// Database connection
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => 'root', 
    'database' => 'bookdress_db'
];

// Initialize connection variable
$conn = null;

// Function to connect to database
function connectDB() {
    global $conn, $db_config;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(
                $db_config['host'],
                $db_config['username'],
                $db_config['password'],
                $db_config['database']
            );
            
            if ($conn->connect_error) {
                throw new Exception("Connection failed: " . $conn->connect_error);
            }
            
            // Set charset to ensure proper encoding
            $conn->set_charset("utf8mb4");
            
        } catch (Exception $e) {
            // Log the error instead of displaying it
            error_log("Database connection error: " . $e->getMessage());
            return false;
        }
    }
    
    return true;
}

// Function to handle contact form submission
function handleContactForm() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['contact_submit'])) {
        // Validate and sanitize inputs
        $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
        $subject = filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING);
        $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);
        
        // Basic validation
        if (empty($name) || empty($email) || empty($subject) || empty($message)) {
            return ['status' => 'error', 'message' => 'All fields are required.'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['status' => 'error', 'message' => 'Please enter a valid email address.'];
        }
        
        // Connect to database
        if (connectDB()) {
            global $conn;
            
            // Prepare and execute query
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt->bind_param("ssss", $name, $email, $subject, $message);
            
            if ($stmt->execute()) {
                return ['status' => 'success', 'message' => 'Your message has been sent. We will get back to you soon!'];
            } else {
                return ['status' => 'error', 'message' => 'Failed to send message. Please try again later.'];
            }
        } else {
            return ['status' => 'error', 'message' => 'Database connection error. Please try again later.'];
        }
    }
    
    return null;
}

// Handle form submission
$contact_result = handleContactForm();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Samridhi Book Dress Issue System - A complete solution for schools to manage books and uniforms">
  <title>Home - Samridhi Book Dress Issue System</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/index.css">
  <link rel="preload" href="assets/pattern.svg" as="image" />
  <link rel="preload" href="assets/paisley.svg" as="image" type="image/svg+xml" />
  <link rel="preload" href="images/img1.jpg" as="image" />
  <link rel="preload" href="images/img2.jpg" as="image" />
  <link rel="preload" href="images/img3.jpg" as="image" />
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <meta name="theme-color" content="#FF9933">
</head>
<body>

  <!-- 🌟 Navbar Start -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-primary px-3 sticky-top">
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
          <li class="nav-item"><a class="nav-link active" href="#" aria-current="page"><i class="fas fa-home me-1" aria-hidden="true"></i> Home</a></li>
          <li class="nav-item"><a class="nav-link" href="school_login.php"><i class="fas fa-school me-1" aria-hidden="true"></i> School</a></li>
          <li class="nav-item"><a class="nav-link" href="parent_login.php"><i class="fas fa-users me-1" aria-hidden="true"></i> Parent</a></li>
          <li class="nav-item"><a class="nav-link" href="admin_login.php"><i class="fas fa-user-shield me-1" aria-hidden="true"></i> Admin Panel</a></li>
        </ul>
      </div>
    </div>
  </nav>
  <!-- 🌟 Navbar End -->

  <!-- 🎠 Hero Carousel Start -->
  <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
    <div class="carousel-indicators">
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
      <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
    </div>
    <div class="carousel-inner">
      <div class="carousel-item active">
        <img src="images/img1.jpg" class="d-block w-100" alt="Students in classroom with books" width="1600" height="600" fetchpriority="high" />
        <div class="carousel-caption">
          <h1 class="carousel-title animate-fade-in">Welcome to Samridhi</h1>
          <p class="carousel-subtitle animate-fade-in">Making book and uniform distribution simple and efficient</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="images/img2.jpg" class="d-block w-100" alt="School uniforms arranged neatly" width="1600" height="600" loading="lazy" />
        <div class="carousel-caption">
          <h2 class="carousel-title animate-fade-in">One-Stop Solution</h2>
          <p class="carousel-subtitle animate-fade-in">For all your educational resource needs</p>
        </div>
      </div>
      <div class="carousel-item">
        <img src="images/img3.jpg" class="d-block w-100" alt="Digital education concept" width="1600" height="600" loading="lazy" />
        <div class="carousel-caption">
          <h2 class="carousel-title animate-fade-in">Modern Management</h2>
          <p class="carousel-subtitle animate-fade-in">Streamline your administrative processes</p>
        </div>
      </div>
    </div>
    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Previous slide">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Next slide">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
    </button>
  </div>
  <!-- 🎠 Hero Carousel End -->

  <!-- 🔑 Login Section Start -->
  <section class="login-section py-5" id="login-section">
    <div class="container">
      <h2 class="section-title text-center mb-5">
        <span class="indian-title-decor"></span>
        Login to Get Started
        <span class="indian-title-decor"></span>
      </h2>
      
      <div class="row justify-content-center">
        <div class="col-md-5 mb-4 mb-md-0">
          <div class="login-card school-card animate-on-scroll">
            <div class="login-icon">
              <i class="fas fa-school" aria-hidden="true"></i>
            </div>
            <h3>School Login</h3>
            <p>Access your school's admin dashboard to manage inventory, process orders, and generate reports</p>
            <a href="school_login.php" class="btn-secondary">
              <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>School Login
            </a>
          </div>
        </div>
        
        <div class="col-md-5">
          <div class="login-card parent-card animate-on-scroll" data-delay="200">
            <div class="login-icon">
              <i class="fas fa-users" aria-hidden="true"></i>
            </div>
            <h3>Parent Login</h3>
            <p>Order books and uniforms for your child from your school's inventory with easy tracking</p>
            <a href="parent_login.php" class="btn-secondary">
              <i class="fas fa-sign-in-alt me-2" aria-hidden="true"></i>Parent Login
            </a>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- 🔑 Login Section End -->

  <!-- 💡 Features Section Start -->
  <section class="features-section py-5 bg-light content-visibility-auto">
    <div class="container">
      <h2 class="section-title text-center mb-5">
        <span class="indian-title-decor"></span>
        Key Features
        <span class="indian-title-decor"></span>
      </h2>
      
      <div class="row">
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll">
            <div class="feature-icon">
              <i class="fas fa-tasks" aria-hidden="true"></i>
            </div>
            <h3>Inventory Management</h3>
            <p>Track books and uniforms inventory with ease, receive low stock alerts, and manage suppliers</p>
          </div>
        </div>
        
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll" data-delay="100">
            <div class="feature-icon">
              <i class="fas fa-shopping-cart" aria-hidden="true"></i>
            </div>
            <h3>Order Processing</h3>
            <p>Simple order placement for parents, and efficient processing workflow for school administrators</p>
          </div>
        </div>
        
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll" data-delay="200">
            <div class="feature-icon">
              <i class="fas fa-chart-bar" aria-hidden="true"></i>
            </div>
            <h3>Reporting & Analytics</h3>
            <p>Generate comprehensive reports on inventory, sales, and distribution to optimize operations</p>
          </div>
        </div>
      </div>
      
      <div class="row pt-4">
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll" data-delay="150">
            <div class="feature-icon">
              <i class="fas fa-bell" aria-hidden="true"></i>
            </div>
            <h3>Notifications</h3>
            <p>Automated notifications for order status, availability, and upcoming distributions</p>
          </div>
        </div>
        
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll" data-delay="250">
            <div class="feature-icon">
              <i class="fas fa-mobile-alt" aria-hidden="true"></i>
            </div>
            <h3>Mobile Responsive</h3>
            <p>Access the system from any device with our fully responsive design</p>
          </div>
        </div>
        
        <div class="col-md-4 mb-4">
          <div class="feature-card animate-on-scroll" data-delay="350">
            <div class="feature-icon">
              <i class="fas fa-shield-alt" aria-hidden="true"></i>
            </div>
            <h3>Secure & Reliable</h3>
            <p>Data security and privacy with role-based access controls and encryption</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- 💡 Features Section End -->

  <!-- 📚 How It Works Section Start -->
  <section class="how-it-works-section py-5 content-visibility-auto">
    <div class="container">
      <h2 class="section-title text-center mb-5">
        <span class="indian-title-decor"></span>
        How It Works
        <span class="indian-title-decor"></span>
      </h2>
      
      <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="step-card animate-on-scroll">
            <div class="step-number">1</div>
            <div class="step-icon">
              <i class="fas fa-user-plus" aria-hidden="true"></i>
            </div>
            <h3>Register</h3>
            <p>Schools register on the platform and set up their inventory</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="step-card animate-on-scroll" data-delay="100">
            <div class="step-number">2</div>
            <div class="step-icon">
              <i class="fas fa-box-open" aria-hidden="true"></i>
            </div>
            <h3>Stock Management</h3>
            <p>Schools add books and uniforms to their inventory system</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="step-card animate-on-scroll" data-delay="200">
            <div class="step-number">3</div>
            <div class="step-icon">
              <i class="fas fa-clipboard-list" aria-hidden="true"></i>
            </div>
            <h3>Parent Orders</h3>
            <p>Parents login and place orders for their children's needs</p>
          </div>
        </div>
        
        <div class="col-lg-3 col-md-6 mb-4">
          <div class="step-card animate-on-scroll" data-delay="300">
            <div class="step-number">4</div>
            <div class="step-icon">
              <i class="fas fa-check-circle" aria-hidden="true"></i>
            </div>
            <h3>Distribution</h3>
            <p>Schools process orders and distribute items to students</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- 📚 How It Works Section End -->

  <!-- 🏫 Project Info Section Start -->
  <section class="project-info-section py-5 bg-light content-visibility-auto">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6 mb-4 mb-lg-0">
          <div class="project-info-image animate-on-scroll">
            <img src="images/purpose3.jpg" alt="School students receiving books" class="img-fluid rounded shadow" width="600" height="400" loading="lazy" />
          </div>
        </div>
        
        <div class="col-lg-6">
          <div class="project-info-content animate-on-scroll" data-delay="100">
            <h2 class="section-title mb-4">About Our Project</h2>
            <p class="lead">Samridhi Book Dress Issue System is designed to streamline the distribution of educational resources in schools across India.</p>
            <p>Our system helps schools manage their book and uniform inventory efficiently, while providing parents with a convenient way to procure these items for their children.</p>
            <p>With features tailored to the Indian educational context, our platform addresses the unique challenges faced by schools in managing and distributing educational resources.</p>
            <div class="mt-4">
              <a href="#contact-section" class="btn-primary me-3">
                <i class="fas fa-info-circle me-2" aria-hidden="true"></i>Contact Us
              </a>
              <a href="school_register.php" class="btn-secondary">
                <i class="fas fa-user-plus me-2" aria-hidden="true"></i>Register School
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- 🏫 Project Info Section End -->

  <!-- 📞 Contact Section Start -->
  <section class="contact-section py-5 content-visibility-auto" id="contact-section">
    <div class="container">
      <h2 class="section-title text-center mb-5">
        <span class="indian-title-decor"></span>
        Contact Us
        <span class="indian-title-decor"></span>
      </h2>
      
      <?php if ($contact_result !== null): ?>
        <div class="row justify-content-center mb-4">
          <div class="col-lg-8">
            <div class="alert alert-<?php echo $contact_result['status'] === 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
              <?php echo $contact_result['message']; ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          </div>
        </div>
      <?php endif; ?>
      
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="contact-card animate-on-scroll">
            <form id="contact-form" class="needs-validation" method="post" action="#contact-section" novalidate>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="name" class="form-label">Your Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required autocomplete="name" />
                  <div class="invalid-feedback">Please provide your name.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                  <label for="email" class="form-label">Email Address</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required autocomplete="email" />
                  <div class="invalid-feedback">Please provide a valid email address.</div>
                </div>
              </div>
              
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" placeholder="Enter subject" required />
                <div class="invalid-feedback">Please provide a subject.</div>
              </div>
              
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="5" placeholder="Enter your message" required></textarea>
                <div class="invalid-feedback">Please provide a message.</div>
              </div>
              
              <button type="submit" name="contact_submit" class="btn-primary">
                <i class="fas fa-paper-plane me-2" aria-hidden="true"></i>Send Message
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- 📞 Contact Section End -->

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
            <li><a href="school_register.php" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>School Registration</a></li>
            <li><a href="parent_login.php" class="text-white text-decoration-none"><i class="fas fa-angle-right me-2" aria-hidden="true"></i>Parent Login</a></li>
          </ul>
        </div>
        <div class="col-md-4 mb-4">
          <h2 class="h5">Connect With Us</h2>
          <div class="mt-4">
            <h3 class="h6">&copy; <?php echo date('Y'); ?> Samridhi Book Dress</h3>
            <p>All rights reserved</p>
          </div>
        </div>
      </div>
    </div>
  </footer>
  <!-- 📞 Footer End -->

  <!-- Include JavaScript files -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="js/index.js"></script>
  
  <!-- Form validation script -->
  <script>
  (function() {
    'use strict';
    
    // Fetch all forms with needs-validation class
    const forms = document.querySelectorAll('.needs-validation');
    
    // Loop over them and prevent submission
    Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        
        form.classList.add('was-validated');
      }, false);
    });
  })();
  </script>
</body>
</html> 