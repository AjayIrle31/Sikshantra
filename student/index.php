<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['student']); 
require_once "../includes/natural_header.php";

$student_id = $_SESSION['user']['user_id'];
$student_name = $_SESSION['user']['name'];

// Get total assignments count
$assignment_count_query = "SELECT COUNT(*) as count FROM assignments";
$assignment_stmt = $conn->prepare($assignment_count_query);
$assignment_stmt->execute();
$assignment_count = $assignment_stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get student's quiz attempts
$quiz_count_query = "SELECT COUNT(*) as count FROM quiz_results WHERE student_id = ?";
$quiz_count_stmt = $conn->prepare($quiz_count_query);
$quiz_count_stmt->bind_param("i", $student_id);
$quiz_count_stmt->execute();
$quiz_count = $quiz_count_stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get student's submissions
$submission_count_query = "SELECT COUNT(*) as count FROM submissions WHERE student_id = ?";
$submission_stmt = $conn->prepare($submission_count_query);
$submission_stmt->bind_param("i", $student_id);
$submission_stmt->execute();
$submission_count = $submission_stmt->get_result()->fetch_assoc()['count'] ?? 0;

// Get recent assignments for activity feed
$recent_assignments_query = "SELECT a.title, a.upload_date, u.name as creator_name 
                            FROM assignments a 
                            LEFT JOIN users u ON a.uploaded_by = u.user_id 
                            ORDER BY a.upload_date DESC 
                            LIMIT 5";
$recent_stmt = $conn->prepare($recent_assignments_query);
$recent_stmt->execute();
$recent_assignments = $recent_stmt->get_result();

// Get student's attendance stats
$attendance_query = "SELECT COUNT(*) as total, 
                    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present 
                    FROM attendance WHERE student_id = ?";
$attendance_stmt = $conn->prepare($attendance_query);
$attendance_stmt->bind_param("i", $student_id);
$attendance_stmt->execute();
$attendance_stats = $attendance_stmt->get_result()->fetch_assoc();
$attendance_percentage = $attendance_stats['total'] > 0 ? round(($attendance_stats['present'] / $attendance_stats['total']) * 100) : 100;

// Dynamic motivational content
$motivational_quotes = [
    ["quote" => "Success is the sum of small efforts repeated day in and day out.", "author" => "Robert Collier"],
    ["quote" => "The beautiful thing about learning is that no one can take it away from you.", "author" => "B.B. King"],
    ["quote" => "Education is not preparation for life; education is life itself.", "author" => "John Dewey"],
    ["quote" => "The expert in anything was once a beginner.", "author" => "Helen Hayes"],
    ["quote" => "Learning never exhausts the mind.", "author" => "Leonardo da Vinci"],
    ["quote" => "The more that you read, the more things you will know. The more that you learn, the more places you'll go.", "author" => "Dr. Seuss"],
    ["quote" => "Investment in knowledge pays the best interest.", "author" => "Benjamin Franklin"],
    ["quote" => "The capacity to learn is a gift; the ability to learn is a skill; the willingness to learn is a choice.", "author" => "Brian Herbert"]
];

$study_tips = [
    "Create a dedicated study space free from distractions",
    "Break large tasks into smaller, manageable chunks",
    "Use the Pomodoro Technique: 25 minutes of focus, 5-minute break",
    "Review your notes within 24 hours of taking them",
    "Form study groups to discuss and explain concepts",
    "Stay consistent with your study schedule",
    "Take regular breaks to avoid burnout",
    "Use active recall instead of passive reading",
    "Create mind maps to visualize connections between concepts",
    "Get adequate sleep - it's crucial for memory consolidation",
    "Practice past exams and quiz questions regularly",
    "Teach concepts to others to reinforce your understanding"
];

// Select random quote and tip based on the current date
$day_of_year = date('z'); // 0-365
$selected_quote = $motivational_quotes[$day_of_year % count($motivational_quotes)];
$selected_tip = $study_tips[$day_of_year % count($study_tips)];
?>

