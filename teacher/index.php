<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['teacher']); 
require_once "../includes/natural_header.php";

$teacher_id = $_SESSION['user']['user_id'];
$teacher_name = $_SESSION['user']['name'];

// Get teacher statistics
$assignments_count_query = "SELECT COUNT(*) as count FROM assignments WHERE uploaded_by = ?";
$assignments_stmt = $conn->prepare($assignments_count_query);
$assignments_stmt->bind_param("i", $teacher_id);
$assignments_stmt->execute();
$assignments_count = $assignments_stmt->get_result()->fetch_assoc()['count'] ?? 0;

$quizzes_count_query = "SELECT COUNT(*) as count FROM quizzes WHERE created_by = ?";
$quizzes_stmt = $conn->prepare($quizzes_count_query);
$quizzes_stmt->bind_param("i", $teacher_id);
$quizzes_stmt->execute();
$quizzes_count = $quizzes_stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get total student count
$students_count_query = "SELECT COUNT(*) as count FROM users WHERE role = 'student'";
$students_stmt = $conn->prepare($students_count_query);
$students_stmt->execute();
$students_count = $students_stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get recent assignments created by teacher
$recent_assignments_query = "SELECT assignment_id, title, upload_date, description 
                            FROM assignments 
                            WHERE uploaded_by = ? 
                            ORDER BY upload_date DESC 
                            LIMIT 5";
$recent_stmt = $conn->prepare($recent_assignments_query);
$recent_stmt->bind_param("i", $teacher_id);
$recent_stmt->execute();
$recent_assignments = $recent_stmt->get_result();

