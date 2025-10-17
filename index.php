<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shikshantra - Learning Management System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    html {
      scroll-behavior: smooth;
    }

    body {
      background-color: #f8fafc;
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #334155;
      line-height: 1.6;
    }

    /* Header Section */
    .navbar {
      background-color: #ffffff;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      padding: 1rem 0;
    }

    .navbar-brand {
      font-weight: 600;
      color: #1e40af !important;
      font-size: 1.5rem;
    }

    .navbar-nav {
      margin-left: auto;
    }

    .navbar-nav .nav-link {
      color: #64748b !important;
      font-weight: 500;
      margin: 0 0.5rem;
      padding: 0.5rem 1rem !important;
      border-radius: 8px;
      transition: all 0.15s ease;
    }

    .navbar-nav .nav-link:hover {
      background-color: #f1f5f9;
      color: #1e293b !important;
    }

    .navbar-nav .nav-link.contact-btn {
      background-color: #1e40af;
      color: #ffffff !important;
      margin-left: 1rem;
    }

    .navbar-nav .nav-link.contact-btn:hover {
      background-color: #1d4ed8;
      color: #ffffff !important;
    }

    .navbar-toggler {
      border: 1px solid #e2e8f0;
      padding: 0.25rem 0.5rem;
    }

    .navbar-toggler:focus {
      box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.2);
    }

    .navbar-toggler-icon {
      background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%2864, 116, 139, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='m4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
    }

    .hero-section {
      background-color: #ffffff;
      padding: 80px 0 60px;
      border-bottom: 1px solid #e2e8f0;
    }

    .hero-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 1rem;
    }

    .hero-title {
      font-size: 3.2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 1.5rem;
      line-height: 1.1;
    }

    .hero-subtitle {
      font-size: 1.25rem;
      color: #64748b;
      margin-bottom: 3rem;
      font-weight: 400;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }

    .hero-stats {
      display: flex;
      justify-content: center;
      gap: 3rem;
      margin-bottom: 4rem;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center;
    }

    .stat-number {
      font-size: 2rem;
      font-weight: 700;
      color: #1e40af;
      display: block;
    }

    .stat-label {
      font-size: 0.875rem;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      font-weight: 500;
    }

    /* Action Cards Section */
    .actions-section {
      background-color: #f8fafc;
      padding: 60px 0;
    }

    .section-title {
      text-align: center;
      font-size: 2.5rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 1rem;
    }

    .section-subtitle {
      text-align: center;
      font-size: 1.125rem;
      color: #64748b;
      margin-bottom: 3rem;
      max-width: 500px;
      margin-left: auto;
      margin-right: auto;
    }

    .action-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 2rem;
      margin: 1rem 0;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: all 0.2s ease;
      width: 100%;
      flex: 1;
      height: 100%;
    }

    .action-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transform: translateY(-2px);
    }

    .action-icon {
      width: 60px;
      height: 60px;
      background-color: #eff6ff;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 1.5rem;
    }

    .action-icon i {
      font-size: 1.5rem;
      color: #1e40af;
    }

    .login-card .action-icon {
      background-color: #eff6ff;
    }

    .register-card .action-icon {
      background-color: #f0fdf4;
    }

    .register-card .action-icon i {
      color: #166534;
    }

    .action-title {
      font-size: 1.5rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.75rem;
    }

    .action-description {
      color: #64748b;
      margin-bottom: 2rem;
      line-height: 1.5;
      font-size: 0.95rem;
    }

    .btn-custom {
      padding: 12px 24px;
      font-weight: 500;
      border-radius: 8px;
      text-decoration: none;
      transition: all 0.15s ease;
      display: inline-block;
      font-size: 0.95rem;
      border: 1px solid transparent;
    }

    .btn-login {
      background-color: #1e40af;
      color: white;
      border-color: #1e40af;
    }

    .btn-login:hover {
      background-color: #1d4ed8;
      border-color: #1d4ed8;
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .btn-register {
      background-color: #166534;
      color: white;
      border-color: #166534;
    }

    .btn-register:hover {
      background-color: #15803d;
      border-color: #15803d;
      color: white;
      transform: translateY(-1px);
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    /* Features Section */
    .features-section {
      background-color: #ffffff;
      padding: 60px 0;
      border-top: 1px solid #e2e8f0;
    }

    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 2rem;
      margin-top: 2rem;
    }

    .feature-item {
      text-align: center;
      padding: 1.5rem;
    }

    .feature-icon {
      width: 48px;
      height: 48px;
      background-color: #f1f5f9;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
    }

    .feature-icon i {
      font-size: 1.25rem;
      color: #475569;
    }

    .feature-title {
      font-size: 1.1rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }

    .feature-description {
      font-size: 0.9rem;
      color: #64748b;
      line-height: 1.4;
    }

    /* About Section */
    .about-section {
      background-color: #f8fafc;
      padding: 60px 0;
      border-top: 1px solid #e2e8f0;
    }

    .about-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    /* Contact Section */
    .contact-section {
      background-color: #1e40af;
      color: #ffffff;
      padding: 60px 0;
    }

    .contact-section .section-title {
      color: #ffffff;
    }

    .contact-section .section-subtitle {
      color: #cbd5e1;
    }

    .contact-card {
      background: #ffffff;
      border: none;
      border-radius: 12px;
      padding: 2rem;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      color: #1e293b;
    }

    .contact-item {
      display: flex;
      align-items: center;
      margin-bottom: 1.5rem;
    }

    .contact-icon {
      width: 48px;
      height: 48px;
      background-color: #eff6ff;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 1rem;
    }

    .contact-icon i {
      font-size: 1.25rem;
      color: #1e40af;
    }

    .contact-info h5 {
      color: #1e293b;
      font-weight: 600;
      margin-bottom: 0.25rem;
      font-size: 1rem;
    }

    .contact-info p {
      color: #64748b;
      margin: 0;
      font-size: 0.9rem;
    }

    /* Footer */
    .footer {
      background-color: #1e293b;
      color: #94a3b8;
      padding: 2rem 0;
      text-align: center;
      font-size: 0.9rem;
    }

    .footer a {
      color: #cbd5e1;
      text-decoration: none;
    }

    .footer a:hover {
      color: #ffffff;
    }

    .equal-height-row {
      display: flex;
      align-items: stretch;
    }

    .card-content {
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }

    .card-body {
      flex-grow: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 1rem 0;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
      .navbar-nav {
        margin-left: 0;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
      }

      .navbar-nav .nav-link {
        margin: 0.25rem 0;
        text-align: center;
      }

      .navbar-nav .nav-link.contact-btn {
        margin-left: 0;
        margin-top: 0.5rem;
      }

      .hero-title {
        font-size: 2.5rem;
      }
      .hero-subtitle {
        font-size: 1.125rem;
      }
      .hero-stats {
        gap: 2rem;
      }
      .section-title {
        font-size: 2rem;
      }
      .action-card {
        margin: 0.5rem 0;
        padding: 1.5rem;
      }
      .equal-height-row {
        flex-direction: column;
      }
      .features-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
      }
    }

    @media (max-width: 480px) {
      .hero-title {
        font-size: 2rem;
      }
      .hero-container {
        padding: 0 1rem;
      }
      .hero-section {
        padding: 60px 0 40px;
      }
      .actions-section,
      .features-section {
        padding: 40px 0;
      }
    }
  </style>
