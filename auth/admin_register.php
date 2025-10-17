<?php
require_once "../includes/db.php";

// Simple admin registration - no complex verification needed
$success = false;
$admin_key = "IAMADMIN"; // Admin key

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $name = trim($_POST["name"]); 
  $email = trim($_POST["email"]); 
  $pass = $_POST["password"]; 
  $admin_key_input = trim($_POST["admin_key"]);
  
  if(!$name || !$email || !$pass || !$admin_key_input){ 
    $err = "All fields are required."; 
  } elseif($admin_key_input !== $admin_key) {
    $err = "Invalid admin key. Please contact system administrator.";
  } else {
    $hash = password_hash($pass, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
    $role = "admin";
    $stmt->bind_param("ssss", $name, $email, $hash, $role);
    if($stmt->execute()){ 
      $success = true;
    } else { 
      $err = "Email already exists or database error."; 
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Registration - Shikshantra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      background-color: #f8fafc;
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      color: #334155;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1rem;
    }
    .auth-container {
      width: 100%;
      max-width: 420px;
    }
    .card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 2.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }
    .auth-header {
      text-align: center;
      margin-bottom: 2rem;
    }
    .auth-title {
      font-size: 1.875rem;
      font-weight: 600;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }
    .auth-subtitle {
      color: #64748b;
      font-size: 0.95rem;
    }
    .admin-badge {
      background-color: #fef2f2;
      color: #dc2626;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      border: 1px solid #fecaca;
      margin-bottom: 1.5rem;
      text-align: center;
    }
    .form-label {
      font-weight: 500;
      color: #374151;
      margin-bottom: 0.5rem;
    }
    .form-control {
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      transition: all 0.15s ease;
    }
    .form-control:focus {
      border-color: #dc2626;
      box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
    }
    .btn-primary {
      background-color: #dc2626;
      border-color: #dc2626;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.15s ease;
    }
    .btn-primary:hover {
      background-color: #b91c1c;
      border-color: #b91c1c;
      transform: translateY(-1px);
    }
    .success-container {
      text-align: center;
      padding: 1rem;
    }
    .success-icon {
      width: 64px;
      height: 64px;
      background-color: #dcfce7;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1.5rem;
    }
    .success-icon i {
      font-size: 1.5rem;
      color: #166534;
    }
    .success-title {
      color: #1e293b;
      font-size: 1.5rem;
      font-weight: 600;
      margin-bottom: 0.75rem;
    }
    .success-message {
      color: #64748b;
      margin-bottom: 2rem;
      line-height: 1.5;
    }
    .btn-success {
      background-color: #166534;
      border-color: #166534;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      color: white;
      text-decoration: none;
      display: inline-block;
      transition: all 0.15s ease;
    }
    .btn-success:hover {
      background-color: #15803d;
      border-color: #15803d;
      color: white;
      transform: translateY(-1px);
    }
    .auth-links {
      text-align: center;
      margin-top: 1.5rem;
    }
    .auth-links a {
      color: #dc2626;
      text-decoration: none;
      font-weight: 500;
    }
    .auth-links a:hover {
      color: #b91c1c;
      text-decoration: underline;
    }
    .text-muted {
      color: #64748b !important;
    }
    .alert {
      border: none;
      border-radius: 8px;
      padding: 0.75rem 1rem;
    }
    .alert-danger {
      background-color: #fef2f2;
      color: #991b1b;
      border-left: 4px solid #dc2626;
    }
    .key-info {
      background-color: #fffbeb;
      border: 1px solid #fed7aa;
      color: #92400e;
      padding: 0.75rem 1rem;
      border-radius: 8px;
      font-size: 0.9rem;
      margin-bottom: 1rem;
    }
  </style>
</head>
<body>

<div class="auth-container">
  <div class="card">
    <?php if($success): ?>
      <!-- Success State -->
      <div class="success-container">
        <div class="success-icon">
          <i class="fas fa-shield-check"></i>
        </div>
        <h2 class="success-title">Admin Account Created!</h2>
        <p class="success-message">
          Your administrator account has been created successfully. You now have full system access and management capabilities.
        </p>
        <a href="/fy_proj/auth/login.php" class="btn btn-success">
          Continue to Login
        </a>
        <div class="auth-links">
          <a href="/fy_proj/">← Back to Home</a>
        </div>
      </div>
    <?php else: ?>
      <!-- Registration Form -->
      <div class="auth-header">
        <h1 class="auth-title">Admin Registration</h1>
        <p class="auth-subtitle">Create an administrator account for system management</p>
      </div>
      
      <div class="admin-badge">
        <i class="fas fa-shield-alt me-2"></i>
        <strong>Administrator Access</strong><br>
        This will create an account with full system privileges
      </div>
      
      <?php if(!empty($err)) echo "<div class='alert alert-danger mb-3'>$err</div>"; ?>
      
      <div class="key-info">
        <i class="fas fa-key me-2"></i>
        <strong>Admin Key Required:</strong> You need the admin key to create an administrator account.
      </div>
      
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Administrator Name</label>
          <input name="name" class="form-control" placeholder="Enter your full name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Create a secure password" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Admin Key</label>
          <input type="password" name="admin_key" class="form-control" placeholder="Enter the admin key" required>
          <div class="form-text text-muted">
            Contact the system owner if you don't have the admin key
          </div>
        </div>
        <button type="submit" class="btn btn-primary w-100">
          <i class="fas fa-shield-check me-2"></i>Create Admin Account
        </button>
      </form>
      
      <div class="auth-links">
        <span class="text-muted">Regular account?</span>
        <a href="/fy_proj/auth/register.php" class="ms-1">Register here</a>
      </div>
      
      <div class="auth-links mt-2">
        <span class="text-muted">Already have an account?</span>
        <a href="/fy_proj/auth/login.php" class="ms-1">Sign in</a>
      </div>
      
      <div class="auth-links mt-2">
        <a href="/fy_proj/">← Back to Home</a>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>