// Get recent notices posted by teacher
$notices_count_query = "SELECT COUNT(*) as count FROM notices WHERE posted_by = ?";
$notices_stmt = $conn->prepare($notices_count_query);
$notices_stmt->bind_param("i", $teacher_id);
$notices_stmt->execute();
$notices_count = $notices_stmt->get_result()->fetch_assoc()['count'] ?? 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">Welcome back, <?= htmlspecialchars($teacher_name) ?></h1>
            <p class="page-subtitle">Manage assignments, quizzes, and monitor student progress from your dashboard</p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="d-flex flex-column align-items-end">
                <div class="text-muted mb-2">
                    <i class="fas fa-calendar"></i> <?= date('F j, Y') ?>
                </div>
                <a href="/fy_proj/assignments/create.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i>Create Assignment
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Overview -->
<div class="row mb-4">
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon green">
                <i class="fas fa-tasks"></i>
            </div>
            <h3 class="text-success mb-2"><?= $assignments_count ?></h3>
            <p class="mb-1">My Assignments</p>
            <small class="text-muted">Created by you</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon blue">
                <i class="fas fa-users"></i>
            </div>
            <h3 class="text-primary mb-2"><?= $students_count ?></h3>
            <p class="mb-1">Total Students</p>
            <small class="text-muted">In the system</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon amber">
                <i class="fas fa-question-circle"></i>
            </div>
            <h3 style="color: #d97706;" class="mb-2"><?= $quizzes_count ?></h3>
            <p class="mb-1">My Quizzes</p>
            <small class="text-muted">Created by you</small>
        </div>
    </div>
    
    <div class="col-lg-3 col-md-6">
        <div class="stats-card">
            <div class="stats-icon teal">
                <i class="fas fa-comments"></i>
            </div>
            <h3 style="color: #0d9488;" class="mb-2"><?= $notices_count ?></h3>
            <p class="mb-1">Notices Posted</p>
            <small class="text-muted">By you</small>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="row">
    <div class="col-lg-8">
        <!-- Quick Actions -->
        <div class="content-card">
            <h3 class="mb-4">
                <i class="fas fa-bolt me-2"></i>Quick Actions
            </h3>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon amber me-3">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Create Quiz</h6>
                            <p class="mb-2 text-muted small">Create assessments for students</p>
                            <a href="/fy_proj/quizzes/index.php" class="btn btn-outline-primary btn-sm">Create Now</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon teal me-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Messages</h6>
                            <p class="mb-2 text-muted small">Chat with students and colleagues</p>
                            <a href="/fy_proj/chat/index.php" class="btn btn-outline-primary btn-sm">Open Chat</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon green me-3">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Assignments</h6>
                            <p class="mb-2 text-muted small">Create and manage assignments</p>
                            <a href="/fy_proj/assignments/index.php" class="btn btn-outline-primary btn-sm">Manage</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon blue me-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">Attendance</h6>
                            <p class="mb-2 text-muted small">Track student attendance</p>
                            <a href="/fy_proj/attendance/index.php" class="btn btn-outline-primary btn-sm">View</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- System Tools -->
        <div class="professional-card">
            <h3 style="color: var(--dark-gray); font-weight: 600; margin-bottom: 25px;">
                <i class="fas fa-tools me-2"></i>Teaching Tools
            </h3>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 border rounded-3">
                        <div class="stats-icon purple mb-3" style="width: 45px; height: 45px; font-size: 1.1rem; margin: 0 auto;">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h6 style="color: var(--dark-gray);">Timetable</h6>
                        <p class="text-muted" style="font-size: 0.85rem;">Schedule management</p>
                        <a href="/fy_proj/timetable/index.php" class="btn btn-sm btn-outline-secondary">Access</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 border rounded-3">
                        <div class="stats-icon red mb-3" style="width: 45px; height: 45px; font-size: 1.1rem; margin: 0 auto;">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h6 style="color: var(--dark-gray);">Quizzes</h6>
                        <p class="text-muted" style="font-size: 0.85rem;">Create assessments</p>
                        <a href="/fy_proj/quizzes/index.php" class="btn btn-sm btn-outline-secondary">Create</a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <div class="text-center p-3 border rounded-3">
                        <div class="stats-icon teal mb-3" style="width: 45px; height: 45px; font-size: 1.1rem; margin: 0 auto;">
                            <i class="fas fa-folder-open"></i>
                        </div>
                        <h6 style="color: var(--dark-gray);">Materials</h6>
                        <p class="text-muted" style="font-size: 0.85rem;">Upload resources</p>
                        <a href="/fy_proj/study_material/index.php" class="btn btn-sm btn-outline-secondary">Upload</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Sidebar -->
    <div class="col-lg-4">
        <!-- Recent Assignments -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-tasks me-2"></i>My Recent Assignments
            </h4>
            
            <?php if ($recent_assignments->num_rows > 0): ?>
                <?php while ($assignment = $recent_assignments->fetch_assoc()): ?>
                <div class="border rounded-3 p-3 mb-3" style="background: #f8fafc;">
                    <h6 class="mb-2"><?= htmlspecialchars($assignment['title']) ?></h6>
                    <?php if (!empty($assignment['description'])): ?>
                    <p class="text-muted small mb-2">
                        <?= htmlspecialchars(substr($assignment['description'], 0, 60)) ?>
                        <?= strlen($assignment['description']) > 60 ? '...' : '' ?>
                    </p>
                    <?php endif; ?>
                    <small class="text-muted">
                        <i class="fas fa-calendar me-1"></i><?= date('M j, Y', strtotime($assignment['upload_date'])) ?>
                    </small>
                </div>
                <?php endwhile; ?>
                
                <div class="text-center mt-3">
                    <a href="/fy_proj/assignments/index.php" class="btn btn-outline-primary btn-sm">
                        View All Assignments
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-tasks fa-2x text-muted mb-3 opacity-50"></i>
                    <p class="text-muted">No assignments created yet</p>
                    <a href="/fy_proj/assignments/create.php" class="btn btn-success btn-sm">Create Your First Assignment</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Teaching Overview -->
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-chart-bar me-2"></i>Teaching Overview
            </h4>
            
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">Total Students</span>
                    <strong><?= $students_count ?></strong>
                </div>
            </div>
            
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">My Assignments</span>
                    <strong><?= $assignments_count ?></strong>
                </div>
            </div>
            
            <div class="mb-3 pb-3 border-bottom">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted">My Quizzes</span>
                    <strong><?= $quizzes_count ?></strong>
                </div>
            </div>
            
            <div class="row">
                <div class="col-6">
                    <a href="/fy_proj/notices/index.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-bullhorn me-1"></i>Notices
                    </a>
                </div>
                <div class="col-6">
                    <a href="/fy_proj/study_material/index.php" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-upload me-1"></i>Materials
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