</head>
<body>
  <!-- Navigation -->
  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="#">
        <i class="fas fa-graduation-cap me-2"></i>
        Shikshantra
      </a>
      
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav">
          <li class="nav-item">
            <a class="nav-link" href="#features">
              <i class="fas fa-star me-1"></i>Features
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#about">
              <i class="fas fa-info-circle me-1"></i>About
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/auth/login.php">
              <i class="fas fa-sign-in-alt me-1"></i>Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link contact-btn" href="#contact">
              <i class="fas fa-envelope me-1"></i>Contact
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="hero-container text-center">
        <h1 class="hero-title">
          Modern Learning Management System
        </h1>
        <p class="hero-subtitle">
          Streamline education with our comprehensive platform designed for students, teachers, and administrators.
        </p>
        
        <!-- Stats -->
        <div class="hero-stats">
          <div class="stat-item">
            <span class="stat-number">500+</span>
            <span class="stat-label">Students</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">50+</span>
            <span class="stat-label">Teachers</span>
          </div>
          <div class="stat-item">
            <span class="stat-number">24/7</span>
            <span class="stat-label">Support</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Action Cards Section -->
  <section class="actions-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="section-title">Get Started</h2>
          <p class="section-subtitle">
            Choose your path to begin your learning journey with Shikshantra.
          </p>
        </div>
      </div>
      
      <div class="row equal-height-row">
        <div class="col-md-6 d-flex">
          <div class="action-card login-card">
            <div class="card-content">
              <div class="action-icon">
                <i class="fas fa-sign-in-alt"></i>
              </div>
              <h3 class="action-title">Sign In</h3>
              <div class="card-body">
                <p class="action-description">
                  Access your personalized dashboard and continue where you left off.
                </p>
              </div>
              <div class="mt-auto">
                <a href="/fy_proj/auth/login.php" class="btn btn-custom btn-login">
                  Login to Account
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6 d-flex">
          <div class="action-card register-card">
            <div class="card-content">
              <div class="action-icon">
                <i class="fas fa-user-plus"></i>
              </div>
              <h3 class="action-title">Create Account</h3>
              <div class="card-body">
                <p class="action-description">
                  Join our academic community and unlock all platform features.
                </p>
              </div>
              <div class="mt-auto">
                <a href="/fy_proj/auth/register.php" class="btn btn-custom btn-register">
                  Register Now
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Features Section -->
  <section id="features" class="features-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="section-title">Platform Features</h2>
          <p class="section-subtitle">
            Everything you need for effective learning and teaching management.
          </p>
        </div>
      </div>
      
      <div class="features-grid">
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-tasks"></i>
          </div>
          <h4 class="feature-title">Assignment Management</h4>
          <p class="feature-description">
            Create, distribute, and grade assignments with our intuitive tools.
          </p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h4 class="feature-title">Progress Tracking</h4>
          <p class="feature-description">
            Monitor student performance with detailed analytics and reports.
          </p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-comments"></i>
          </div>
          <h4 class="feature-title">Communication Tools</h4>
          <p class="feature-description">
            Stay connected with integrated messaging and feedback systems.
          </p>
        </div>
        <div class="feature-item">
          <div class="feature-icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <h4 class="feature-title">Attendance System</h4>
          <p class="feature-description">
            Track attendance and participation with automated recording.
          </p>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="about-section">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="section-title">About Shikshantra</h2>
          <p class="section-subtitle">
            Empowering education through innovative technology and seamless learning experiences.
          </p>
        </div>
      </div>
      
      <div class="row">
        <div class="col-lg-6 mb-4">
          <div class="about-card">
            <h4 class="mb-3">
              <i class="fas fa-target me-2 text-primary"></i>Our Mission
            </h4>
            <p class="text-muted mb-0">
              To revolutionize education by providing a comprehensive learning management system that connects students, teachers, and administrators in a seamless digital environment. We believe in making quality education accessible and engaging for everyone.
            </p>
          </div>
        </div>
        <div class="col-lg-6 mb-4">
          <div class="about-card">
            <h4 class="mb-3">
              <i class="fas fa-eye me-2 text-primary"></i>Our Vision
            </h4>
            <p class="text-muted mb-0">
              To become the leading platform that transforms traditional education through cutting-edge technology, fostering collaboration, creativity, and continuous learning in academic communities worldwide.
            </p>
          </div>
        </div>
      </div>
      
      <div class="row">
        <div class="col-lg-12">
          <div class="about-card">
            <h4 class="mb-3">
              <i class="fas fa-graduation-cap me-2 text-primary"></i>Why Choose Shikshantra?
            </h4>
            <div class="row">
              <div class="col-md-4 mb-3">
                <h6 class="fw-semibold">📚 Comprehensive Tools</h6>
                <p class="text-muted small mb-0">Complete suite of academic management features</p>
              </div>
              <div class="col-md-4 mb-3">
                <h6 class="fw-semibold">🔒 Secure & Reliable</h6>
                <p class="text-muted small mb-0">Bank-level security for your educational data</p>
              </div>
              <div class="col-md-4 mb-3">
                <h6 class="fw-semibold">📱 Mobile Friendly</h6>
                <p class="text-muted small mb-0">Access your content anywhere, anytime</p>
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
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <h2 class="section-title">Get In Touch</h2>
          <p class="section-subtitle">
            Have questions or need support? We're here to help you succeed.
          </p>
        </div>
      </div>
      
      <div class="row justify-content-center">
        <div class="col-lg-8">
          <div class="contact-card">
            <div class="row">
              <div class="col-md-6">
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="fas fa-envelope"></i>
                  </div>
                  <div class="contact-info">
                    <h5>Email Support</h5>
                    <p>support@smartacademic.edu</p>
                    <p>admissions@smartacademic.edu</p>
                  </div>
                </div>
                
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="fas fa-phone"></i>
                  </div>
                  <div class="contact-info">
                    <h5>Phone Support</h5>
                    <p>+1 (555) 123-4567</p>
                    <p>Mon-Fri: 8AM-6PM EST</p>
                  </div>
                </div>
              </div>
              
              <div class="col-md-6">
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="fas fa-map-marker-alt"></i>
                  </div>
                  <div class="contact-info">
                    <h5>Office Location</h5>
                    <p>123 Education Avenue</p>
                    <p>Learning City, LC 12345</p>
                  </div>
                </div>
                
                <div class="contact-item">
                  <div class="contact-icon">
                    <i class="fas fa-clock"></i>
                  </div>
                  <div class="contact-info">
                    <h5>Support Hours</h5>
                    <p>Monday - Friday: 8AM-6PM</p>
                    <p>Weekend: 9AM-5PM</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div class="row mt-4">
              <div class="col-12 text-center">
                <div class="border-top pt-4">
                  <h5 class="mb-3">Quick Links</h5>
                  <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="/fy_proj/auth/login.php" class="btn btn-outline-primary btn-sm">
                      <i class="fas fa-sign-in-alt me-1"></i>Student Login
                    </a>
                    <a href="/fy_proj/auth/register.php" class="btn btn-primary btn-sm">
                      <i class="fas fa-user-plus me-1"></i>Register Now
                    </a>
                    <a href="/fy_proj/auth/admin_register.php" class="btn btn-outline-secondary btn-sm">
                      <i class="fas fa-shield-alt me-1"></i>Admin Access
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="footer">
    <div class="container">
      <div class="row">
        <div class="col-md-8">
          <p>&copy; 2025 Shikshantra. Built for modern education.</p>
        </div>
        <div class="col-md-4 text-end">
          <a href="/fy_proj/auth/admin_register.php" class="text-decoration-none" style="color: #cbd5e1; font-size: 0.9rem;">
            <i class="fas fa-shield-alt me-1"></i>Admin Registration
          </a>
        </div>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Simple enhancement for better UX
    document.addEventListener('DOMContentLoaded', function() {
      // Add click functionality to cards
      const actionCards = document.querySelectorAll('.action-card');
      actionCards.forEach(card => {
        card.addEventListener('click', function(e) {
          if (e.target.tagName !== 'A' && e.target.closest('a') === null) {
            const link = this.querySelector('a.btn');
            if (link) {
              window.location.href = link.href;
            }
          }
        });
        
        // Add hover effect for better visual feedback
        card.style.cursor = 'pointer';
      });
    });
  </script>
</body>
</html>
