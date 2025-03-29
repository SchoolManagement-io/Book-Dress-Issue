<?php
// Initialize session if needed
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - School Inventory Management</title>
    
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
        /* About page specific styles */
        .about-hero {
            position: relative;
            padding: 5rem 0;
            overflow: hidden;
            background-image: url('img/background_pattern.svg');
            background-size: 100px;
        }
        
        .about-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            z-index: 0;
        }
        
        .about-content {
            position: relative;
            z-index: 1;
        }
        
        .about-title {
            position: relative;
            display: inline-block;
            margin-bottom: 2rem;
        }
        
        .about-title::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: -10px;
            width: 80px;
            height: 4px;
            background-color: var(--primary);
            border-radius: 2px;
        }
        
        .feature-card {
            transition: all 0.3s ease;
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            height: 100%;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .feature-card-header {
            position: relative;
            padding: 1.5rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            overflow: hidden;
        }
        
        .feature-card-header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            transform: rotate(30deg);
            z-index: 0;
        }
        
        .feature-icon {
            position: relative;
            z-index: 1;
            width: 70px;
            height: 70px;
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            font-size: 2rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover .feature-icon {
            transform: rotateY(180deg);
            background-color: white;
            color: var(--primary);
        }
        
        .feature-card-body {
            padding: 1.5rem;
        }
        
        .feature-list-item {
            padding: 0.5rem 0;
            border-bottom: 1px dashed var(--gray-200);
            transition: all 0.3s ease;
        }
        
        .feature-list-item:last-child {
            border-bottom: none;
        }
        
        .feature-list-item:hover {
            transform: translateX(5px);
        }
        
        .feature-list-icon {
            background-color: var(--primary-light);
            color: var(--primary);
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 0.5rem;
            font-size: 0.75rem;
        }
        
        .mission-section {
            position: relative;
            background-color: var(--primary-light);
            padding: 5rem 0;
            overflow: hidden;
        }
        
        .mission-bg-circle {
            position: absolute;
            border-radius: 50%;
            background-color: var(--primary);
            opacity: 0.05;
        }
        
        .mission-bg-circle-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            left: -150px;
        }
        
        .mission-bg-circle-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            right: -100px;
        }
        
        .mission-card {
            background-color: white;
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            height: 100%;
            transition: all 0.3s ease;
        }
        
        .mission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }
        
        .mission-icon {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-light);
            color: var(--primary);
            font-size: 2.5rem;
            border-radius: 50%;
            margin: 0 auto 1.5rem;
            transition: all 0.3s ease;
        }
        
        .mission-card:hover .mission-icon {
            transform: scale(1.1);
            background-color: var(--primary);
            color: white;
        }
        
        .team-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        
        .team-card-img {
            position: relative;
            overflow: hidden;
        }
        
        .team-card-img img {
            transition: all 0.5s ease;
        }
        
        .team-card:hover .team-card-img img {
            transform: scale(1.1);
        }
        
        .team-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.7) 0%, transparent 100%);
            padding: 1.5rem;
            transform: translateY(20%);
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .team-card:hover .team-card-overlay {
            transform: translateY(0);
            opacity: 1;
        }
        
        .team-social-links {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: center;
        }
        
        .team-social-links li {
            margin: 0 0.5rem;
        }
        
        .team-social-links a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            transition: all 0.3s ease;
        }
        
        .team-social-links a:hover {
            transform: translateY(-3px);
            background-color: var(--primary-dark);
        }
        
        .team-card-body {
            padding: 1.5rem;
            text-align: center;
        }
        
        .team-name {
            margin-bottom: 0.25rem;
        }
        
        .team-position {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 1rem;
        }
        
        .contact-section {
            position: relative;
            background-color: white;
            padding: 5rem 0;
        }
        
        .contact-card {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            border-radius: var(--border-radius-lg);
            transition: all 0.3s ease;
        }
        
        .contact-item:hover {
            background-color: var(--primary-light);
            transform: translateX(5px);
        }
        
        .contact-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background-color: var(--primary-light);
            color: var(--primary);
            font-size: 1.5rem;
            border-radius: 50%;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        .contact-item:hover .contact-icon {
            background-color: var(--primary);
            color: white;
        }
        
        .contact-info h5 {
            margin-bottom: 0.25rem;
        }
        
        .contact-info p {
            margin-bottom: 0;
            color: var(--gray-600);
        }
        
        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            background-color: var(--primary-light);
            color: var(--primary);
            border-radius: 50%;
            font-size: 1.25rem;
            margin: 0 0.5rem;
            transition: all 0.3s ease;
        }
        
        .social-link:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-5px);
        }
        
        /* Animation for page content */
        .animate-fade-in-up {
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
        
        /* Animation for stats counter */
        .stat-counter {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }
        
        /* Media queries */
        @media (max-width: 991.98px) {
            .about-hero {
                padding: 3rem 0;
            }
            
            .mission-section, .contact-section {
                padding: 3rem 0;
            }
            
            .feature-card-header {
                padding: 1rem;
            }
            
            .feature-icon {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }
            
            .contact-svg {
                max-width: 80%;
                margin: 0 auto 2rem;
            }
        }
        
        @media (max-width: 767.98px) {
            .about-title {
                font-size: 2rem;
            }
            
            .mission-card, .team-card {
                margin-bottom: 2rem;
            }
            
            .stat-counter {
                font-size: 2rem;
            }
            
            .mission-icon {
                width: 60px;
                height: 60px;
                font-size: 1.75rem;
            }
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
                        <a class="nav-link active" aria-current="page" href="about.php">About Us</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <!-- About Hero Section -->
        <section class="about-hero">
            <div class="container about-content">
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <h1 class="about-title display-4 fw-bold animate__animated animate__fadeInUp">About Our School Inventory System</h1>
                        <p class="lead mb-4 animate__animated animate__fadeInUp animate__delay-1s">Our comprehensive platform connects parents and schools to streamline the management of educational supplies, ensuring every student has what they need to succeed.</p>
                        <div class="d-flex flex-wrap gap-2 animate__animated animate__fadeInUp animate__delay-2s">
                            <a href="#features" class="btn btn-primary btn-lg">Explore Features</a>
                            <a href="#contact" class="btn btn-outline-primary btn-lg">Contact Us</a>
                        </div>
                    </div>
                    <div class="col-lg-6 animate__animated animate__fadeInRight animate__delay-1s">
                        <img src="img/about_illustration.svg" alt="School Inventory Team" class="img-fluid">
                    </div>
                </div>
            </div>
            
            <!-- Floating background elements -->
            <div class="floating-background" data-effect-type="shapes" data-count="10"></div>
        </section>

        <!-- Stats Section -->
        <section class="py-5 bg-light">
            <div class="container">
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-4 mb-md-0">
                        <div class="stat-counter" data-target="25">0</div>
                        <p class="mb-0 text-uppercase fw-bold small">Schools</p>
                    </div>
                    <div class="col-md-3 col-6 mb-4 mb-md-0">
                        <div class="stat-counter" data-target="5000">0</div>
                        <p class="mb-0 text-uppercase fw-bold small">Happy Parents</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-counter" data-target="10000">0</div>
                        <p class="mb-0 text-uppercase fw-bold small">Products</p>
                    </div>
                    <div class="col-md-3 col-6">
                        <div class="stat-counter" data-target="99">0</div>
                        <p class="mb-0 text-uppercase fw-bold small">Satisfaction Rate %</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8 text-center">
                        <h2 class="fw-bold mb-3">Powerful Features</h2>
                        <p class="lead">Our platform offers a variety of features designed to make inventory management and ordering simple and efficient for both schools and parents.</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-4 animate-fade-in-up">
                        <div class="feature-card">
                            <div class="feature-card-header text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <h3 class="h4 text-white mb-0">For Parents</h3>
                            </div>
                            <div class="feature-card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Easy online ordering for books and uniforms</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Secure login with password recovery</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Track order history and manage deliveries</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Simple checkout with multiple payment options</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Email notifications for order updates</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-4 animate-fade-in-up" style="animation-delay: 0.2s;">
                        <div class="feature-card">
                            <div class="feature-card-header text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-school"></i>
                                </div>
                                <h3 class="h4 text-white mb-0">For Schools</h3>
                            </div>
                            <div class="feature-card-body">
                                <ul class="list-unstyled mb-0">
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Comprehensive inventory management system</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Real-time order tracking and notifications</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Automated low-stock alerts and reordering</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Detailed analytics and reporting tools</span>
                                    </li>
                                    <li class="feature-list-item d-flex align-items-center">
                                        <span class="feature-list-icon">
                                            <i class="fas fa-check"></i>
                                        </span>
                                        <span>Customizable categories and product listings</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Mission Section -->
        <section id="mission" class="mission-section">
            <div class="container">
                <!-- Decorative circles -->
                <div class="mission-bg-circle mission-bg-circle-1"></div>
                <div class="mission-bg-circle mission-bg-circle-2"></div>
                
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8 text-center">
                        <h2 class="fw-bold mb-3">Our Mission & Vision</h2>
                        <p class="lead">We're dedicated to streamlining school inventory management, making it convenient for parents and efficient for schools.</p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <img src="img/mission.svg" alt="Our Mission" class="img-fluid mb-4 mb-lg-0 animate__animated animate__fadeInLeft">
                    </div>
                    <div class="col-lg-6">
                        <div class="row h-100">
                            <div class="col-md-6 mb-4">
                                <div class="mission-card">
                                    <div class="mission-icon">
                                        <i class="fas fa-bullseye"></i>
                                    </div>
                                    <h3 class="h5 text-center mb-3">Our Mission</h3>
                                    <p class="mb-0">To create a seamless inventory management system that connects schools and parents, ensuring every student has the resources they need.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="mission-card">
                                    <div class="mission-icon">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h3 class="h5 text-center mb-3">Our Vision</h3>
                                    <p class="mb-0">To be the leading platform for educational resource management, empowering schools with technology-driven solutions.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="mission-card">
                                    <div class="mission-icon">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <h3 class="h5 text-center mb-3">Our Values</h3>
                                    <p class="mb-0">Integrity, innovation, and exceptional service are at the core of everything we do, ensuring trust and reliability.</p>
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <div class="mission-card">
                                    <div class="mission-icon">
                                        <i class="fas fa-rocket"></i>
                                    </div>
                                    <h3 class="h5 text-center mb-3">Our Goal</h3>
                                    <p class="mb-0">To reduce administrative burden for schools and simplify the supply acquisition process for parents and guardians.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Contact Section -->
        <section id="contact" class="contact-section">
            <div class="container">
                <div class="row justify-content-center mb-5">
                    <div class="col-lg-8 text-center">
                        <h2 class="fw-bold mb-3">Get In Touch</h2>
                        <p class="lead">Have questions about our system? Our team is here to help you with any inquiries you might have.</p>
                    </div>
                </div>
                
                <div class="row align-items-center">
                    <div class="col-lg-6 mb-5 mb-lg-0">
                        <img src="img/contact.svg" alt="Contact Us" class="img-fluid contact-svg animate__animated animate__fadeInLeft">
                    </div>
                    <div class="col-lg-6 animate__animated animate__fadeInRight">
                        <div class="contact-card p-4">
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="contact-info">
                                    <h5>Email</h5>
                                    <p>support@schoolinventory.example.com</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-phone-alt"></i>
                                </div>
                                <div class="contact-info">
                                    <h5>Phone</h5>
                                    <p>+91-123-456-7890</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="contact-icon">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="contact-info">
                                    <h5>Address</h5>
                                    <p>123 Education Street, Tech City, 400001</p>
                                </div>
                            </div>
                            
                            <div class="text-center mt-4">
                                <h5 class="mb-3">Follow Us</h5>
                                <a href="#" class="social-link" aria-label="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-link" aria-label="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link" aria-label="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                                <a href="#" class="social-link" aria-label="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4 mb-lg-0">
                    <img src="img/logo.svg" alt="School Inventory" height="40" class="mb-3">
                    <p class="mb-4">Our platform connects schools and parents to streamline the management of educational supplies and resources.</p>
                    <ul class="social-links">
                        <li><a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                        <li><a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a></li>
                        <li><a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-heading">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="index.html">Home</a></li>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="#features">Features</a></li>
                        <li><a href="#contact">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                    <h5 class="footer-heading">For Parents</h5>
                    <ul class="footer-links">
                        <li><a href="parent_login.php">Login</a></li>
                        <li><a href="parent_login.php">Order Books</a></li>
                        <li><a href="parent_login.php">Order Uniforms</a></li>
                        <li><a href="forgot_password.php">Reset Password</a></li>
                    </ul>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h5 class="footer-heading">For Schools</h5>
                    <ul class="footer-links">
                        <li><a href="school_login.php">Login</a></li>
                        <li><a href="school_login.php">Manage Inventory</a></li>
                        <li><a href="school_login.php">Track Orders</a></li>
                        <li><a href="forgot_password.php">Reset Password</a></li>
                    </ul>
                </div>
                <div class="col-lg-2">
                    <h5 class="footer-heading">Support</h5>
                    <ul class="footer-links">
                        <li><a href="#contact">Help Center</a></li>
                        <li><a href="#contact">FAQ</a></li>
                        <li><a href="#contact">Terms of Service</a></li>
                        <li><a href="#contact">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2025 School Inventory Management System | All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JavaScript -->
    <script src="js/main.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Stats counter animation
            const counters = document.querySelectorAll('.stat-counter');
            
            counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                const duration = 2000; // Animation duration in milliseconds
                const step = target / (duration / 50); // Update every 50ms
                let current = 0;
                
                const updateCounter = () => {
                    current += step;
                    if (current < target) {
                        counter.textContent = Math.ceil(current);
                        setTimeout(updateCounter, 50);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                // Start animation when element is in viewport
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            updateCounter();
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });
                
                observer.observe(counter);
            });
            
            // Animate elements when they come into view
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.animate-fade-in-up');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const windowHeight = window.innerHeight;
                    
                    if (elementPosition < windowHeight - 50) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            // Set initial state for animation
            document.querySelectorAll('.animate-fade-in-up').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'all 0.5s ease-out';
            });
            
            // Listen for scroll events
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on page load
        });
    </script>
</body>
</html> 