<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shikshantra - <?= $_SESSION['user']['role'] === 'student' ? 'Student' : ($_SESSION['user']['role'] === 'teacher' ? 'Teacher' : 'Admin') ?> Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      line-height: 1.6;
      
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
        /* Student Theme - Playful Educational Background */
        background: linear-gradient(135deg, #dbeafe 0%, #fef3c7 100%);
        position: relative;
        overflow-x: hidden;
        color: #1e293b;
      <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
        /* Teacher Theme - Modern Chalkboard Background */
        background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #0f172a 100%);
        position: relative;
        overflow-x: hidden;
        color: #f1f5f9;
      <?php else: ?>
        background-color: #f8fafc;
        color: #1e293b;
      <?php endif; ?>
    }
    
    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
    /* Educational Pattern Background for Students */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background-image: 
        /* Books */
        radial-gradient(circle at 15% 15%, #3b82f6 2px, transparent 2px),
        radial-gradient(circle at 25% 25%, #10b981 2px, transparent 2px),
        radial-gradient(circle at 35% 35%, #f59e0b 2px, transparent 2px),
        /* Mathematical symbols */
        radial-gradient(circle at 65% 20%, #ec4899 1px, transparent 1px),
        radial-gradient(circle at 75% 40%, #8b5cf6 1px, transparent 1px),
        radial-gradient(circle at 85% 60%, #06b6d4 1px, transparent 1px);
      background-size: 200px 200px, 250px 250px, 180px 180px, 120px 120px, 160px 160px, 140px 140px;
      background-position: 0 0, 50px 50px, 100px 25px, 150px 75px, 200px 100px, 75px 150px;
      opacity: 0.4;
      animation: float 20s ease-in-out infinite;
    }
    
    /* Floating educational icons */
    body::after {
      content: "📚 ✏️ 🔬 🎓 📐 🌍 💡 📖 ✨";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      font-size: 20px;
      display: flex;
      align-items: center;
      justify-content: space-around;
      flex-wrap: wrap;
      z-index: -1;
      opacity: 0.15;
      animation: gentle-float 15s ease-in-out infinite;
      pointer-events: none;
    }
    
    @keyframes float {
      0%, 100% { transform: translateY(0px); }
      25% { transform: translateY(-10px); }
      50% { transform: translateY(-5px); }
      75% { transform: translateY(-8px); }
    }
    
    @keyframes gentle-float {
      0%, 100% { transform: translateY(0px); opacity: 0.1; }
      50% { transform: translateY(-15px); opacity: 0.2; }
    }
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
    /* Modern Chalkboard Pattern Background for Teachers */
    body::before {
      content: '';
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      background-image: 
        /* Chalkboard texture lines */
        linear-gradient(90deg, rgba(241, 245, 249, 0.03) 1px, transparent 1px),
        linear-gradient(rgba(241, 245, 249, 0.03) 1px, transparent 1px),
        /* Chalk dust effect */
        radial-gradient(circle at 15% 25%, rgba(241, 245, 249, 0.08) 1px, transparent 1px),
        radial-gradient(circle at 35% 55%, rgba(241, 245, 249, 0.06) 1px, transparent 1px),
        radial-gradient(circle at 65% 35%, rgba(241, 245, 249, 0.04) 1px, transparent 1px),
        radial-gradient(circle at 85% 75%, rgba(241, 245, 249, 0.07) 1px, transparent 1px),
        /* Subtle mathematical grid */
        linear-gradient(45deg, rgba(34, 197, 94, 0.02) 1px, transparent 1px),
        linear-gradient(-45deg, rgba(34, 197, 94, 0.02) 1px, transparent 1px);
      background-size: 40px 40px, 40px 40px, 180px 180px, 220px 220px, 200px 200px, 160px 160px, 80px 80px, 80px 80px;
      background-position: 0 0, 0 0, 20px 30px, 150px 80px, 80px 180px, 250px 40px, 0 0, 40px 40px;
      opacity: 0.8;
      animation: chalk-drift 25s ease-in-out infinite;
    }
    
    /* Mathematical formulas and academic symbols */
    body::after {
      content: "∑ ∫ π α β γ δ ∞ ± ≤ ≥ ≠ √ ² ³ ∴ ∵ ⊕ ⊗ ∀ ∃ ∈ ⊂ ∩ ∪ ∅";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      font-size: 16px;
      font-family: 'Times New Roman', serif;
      color: rgba(241, 245, 249, 0.15);
      display: flex;
      align-items: center;
      justify-content: space-around;
      flex-wrap: wrap;
      z-index: -1;
      animation: chalk-float 20s ease-in-out infinite;
      pointer-events: none;
      text-shadow: 0 0 3px rgba(241, 245, 249, 0.1);
    }
    
    @keyframes chalk-drift {
      0%, 100% { transform: translateX(0px) translateY(0px); }
      25% { transform: translateX(2px) translateY(-1px); }
      50% { transform: translateX(-1px) translateY(2px); }
      75% { transform: translateX(1px) translateY(-1px); }
    }
    
    @keyframes chalk-float {
      0%, 100% { transform: translateY(0px) scale(1); opacity: 0.12; }
      33% { transform: translateY(-5px) scale(1.01); opacity: 0.18; }
      66% { transform: translateY(3px) scale(0.99); opacity: 0.15; }
    }
    <?php endif; ?>
    
    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
    /* Teacher Theme - Text Color Overrides */
    .page-title, .page-subtitle {
      color: #f1f5f9 !important;
    }
    
    /* Main headings on dark background */
    h1, h2, h3, h4, h5, h6 {
      color: #f1f5f9 !important;
    }
    
    /* Content inside white cards should be dark */
    .content-card h1, .content-card h2, .content-card h3, 
    .content-card h4, .content-card h5, .content-card h6,
    .stats-card h1, .stats-card h2, .stats-card h3,
    .stats-card h4, .stats-card h5, .stats-card h6,
    .page-header h1, .page-header h2, .page-header h3,
    .page-header h4, .page-header h5, .page-header h6 {
      color: #1e293b !important;
    }
    
    /* Text inside white cards should be dark */
    .content-card p, .content-card span, .content-card div,
    .stats-card p, .stats-card span, .stats-card div,
    .page-header p, .page-header span, .page-header div {
      color: #1e293b !important;
    }
    
    /* Muted text inside white cards */
    .content-card .text-muted, .stats-card .text-muted, .page-header .text-muted {
      color: #64748b !important;
    }
    
    /* General text on dark background */
    body > p, body > span, body > div:not(.content-card):not(.stats-card):not(.page-header) {
      color: #f1f5f9;
    }
    
    /* Table styling */
    .table th {
      background-color: #f1f5f9 !important;
      color: #1e293b !important;
      font-weight: 600;
    }
    
    .table td {
      color: #1e293b !important;
      background-color: #ffffff !important;
    }
    
    .table-hover tbody tr:hover {
      background-color: #f8fafc !important;
    }
    
    /* Form elements */
    .form-label {
      color: #1e293b !important;
      font-weight: 500;
    }
    
    /* Badge and button text fixes */
    .badge {
      color: #ffffff !important;
    }
    
    .btn {
      font-weight: 500;
    }
    
    /* Specific fixes for cards */
    .content-card .fw-medium, .content-card .fw-bold,
    .stats-card .fw-medium, .stats-card .fw-bold {
      color: #1e293b !important;
    }
    
    .content-card small, .stats-card small {
      color: #64748b !important;
    }
    
    /* Quick action cards background fix */
    .content-card .border[style*="background-color: #f8fafc"] {
      background-color: #ffffff !important;
    }
    
    /* Stats numbers should be colored */
    .text-success, .text-primary, .text-warning, .text-info, .text-danger {
      color: inherit !important;
    }
    
    .stats-card .text-success {
      color: #16a34a !important;
    }
    
    .stats-card .text-primary {
      color: #2563eb !important;
    }
    
    .navbar {
      background-color: rgba(15, 23, 42, 0.95) !important;
      border-bottom: 1px solid rgba(241, 245, 249, 0.2) !important;
    }
    
    .navbar-brand {
      color: #22c55e !important;
    }
    
    .navbar-nav .nav-link {
      color: #f1f5f9 !important;
    }
    
    .navbar-nav .nav-link:hover {
      background-color: rgba(241, 245, 249, 0.1) !important;
      color: #22c55e !important;
    }
    
    .navbar-nav .nav-link.active {
      background-color: rgba(34, 197, 94, 0.2) !important;
      color: #22c55e !important;
    }
    
    /* Alert overrides for dark theme */
    .alert-success {
      background-color: rgba(34, 197, 94, 0.2) !important;
      color: #22c55e !important;
      border-left: 4px solid #22c55e !important;
    }
    
    .alert-danger {
      background-color: rgba(239, 68, 68, 0.2) !important;
      color: #ef4444 !important;
      border-left: 4px solid #ef4444 !important;
    }
    
    .alert-warning {
      background-color: rgba(245, 158, 11, 0.2) !important;
      color: #f59e0b !important;
      border-left: 4px solid #f59e0b !important;
    }
    
    .alert-info {
      background-color: rgba(59, 130, 246, 0.2) !important;
      color: #3b82f6 !important;
      border-left: 4px solid #3b82f6 !important;
    }
    
    /* Card content specific overrides */
    .content-card h6, .stats-card h6 {
      color: #1e293b !important;
      font-weight: 600;
    }
    
    .content-card .mb-1, .content-card .mb-2 {
      color: #1e293b !important;
    }
    
    /* Override any remaining light text */
    .content-card *, .stats-card *, .page-header * {
      color: #1e293b;
    }
    
    .content-card .text-muted, .stats-card .text-muted, .page-header .text-muted {
      color: #64748b !important;
    }
    
    /* Professional card styling */
    .professional-card {
      background: rgba(241, 245, 249, 0.95) !important;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(241, 245, 249, 0.2);
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
    }
    
    .professional-card * {
      color: #1e293b !important;
    }
    
    .professional-card .text-muted {
      color: #64748b !important;
    }
    
    /* Fix CSS custom property references */
    .professional-card h3[style*="var(--dark-gray)"] {
      color: #1e293b !important;
    }
    
    .professional-card h6[style*="var(--dark-gray)"] {
      color: #1e293b !important;
    }
    
    /* Teaching tools card backgrounds */
    .professional-card .border {
      background-color: #ffffff !important;
      border: 1px solid #e2e8f0 !important;
    }
    
    .professional-card .border:hover {
      background-color: #f8fafc !important;
      border-color: #cbd5e1 !important;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
      transition: all 0.3s ease;
    }
    
    /* Fix any inline styles that might override */
    .professional-card [style*="color: var(--dark-gray)"] {
      color: #1e293b !important;
    }
    
    /* Fix Quick Actions card backgrounds */
    .content-card .border[style*="background-color: #f8fafc"],
    .content-card .border[style*="background: #f8fafc"] {
      background-color: #ffffff !important;
    }
    
    /* Fix Recent Assignments card backgrounds */
    .content-card .border[style*="background: #f8fafc"] {
      background-color: #ffffff !important;
    }
    
    /* Ensure all card content is readable */
    .professional-card .text-center * {
      color: #1e293b !important;
    }
    
    .professional-card .btn-outline-secondary {
      color: #1e293b !important;
      border-color: #1e293b !important;
    }
    
    .professional-card .btn-outline-secondary:hover {
      background-color: #1e293b !important;
      color: #ffffff !important;
    }
    <?php endif; ?>

    /* Navigation */
    .navbar {
      background-color: #ffffff;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      padding: 1rem 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .navbar-brand {
      font-weight: 600;
      color: #1e40af !important;
      font-size: 1.5rem;
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

    .navbar-nav .nav-link.active {
      background-color: #eff6ff;
      color: #1e40af !important;
    }

    /* Main Content */
    .main-content {
      padding: 2rem 0;
      min-height: calc(100vh - 80px);
    }

    /* Cards */
    .content-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
        /* Student cards - more playful and bouncy */
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
      <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
        /* Teacher cards - chalkboard style */
        background: rgba(241, 245, 249, 0.95);
        border: 1px solid rgba(241, 245, 249, 0.2);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
      <?php endif; ?>
    }

    .content-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
      
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
        transform: translateY(-5px) scale(1.02);
        border-color: #60a5fa;
        background: rgba(255, 255, 255, 0.98);
      <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
        transform: translateY(-5px) scale(1.02);
        border-color: rgba(34, 197, 94, 0.4);
        background: rgba(255, 255, 255, 0.98);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
      <?php endif; ?>
    }
    
    <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
    /* Subtle shadow effects instead of rotation */
    .content-card:nth-child(even) {
      box-shadow: 2px 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .content-card:nth-child(odd) {
      box-shadow: -2px 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .content-card:nth-child(3n) {
      box-shadow: 0 6px 12px rgba(0, 0, 0, 0.12);
    }
    <?php endif; ?>

    .stats-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 1.5rem;
      text-align: center;
      transition: all 0.3s ease;
      
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
        background: rgba(255, 255, 255, 0.95);
        border-radius: 16px;
        backdrop-filter: blur(10px);
      <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
        background: rgba(241, 245, 249, 0.95);
        border: 1px solid rgba(241, 245, 249, 0.2);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
      <?php endif; ?>
    }

    .stats-card:hover {
      border-color: #cbd5e1;
      transform: translateY(-5px) scale(1.05);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      
      <?php if (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'student'): ?>
        background: rgba(255, 255, 255, 0.98);
      <?php elseif (isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'teacher'): ?>
        background: rgba(255, 255, 255, 0.98);
        border-color: rgba(34, 197, 94, 0.4);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.4);
      <?php endif; ?>
    }

    .stats-icon {
      width: 60px;
      height: 60px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 1.5rem;
    }

    .stats-icon.blue {
      background-color: #eff6ff;
      color: #1e40af;
    }

    .stats-icon.green {
      background-color: #f0fdf4;
      color: #166534;
    }

    .stats-icon.amber {
      background-color: #fffbeb;
      color: #d97706;
    }

    .stats-icon.purple {
      background-color: #faf5ff;
      color: #7c3aed;
    }

    .stats-icon.red {
      background-color: #fef2f2;
      color: #dc2626;
    }

    .stats-icon.teal {
      background-color: #f0fdfa;
      color: #0d9488;
    }

    /* Page Headers */
    .page-header {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 2rem;
      margin-bottom: 1.5rem;
    }

    .page-title {
      font-size: 2rem;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 0.5rem;
    }

    .page-subtitle {
      color: #64748b;
      font-size: 1.1rem;
      margin-bottom: 0;
    }

    /* Buttons */
    .btn {
      border-radius: 8px;
      padding: 0.75rem 1.5rem;
      font-weight: 500;
      transition: all 0.15s ease;
    }
    
    .btn:hover {
      transform: translateY(-1px);
    }

    .btn-primary {
      background-color: #1e40af;
      border-color: #1e40af;
    }

    .btn-primary:hover {
      background-color: #1d4ed8;
      border-color: #1d4ed8;
    }

    .btn-success {
      background-color: #166534;
      border-color: #166534;
    }

    .btn-success:hover {
      background-color: #15803d;
      border-color: #15803d;
    }

    /* Tables */
    .table {
      background: #ffffff;
      border-radius: 8px;
      overflow: hidden;
    }

    .table th {
      background-color: #f8fafc;
      border-color: #e2e8f0;
      font-weight: 600;
      color: #374151;
      padding: 1rem;
    }

    .table td {
      border-color: #e2e8f0;
      padding: 1rem;
    }

    /* Forms */
    .form-label {
      font-weight: 500;
      color: #374151;
      margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
      border: 1px solid #d1d5db;
      border-radius: 8px;
      padding: 0.75rem 1rem;
      transition: all 0.15s ease;
    }

    .form-control:focus, .form-select:focus {
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    /* Alerts */
    .alert {
      border: none;
      border-radius: 8px;
      padding: 1rem;
      margin-bottom: 1rem;
    }

    .alert-success {
      background-color: #f0fdf4;
      color: #166534;
      border-left: 4px solid #22c55e;
    }

    .alert-danger {
      background-color: #fef2f2;
      color: #991b1b;
      border-left: 4px solid #dc2626;
    }

    .alert-warning {
      background-color: #fffbeb;
      color: #92400e;
      border-left: 4px solid #f59e0b;
    }

    .alert-info {
      background-color: #eff6ff;
      color: #1e40af;
      border-left: 4px solid #3b82f6;
    }

    /* Role-specific colors */
    <?php if (isset($_SESSION['user']['role'])): ?>
      <?php if ($_SESSION['user']['role'] === 'student'): ?>
        .role-primary { background-color: #3b82f6; }
        .role-secondary { background-color: #06b6d4; }
      <?php elseif ($_SESSION['user']['role'] === 'teacher'): ?>
        .role-primary { background-color: #16a34a; }
        .role-secondary { background-color: #059669; }
      <?php else: ?>
        .role-primary { background-color: #dc2626; }
        .role-secondary { background-color: #ea580c; }
      <?php endif; ?>
    <?php endif; ?>

    /* Badges */
    .badge {
      border-radius: 6px;
      font-weight: 500;
      padding: 0.5rem 0.75rem;
    }

    /* Links */
    a {
      color: #1e40af;
      text-decoration: none;
    }

    a:hover {
      color: #1d4ed8;
      text-decoration: underline;
    }

    /* Ensure specific cards are stable and not tilted */
    .progress-card, .content-card.progress-card {
      transform: none !important;
      box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3) !important;
    }
    
    .progress-card:hover {
      transform: translateY(-5px) scale(1.02) !important;
    }
    
    /* Override any rotations on important cards */
    .stats-card, .welcome-card, .professional-card {
      transform: none !important;
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
      .main-content {
        padding: 1rem 0;
      }
      
      .content-card, .page-header {
        padding: 1rem;
      }
      
      .page-title {
        font-size: 1.75rem;
      }
    }
  </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand" href="<?php 
      if (isset($_SESSION['user']['role'])) {
        if ($_SESSION['user']['role'] === 'student') {
          echo '/fy_proj/student/index.php';
        } elseif ($_SESSION['user']['role'] === 'teacher') {
          echo '/fy_proj/teacher/index.php';
        } else {
          echo '/fy_proj/admin/index.php';
        }
      } else {
        echo '/fy_proj/';
      }
    ?>">
      <i class="fas fa-graduation-cap me-2"></i>
      Shikshantra
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <?php if ($_SESSION['user']['role'] === 'student'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/student/index.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/assignments/index.php"><i class="fas fa-tasks me-1"></i>Assignments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/quizzes/index.php"><i class="fas fa-question-circle me-1"></i>Quizzes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/attendance/index.php"><i class="fas fa-calendar-check me-1"></i>Attendance</a>
          </li>
        <?php elseif ($_SESSION['user']['role'] === 'teacher'): ?>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/teacher/index.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/assignments/index.php"><i class="fas fa-tasks me-1"></i>Assignments</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/quizzes/index.php"><i class="fas fa-question-circle me-1"></i>Quizzes</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/teacher/classes.php"><i class="fas fa-chalkboard me-1"></i>Classes</a>
          </li>
        <?php else: ?>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/admin/index.php"><i class="fas fa-tachometer-alt me-1"></i>Dashboard</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/admin/reports.php"><i class="fas fa-chart-bar me-1"></i>Reports</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="/fy_proj/notices/index.php"><i class="fas fa-bullhorn me-1"></i>Notices</a>
          </li>
        <?php endif; ?>
        
        <!-- Common navigation items -->
        <li class="nav-item">
          <a class="nav-link" href="/fy_proj/chat/index.php"><i class="fas fa-comments me-1"></i>Messages</a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
            <div class="stats-icon <?= $_SESSION['user']['role'] === 'student' ? 'blue' : ($_SESSION['user']['role'] === 'teacher' ? 'green' : 'red') ?> me-2" style="width: 32px; height: 32px; font-size: 0.875rem;">
              <i class="fas fa-user"></i>
            </div>
            <?= htmlspecialchars($_SESSION['user']['name']) ?>
          </a>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/fy_proj/auth/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="main-content">
  <div class="container">