<!-- Welcome Section -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                Welcome back, <?= htmlspecialchars($student_name) ?>!
            </h1>
            <p class="page-subtitle">
                Track your academic progress and stay on top of your studies.
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon blue">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-lg-4 col-md-6">
        <div class="stats-card">
            <div class="stats-icon blue">
                <i class="fas fa-tasks"></i>
            </div>
            <h3 class="text-primary mb-2"><?= $assignment_count ?></h3>
            <p class="mb-1">Total Assignments</p>
            <small class="text-muted">Available to complete</small>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stats-card">
            <div class="stats-icon green">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3 class="text-success mb-2"><?= $submission_count ?></h3>
            <p class="mb-1">Completed</p>
            <small class="text-muted">Submitted work</small>
        </div>
    </div>
    <div class="col-lg-4 col-md-6">
        <div class="stats-card">
            <div class="stats-icon purple">
                <i class="fas fa-calendar-check"></i>
            </div>
            <h3 style="color: #7c3aed;" class="mb-2"><?= $attendance_percentage ?>%</h3>
            <p class="mb-1">Attendance</p>
            <small class="text-muted">Class participation</small>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row">
    <div class="col-lg-8">
        <!-- Track My Progress Section -->
        <div class="content-card progress-card" style="background: linear-gradient(145deg, #10b981 0%, #059669 100%); color: white; border: none; box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3); margin-bottom: 1.5rem;">
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-chart-line progress-icon"></i>
                </div>
                <h5 class="mb-2 fw-bold progress-title">Track My Progress</h5>
                <p class="mb-3 progress-subtitle opacity-90">Monitor your academic journey and achievements!</p>
                
                <!-- Progress indicators -->
                <div class="row text-center progress-indicators">
                    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                        <div class="progress-item-bg rounded-3 p-2 progress-item">
                            <i class="fas fa-tasks mb-1 progress-item-icon" style="color: #ffffff; font-size: 1.2rem;"></i>
                            <div class="progress-item-value fw-bold" style="color: #ffffff; font-size: 1rem;"><?= $submission_count ?>/<?= $assignment_count ?></div>
                            <small class="d-block progress-item-label" style="color: #f1f5f9; opacity: 0.9;">Assignments</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                        <div class="progress-item-bg rounded-3 p-2 progress-item">
                            <i class="fas fa-brain mb-1 progress-item-icon" style="color: #ffffff; font-size: 1.2rem;"></i>
                            <div class="progress-item-value fw-bold" style="color: #ffffff; font-size: 1rem;"><?= $quiz_count ?></div>
                            <small class="d-block progress-item-label" style="color: #f1f5f9; opacity: 0.9;">Quizzes</small>
                        </div>
                    </div>
                    <div class="col-12 col-sm-4 mb-2 mb-sm-0">
                        <div class="progress-item-bg rounded-3 p-2 progress-item">
                            <i class="fas fa-calendar-check mb-1 progress-item-icon" style="color: #ffffff; font-size: 1.2rem;"></i>
                            <div class="progress-item-value fw-bold" style="color: #ffffff; font-size: 1rem;"><?= $attendance_percentage ?>%</div>
                            <small class="d-block progress-item-label" style="color: #f1f5f9; opacity: 0.9;">Attendance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="content-card">
            <h2 class="mb-4">
                <i class="fas fa-th-large me-2"></i>Learning Hub
            </h2>
            
            <div class="row">
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon blue mb-3">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <h5>My Attendance</h5>
                        <p class="text-muted small mb-3">Monitor your class attendance and track your progress</p>
                        <a href="/fy_proj/attendance/index.php" class="btn btn-outline-primary btn-sm">View Attendance</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon green mb-3">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h5>Communication</h5>
                        <p class="text-muted small mb-3">Connect with classmates and instructors</p>
                        <a href="/fy_proj/chat/index.php" class="btn btn-outline-primary btn-sm">Open Chat</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon amber mb-3">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h5>Assignments</h5>
                        <p class="text-muted small mb-3">View and submit your coursework</p>
                        <a href="/fy_proj/assignments/index.php" class="btn btn-outline-primary btn-sm">View All</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon purple mb-3">
                            <i class="fas fa-calendar-alt"></i>
                        </div>
                        <h5>Schedule</h5>
                        <p class="text-muted small mb-3">View your class timetable and important dates</p>
                        <a href="/fy_proj/timetable/index.php" class="btn btn-outline-primary btn-sm">View Schedule</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon teal mb-3">
                            <i class="fas fa-question-circle"></i>
                        </div>
                        <h5>Assessments</h5>
                        <p class="text-muted small mb-3">Take quizzes and track your performance</p>
                        <a href="/fy_proj/quizzes/index.php" class="btn btn-outline-primary btn-sm">Start Quiz</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon blue mb-3">
                            <i class="fas fa-book"></i>
                        </div>
                        <h5>Resources</h5>
                        <p class="text-muted small mb-3">Access study materials and course content</p>
                        <a href="/fy_proj/study_material/index.php" class="btn btn-outline-primary btn-sm">Browse Resources</a>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="text-center p-3 border rounded-3" style="background-color: #f8fafc;">
                        <div class="stats-icon red mb-3">
                            <i class="fas fa-bell"></i>
                        </div>
                        <h5>Notices</h5>
                        <p class="text-muted small mb-3">View important announcements and updates</p>
                        <a href="/fy_proj/notices/index.php" class="btn btn-outline-primary btn-sm">View Notices</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Sidebar -->
    <div class="col-lg-4">
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-clock me-2"></i>Recent Activity
            </h4>
            
            <?php if ($recent_assignments->num_rows > 0): ?>
                <?php while ($assignment = $recent_assignments->fetch_assoc()): ?>
                <div class="border-start border-primary border-3 ps-3 mb-3">
                    <h6 class="mb-1"><?= htmlspecialchars($assignment['title']) ?></h6>
                    <small class="text-muted">
                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($assignment['creator_name'] ?: 'System') ?><br>
                        <i class="fas fa-calendar me-1"></i><?= date('M j, Y', strtotime($assignment['upload_date'])) ?>
                    </small>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-inbox fa-2x mb-2 opacity-50"></i>
                    <p>No recent assignments</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Study Tip Card - Enhanced Design -->
        <div class="content-card position-relative overflow-hidden" 
             style="background: linear-gradient(145deg, #667eea 0%, #764ba2 100%); 
                    color: white; 
                    border: none; 
                    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);">
            
            <!-- Decorative Elements -->
            <div class="position-absolute top-0 end-0 opacity-25" style="transform: translate(20px, -20px);">
                <i class="fas fa-graduation-cap" style="font-size: 4rem;"></i>
            </div>
            <div class="position-absolute bottom-0 start-0 opacity-15" style="transform: translate(-15px, 15px);">
                <i class="fas fa-book-open" style="font-size: 3rem;"></i>
            </div>
            
            <!-- Content -->
            <div class="position-relative z-index-1">
                <!-- Header with Icon -->
                <div class="d-flex align-items-center mb-3">
                    <div class="bg-white bg-opacity-20 rounded-circle p-2 me-3">
                        <i class="fas fa-lightbulb text-warning" style="font-size: 1.2rem;"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold">Daily Wisdom</h5>
                        <small class="opacity-75"><?= date('M j, Y') ?></small>
                    </div>
                </div>
                
                <!-- Quote Section -->
                <div class="bg-white bg-opacity-10 rounded-3 p-3 mb-3" style="backdrop-filter: blur(10px);">
                    <div class="d-flex">
                        <i class="fas fa-quote-left text-warning me-2 mt-1" style="font-size: 1.2rem;"></i>
                        <div class="flex-grow-1">
                            <p class="mb-2 fst-italic">
                                <?= htmlspecialchars($selected_quote['quote']) ?>
                            </p>
                            <small class="opacity-75 fw-medium">
                                — <?= htmlspecialchars($selected_quote['author']) ?>
                            </small>
                        </div>
                    </div>
                </div>
                
                <!-- Study Tip Section -->
                <div class="border border-white border-opacity-25 rounded-3 p-3">
                    <div class="d-flex align-items-start">
                        <div class="bg-success bg-opacity-20 rounded-circle p-2 me-3 mt-1">
                            <i class="fas fa-brain text-success" style="font-size: 0.9rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1" style="color: #fbbf24;">💡 Today's Study Tip</h6>
                            <p class="mb-0 small lh-base" style="color: #ffffff;">
                                <?= htmlspecialchars($selected_tip) ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Action Card -->
        <div class="content-card" style="background: linear-gradient(145deg, #4facfe 0%, #00f2fe 100%); color: white; border: none; box-shadow: 0 8px 25px rgba(79, 172, 254, 0.3);">
            <div class="text-center">
                <div class="mb-3">
                    <i class="fas fa-rocket" style="font-size: 2rem;"></i>
                </div>
                <h5 class="mb-2 fw-bold">Ready to Learn?</h5>
                <p class="mb-3 small opacity-90">Jump into your next assignment or quiz!</p>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="/fy_proj/assignments/index.php" class="btn btn-light btn-sm">
                        <i class="fas fa-tasks me-1"></i>Tasks
                    </a>
                    <a href="/fy_proj/quizzes/index.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-brain me-1"></i>Quiz
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Student-specific styles */
.progress-card {
    min-height: 280px;
}

