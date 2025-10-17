<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $email=trim($_POST["email"]); 
  $pass=$_POST["password"];
  $stmt=$conn->prepare("SELECT user_id,name,email,password,role FROM users WHERE email=? LIMIT 1");
  $stmt->bind_param("s",$email); 
  $stmt->execute(); 
  $res=$stmt->get_result();
  if($row=$res->fetch_assoc()){
    if(password_verify($pass,$row["password"])){
      $_SESSION["user"]=[
        "user_id"=>$row["user_id"],
        "name"=>$row["name"],
        "email"=>$row["email"],
        "role"=>$row["role"]
      ];
      
      // Redirect to role-specific dashboard
      switch($row["role"]) {
        case 'admin':
          header("Location: /fy_proj/admin/index.php");
          break;
        case 'teacher':
          header("Location: /fy_proj/teacher/index.php");
          break;
        case 'student':
          header("Location: /fy_proj/student/index.php");
          break;
        default:
          header("Location: /fy_proj/auth/login.php");
      }
      exit;
    }
  }
  $err="Invalid credentials.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - Shikshantra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }
    .btn-primary {
      background-color: #1e40af;
      border-color: #1e40af;
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      font-size: 0.95rem;
      transition: all 0.15s ease;
    }
    .btn-primary:hover {
      background-color: #1d4ed8;
      border-color: #1d4ed8;
      transform: translateY(-1px);
    }
    .auth-links {
      text-align: center;
      margin-top: 1.5rem;
    }
    .auth-links a {
      color: #1e40af;
      text-decoration: none;
      font-weight: 500;
    }
    .auth-links a:hover {
      color: #1d4ed8;
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
  </style>
</head>
<body>

<div class="auth-container">
  <div class="card">
    <div class="auth-header">
      <h1 class="auth-title">Welcome Back</h1>
      <p class="auth-subtitle">Sign in to your Shikshantra account</p>
    </div>
    
    <?php if(!empty($err)) echo "<div class='alert alert-danger mb-3'>$err</div>"; ?>
    
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Email Address</label>
        <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">
        Sign In
      </button>
    </form>
    
    <div class="auth-links">
      <span class="text-muted">Don't have an account?</span>
      <a href="/fy_proj/auth/register.php" class="ms-1">Create one</a>
    </div>
    
    <div class="auth-links mt-2">
      <a href="/fy_proj/">← Back to Home</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
