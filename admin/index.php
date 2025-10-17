<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin']); 
require_once "../includes/natural_header.php";

// Handle user deletion
if (isset($_POST['delete_user'])) {
    $user_id = intval($_POST['user_id']);
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ? AND role != 'admin'");
    $stmt->bind_param("i", $user_id);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">User deleted successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error deleting user.</div>';
    }
    $stmt->close();
}

// Get all teachers
$teachers_query = "SELECT user_id, name, email FROM users WHERE role = 'teacher' ORDER BY name";
$teachers_result = $conn->query($teachers_query);

// Get all students
$students_query = "SELECT user_id, name, email FROM users WHERE role = 'student' ORDER BY name";
$students_result = $conn->query($students_query);

// Get system statistics
$assignments_count = $conn->query("SELECT COUNT(*) as count FROM assignments")->fetch_assoc()['count'];
$quizzes_count = $conn->query("SELECT COUNT(*) as count FROM quizzes")->fetch_assoc()['count'];
$notices_count = $conn->query("SELECT COUNT(*) as count FROM notices")->fetch_assoc()['count'];
$timetable_count = $conn->query("SELECT COUNT(*) as count FROM timetable")->fetch_assoc()['count'];
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">System Administration</h1>
            <p class="page-subtitle">Manage users, monitor system performance, and oversee academic operations</p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="d-flex flex-column align-items-end">
                <div class="text-muted mb-2">
                    <i class="fas fa-shield-alt"></i> Administrator Access
                </div>
                <a href="/fy_proj/notices/index.php" class="btn btn-primary">
                    <i class="fas fa-bullhorn me-1"></i>Manage Notices
                </a>
            </div>
        </div>
    </div>
</div>

<!-- System Overview Stats -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon red">
                <i class="fas fa-tasks"></i>
            </div>
            <h3 style="color: #dc2626;" class="mb-2"><?php echo $assignments_count; ?></h3>
            <p class="mb-1">Total Assignments</p>
            <small class="text-muted">System wide</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon green">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h3 class="text-success mb-2"><?php echo $teachers_result->num_rows; ?></h3>
            <p class="mb-1">Active Teachers</p>
            <small class="text-muted">Registered users</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon blue">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h3 class="text-primary mb-2"><?php echo $students_result->num_rows; ?></h3>
            <p class="mb-1">Total Students</p>
            <small class="text-muted">Enrolled users</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon amber">
                <i class="fas fa-server"></i>
            </div>
            <h3 style="color: #d97706;" class="mb-2">Online</h3>
            <p class="mb-1">System Status</p>
            <small class="text-success">All systems operational</small>
        </div>
    </div>
</div>

<!-- User Management Section -->
<div class="row">
    <div class="col-lg-6">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <i class="fas fa-chalkboard-teacher me-2"></i>Teachers Management
                </h4>
                <span class="badge bg-success">
                    <?php echo $teachers_result->num_rows; ?> Active
                </span>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Teacher</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $teachers_result->data_seek(0); // Reset result pointer
                        while ($teacher = $teachers_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon green me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($teacher['name']); ?></div>
                                        <small class="text-muted">Teacher</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo htmlspecialchars($teacher['email']); ?></small>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this teacher? This will remove their assignments, quizzes, and other content.');">
                                    <input type="hidden" name="user_id" value="<?php echo $teacher['user_id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>
                    <i class="fas fa-user-graduate me-2"></i>Students Management
                </h4>
                <span class="badge bg-primary">
                    <?php echo $students_result->num_rows; ?> Enrolled
                </span>
            </div>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Contact</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $students_result->data_seek(0); // Reset result pointer
                        while ($student = $students_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="stats-icon blue me-2" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium"><?php echo htmlspecialchars($student['name']); ?></div>
                                        <small class="text-muted">Student</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                            </td>
                            <td>
                                <form method="POST" style="display: inline;" 
                                      onsubmit="return confirm('Are you sure you want to delete this student?');">
                                    <input type="hidden" name="user_id" value="<?php echo $student['user_id']; ?>">
                                    <button type="submit" name="delete_user" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash me-1"></i>Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Administrative Actions -->
<div class="content-card">
    <h3 class="mb-4">
        <i class="fas fa-tools me-2"></i>Administrative Actions
    </h3>
    
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                <div class="stats-icon red mb-3">
                    <i class="fas fa-cog"></i>
                </div>
                <h6>System Management</h6>
                <p class="text-muted small mb-3">Manage classes and system settings</p>
                <a href="classes.php" class="btn btn-outline-primary btn-sm">Manage Classes</a>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                <div class="stats-icon amber mb-3">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <h6>System Reports</h6>
                <p class="text-muted small mb-3">View usage analytics and reports</p>
                <a href="reports.php" class="btn btn-outline-primary btn-sm">View Reports</a>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                <div class="stats-icon blue mb-3">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <h6>Notices</h6>
                <p class="text-muted small mb-3">Manage system-wide announcements</p>
                <a href="/fy_proj/notices/index.php" class="btn btn-outline-primary btn-sm">Manage Notices</a>
            </div>
        </div>
    </div>
    
    <div class="text-center mt-4 pt-4 border-top">
        <div class="text-muted mb-3">
            <i class="fas fa-shield-check me-2"></i>
            Administrator privileges active - Full system access granted
        </div>
        <div class="row text-center">
            <div class="col-md-4">
                <span class="badge bg-success p-2">
                    <i class="fas fa-check-circle me-1"></i>Systems Online
                </span>
            </div>
            <div class="col-md-4">
                <span class="badge bg-primary p-2">
                    <i class="fas fa-users me-1"></i><?= $teachers_result->num_rows + $students_result->num_rows ?> Total Users
                </span>
            </div>
            <div class="col-md-4">
                <span class="badge bg-info p-2">
                    <i class="fas fa-calendar me-1"></i><?= $timetable_count ?> Schedule Entries
                </span>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
