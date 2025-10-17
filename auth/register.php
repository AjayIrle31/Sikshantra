<?php
require_once "../includes/db.php";
$success = false;
if($_SERVER["REQUEST_METHOD"]==="POST"){
  $name=trim($_POST["name"]); 
  $email=trim($_POST["email"]); 
  $pass=$_POST["password"]; 
  $role=$_POST["role"];
  if(!$name||!$email||!$pass){ 
    $err="All fields are required."; 
  } elseif($role === 'admin') {
    $err="Administrator accounts cannot be created through public registration. Please contact system administrator.";
  } elseif(!in_array($role, ['student', 'teacher'])) {
    $err="Invalid account type selected.";
  } else {
    $hash=password_hash($pass,PASSWORD_DEFAULT);
    $stmt=$conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
    $stmt->bind_param("ssss",$name,$email,$hash,$role);
    if($stmt->execute()){ 
      $success = true;
    } else { 
      $err="Email exists or DB error."; 
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - Shikshantra</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
    .form-control, .form-select {
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      font-size: 0.95rem;
      transition: all 0.15s ease;
    }
    .form-control:focus, .form-select:focus {
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
    
    /* Role Toggle Styles */
    .role-toggle-container {
      text-align: center;
      margin-bottom: 1.5rem;
    }
    
    .role-toggle {
      display: inline-flex;
      background: #f1f5f9;
      border-radius: 50px;
      padding: 4px;
      position: relative;
      border: 2px solid #e2e8f0;
      transition: all 0.3s ease;
    }
    
    .role-option {
      flex: 1;
      padding: 12px 24px;
      border-radius: 50px;
      border: none;
      background: transparent;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      min-width: 120px;
      position: relative;
      z-index: 2;
    }
    
    .role-option.active {
      background: white;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .role-option i {
      font-size: 1.1rem;
    }
    
    /* Student Theme */
    .theme-student .card {
      border-top: 4px solid #3b82f6;
      transition: all 0.3s ease;
    }
    
    .theme-student .role-toggle {
      border-color: #3b82f6;
    }
    
    .theme-student .role-option.active {
      color: #3b82f6;
    }
    
    .theme-student .btn-primary {
      background-color: #3b82f6;
      border-color: #3b82f6;
    }
    
    .theme-student .btn-primary:hover {
      background-color: #2563eb;
      border-color: #2563eb;
    }
    
    .theme-student .form-control:focus, .theme-student .form-select:focus {
      border-color: #3b82f6;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    /* Teacher Theme */
    .theme-teacher .card {
      border-top: 4px solid #10b981;
      transition: all 0.3s ease;
    }
    
    .theme-teacher .role-toggle {
      border-color: #10b981;
    }
    
    .theme-teacher .role-option.active {
      color: #10b981;
    }
    
    .theme-teacher .btn-primary {
      background-color: #10b981;
      border-color: #10b981;
    }
    
    .theme-teacher .btn-primary:hover {
      background-color: #059669;
      border-color: #059669;
    }
    
    .theme-teacher .form-control:focus, .theme-teacher .form-select:focus {
      border-color: #10b981;
      box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    
    /* Role-specific icons in header */
    .role-icon {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 60px;
      height: 60px;
      border-radius: 50%;
      margin: 0 auto 1rem;
      font-size: 1.5rem;
      transition: all 0.3s ease;
    }
    
    .theme-student .role-icon {
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      color: white;
    }
    
    .theme-teacher .role-icon {
      background: linear-gradient(135deg, #10b981, #059669);
      color: white;
    }
  </style>
</head>
<body>

<div class="auth-container" id="authContainer">
  <div class="card">
    <?php if($success): ?>
      <!-- Success State -->
      <div class="success-container">
        <div class="success-icon">
          <i class="fas fa-check-circle"></i>
        </div>
        <h2 class="success-title">Account Created!</h2>
        <p class="success-message">
          Welcome to Shikshantra! Your account has been created successfully and you're ready to get started.
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
        <div class="role-icon" id="roleIcon">
          <i class="fas fa-user-graduate" id="roleIconSymbol"></i>
        </div>
        <h1 class="auth-title" id="authTitle">Create Student Account</h1>
        <p class="auth-subtitle" id="authSubtitle">Join Shikshantra and start your learning journey</p>
      </div>
      
      <?php if(!empty($err)) echo "<div class='alert alert-danger mb-3'>$err</div>"; ?>
      
      <form method="post">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input name="name" class="form-control" placeholder="Enter your full name" 
                 value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="Enter your email address" 
                 value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" placeholder="Create a secure password" required>
        </div>
        <!-- Role Selection Toggle -->
        <div class="role-toggle-container">
          <label class="form-label d-block mb-3">Choose Your Role</label>
          <div class="role-toggle">
            <button type="button" class="role-option active" id="studentBtn" onclick="selectRole('student')">
              <i class="fas fa-user-graduate"></i>
              <span>Student</span>
            </button>
            <button type="button" class="role-option" id="teacherBtn" onclick="selectRole('teacher')">
              <i class="fas fa-chalkboard-teacher"></i>
              <span>Teacher</span>
            </button>
          </div>
          <input type="hidden" name="role" id="roleInput" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : 'student'; ?>">
        </div>
        <button type="submit" class="btn btn-primary w-100">
          Create Account
        </button>
      </form>
      
      <div class="auth-links">
        <span class="text-muted">Already have an account?</span>
        <a href="/fy_proj/auth/login.php" class="ms-1">Sign in</a>
      </div>
      
      <div class="auth-links mt-2">
        <a href="/fy_proj/">← Back to Home</a>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function selectRole(role) {
  // Update hidden input
  document.getElementById('roleInput').value = role;
  
  // Update toggle buttons
  document.getElementById('studentBtn').classList.remove('active');
  document.getElementById('teacherBtn').classList.remove('active');
  document.getElementById(role + 'Btn').classList.add('active');
  
  // Update container theme
  const container = document.getElementById('authContainer');
  container.className = 'auth-container theme-' + role;
  
  // Update header content based on role
  const roleIcon = document.getElementById('roleIconSymbol');
  const authTitle = document.getElementById('authTitle');
  const authSubtitle = document.getElementById('authSubtitle');
  
  if (role === 'student') {
    roleIcon.className = 'fas fa-user-graduate';
    authTitle.textContent = 'Create Student Account';
    authSubtitle.textContent = 'Join Shikshantra and start your learning journey';
  } else {
    roleIcon.className = 'fas fa-chalkboard-teacher';
    authTitle.textContent = 'Create Teacher Account';
    authSubtitle.textContent = 'Join Shikshantra and inspire the next generation';
  }
}

// Initialize with student theme on page load
document.addEventListener('DOMContentLoaded', function() {
  // Check if there was a previous selection (in case of validation error)
  const roleInput = document.getElementById('roleInput');
  const savedRole = roleInput.value || 'student';
  selectRole(savedRole);
});
</script>
</body>
</html>