.progress-icon {
    font-size: 2rem;
    transition: transform 0.3s ease;
}

.progress-title {
    font-size: 1.25rem;
}

.progress-subtitle {
    font-size: 0.9rem;
}

.progress-item {
    transition: all 0.3s ease;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.progress-item-bg {
    background: rgba(255, 255, 255, 0.25) !important;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

/* Ensure all text in progress items is always visible */
.progress-item * {
    color: #ffffff !important;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
}

.progress-item small {
    color: #f1f5f9 !important;
    opacity: 0.9 !important;
}

.progress-item:hover {
    background-color: rgba(255, 255, 255, 0.4) !important;
    transform: translateY(-2px);
    border-color: rgba(255, 255, 255, 0.5) !important;
}

/* Progress item styling now handled inline for better visibility */

/* Mobile-first responsive adjustments */
@media (max-width: 575px) {
    .progress-card {
        min-height: auto;
        padding: 1.25rem;
    }
    
    .progress-icon {
        font-size: 1.75rem;
    }
    
    .progress-title {
        font-size: 1.1rem;
    }
    
    .progress-subtitle {
        font-size: 0.85rem;
        margin-bottom: 1rem !important;
    }
    
    .progress-indicators {
        margin: 1rem 0;
    }
    
    .progress-item {
        min-height: 70px;
        margin-bottom: 0.75rem;
        padding: 0.75rem !important;
    }
    
    .progress-item-bg {
        background: rgba(255, 255, 255, 0.3) !important;
        border: 1px solid rgba(255, 255, 255, 0.4) !important;
    }
    
    .progress-item .progress-item-icon {
        font-size: 1.1rem !important;
        color: #ffffff !important;
    }
    
    .progress-item .progress-item-value {
        font-size: 0.95rem !important;
        color: #ffffff !important;
    }
    
    .progress-item .progress-item-label {
        font-size: 0.7rem !important;
        color: #f1f5f9 !important;
        opacity: 0.9 !important;
    }
}

/* Tablet adjustments */
@media (min-width: 576px) and (max-width: 991px) {
    .progress-card {
        min-height: 250px;
    }
    
    .progress-item {
        min-height: 75px;
    }
    
    .progress-indicators .col-sm-4:last-child {
        margin-top: 0.5rem;
    }
}

/* Desktop adjustments */
@media (min-width: 992px) {
    .progress-card, 
    .content-card[style*="#4facfe"] {
        min-height: 300px;
        display: flex;
        align-items: center;
    }
    
    .progress-card .text-center,
    .content-card[style*="#4facfe"] .text-center {
        width: 100%;
    }
    
    .progress-indicators {
        margin: 1.5rem 0;
    }
}

/* Student-specific button styles */
.btn-light:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.btn-outline-light:hover {
    background-color: rgba(255,255,255,0.2) !important;
}
</style>

<?php require_once "../includes/natural_footer.php"; ?>
