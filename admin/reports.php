<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin']); 
require_once "../includes/natural_header.php";

// Get date range from request (default to last 30 days)
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-30 days'));

// User Statistics
$total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
$admin_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'")->fetch_assoc()['count'];
$teacher_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")->fetch_assoc()['count'];
$student_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];

// Content Statistics
$assignments_count = $conn->query("SELECT COUNT(*) as count FROM assignments")->fetch_assoc()['count'];
$quizzes_count = $conn->query("SELECT COUNT(*) as count FROM quizzes")->fetch_assoc()['count'];
$notices_count = $conn->query("SELECT COUNT(*) as count FROM notices")->fetch_assoc()['count'];
$study_materials_count = $conn->query("SELECT COUNT(*) as count FROM study_material")->fetch_assoc()['count'];

// Activity Statistics (within date range)
$submissions_in_range = $conn->query("SELECT COUNT(*) as count FROM submissions WHERE submitted_on BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'")->fetch_assoc()['count'];
$quiz_attempts_in_range = $conn->query("SELECT COUNT(*) as count FROM quiz_results WHERE attempted_on BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'")->fetch_assoc()['count'];
$notices_in_range = $conn->query("SELECT COUNT(*) as count FROM notices WHERE posted_on BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'")->fetch_assoc()['count'];

// Top Performing Students (based on quiz scores)
$top_students_query = "SELECT u.name, u.email, AVG(qr.score) as avg_score, COUNT(qr.quiz_id) as quiz_attempts 
                      FROM users u 
                      JOIN quiz_results qr ON u.user_id = qr.student_id 
                      WHERE u.role = 'student' 
                      GROUP BY u.user_id 
                      HAVING quiz_attempts > 0 
                      ORDER BY avg_score DESC 
                      LIMIT 10";
$top_students_result = $conn->query($top_students_query);

// Most Active Teachers (based on content creation)
$active_teachers_query = "SELECT u.name, u.email, 
                         (SELECT COUNT(*) FROM assignments WHERE uploaded_by = u.user_id) as assignments_created,
                         (SELECT COUNT(*) FROM quizzes WHERE created_by = u.user_id) as quizzes_created,
                         (SELECT COUNT(*) FROM study_material WHERE uploaded_by = u.user_id) as materials_uploaded
                         FROM users u 
                         WHERE u.role = 'teacher' 
                         ORDER BY (assignments_created + quizzes_created + materials_uploaded) DESC 
                         LIMIT 10";
$active_teachers_result = $conn->query($active_teachers_query);

// Recent Activity Summary
$seven_days_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
$recent_assignments = $conn->query("SELECT COUNT(*) as count FROM assignments WHERE upload_date >= '$seven_days_ago'")->fetch_assoc()['count'];
$recent_submissions = $conn->query("SELECT COUNT(*) as count FROM submissions WHERE submitted_on >= '$seven_days_ago'")->fetch_assoc()['count'];
$recent_notices = $conn->query("SELECT COUNT(*) as count FROM notices WHERE posted_on >= '$seven_days_ago'")->fetch_assoc()['count'];

// Attendance Statistics (if data exists)
$attendance_stats = $conn->query("SELECT 
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_count,
    SUM(CASE WHEN status = 'absent' THEN 1 ELSE 0 END) as absent_count,
    COUNT(*) as total_records
    FROM attendance 
    WHERE date BETWEEN '$start_date' AND '$end_date'")->fetch_assoc();

$attendance_percentage = $attendance_stats['total_records'] > 0 ? 
    round(($attendance_stats['present_count'] / $attendance_stats['total_records']) * 100, 2) : 0;
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-chart-line me-2"></i>System Reports & Analytics
            </h1>
            <p class="page-subtitle">
                Comprehensive overview of system performance, user activity, and key metrics
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="btn-group">
                <button onclick="window.print()" class="btn btn-outline-primary">
                    <i class="fas fa-print me-2"></i>Print Report
                </button>
                <button onclick="exportToCSV()" class="btn btn-primary">
                    <i class="fas fa-download me-2"></i>Export CSV
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="content-card mb-4">
    <h5 class="mb-3"><i class="fas fa-calendar-range me-2"></i>Date Range Filter</h5>
    <form method="GET" class="row align-items-end">
        <div class="col-md-4 mb-2">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" class="form-control" name="start_date" value="<?php echo $start_date; ?>">
        </div>
        <div class="col-md-4 mb-2">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" class="form-control" name="end_date" value="<?php echo $end_date; ?>">
        </div>
        <div class="col-md-4 mb-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-filter me-2"></i>Apply Filter
            </button>
        </div>
    </form>
</div>

<!-- System Overview Metrics -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-chart-bar me-2"></i>System Overview
        </h4>
        <span class="badge bg-danger">Period: <?php echo date('M j', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?></span>
    </div>
    
    <div class="row">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon red mb-3">
                    <i class="fas fa-users"></i>
                </div>
                <h3 class="mb-1"><?php echo $total_users; ?></h3>
                <p class="text-muted small mb-0">Total Users</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon blue mb-3">
                    <i class="fas fa-tasks"></i>
                </div>
                <h3 class="mb-1"><?php echo $assignments_count; ?></h3>
                <p class="text-muted small mb-0">Total Assignments</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon purple mb-3">
                    <i class="fas fa-question-circle"></i>
                </div>
                <h3 class="mb-1"><?php echo $quizzes_count; ?></h3>
                <p class="text-muted small mb-0">Total Quizzes</p>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="stats-card">
                <div class="stats-icon green mb-3">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3 class="mb-1"><?php echo $attendance_percentage; ?>%</h3>
                <p class="text-muted small mb-0">Attendance Rate</p>
            </div>
        </div>
    </div>
</div>

<!-- User Distribution Chart -->
<div class="content-card">
    <h4 class="mb-4">
        <i class="fas fa-chart-pie me-2"></i>User Distribution
    </h4>
    
    <div class="row">
        <div class="col-md-6">
            <canvas id="userDistributionChart" style="max-height: 300px;"></canvas>
        </div>
        <div class="col-md-6">
            <div class="row">
                <div class="col-6 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon green">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h4><?php echo $teacher_count; ?></h4>
                        <small class="text-muted">Teachers</small>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon blue">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h4><?php echo $student_count; ?></h4>
                        <small class="text-muted">Students</small>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon amber">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <h4><?php echo $admin_count; ?></h4>
                        <small class="text-muted">Admins</small>
                    </div>
                </div>
                <div class="col-6 mb-3">
                    <div class="stats-card text-center">
                        <div class="stats-icon teal">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <h4><?php echo $study_materials_count; ?></h4>
                        <small class="text-muted">Materials</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Summary -->
<div class="row">
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-chart-line me-2"></i>Recent Activity (Last 7 Days)
            </h4>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>New Assignments</span>
                    <span class="badge bg-primary"><?php echo $recent_assignments; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-primary" style="width: <?php echo min(($recent_assignments / max($assignments_count, 1)) * 100, 100); ?>%;"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Assignment Submissions</span>
                    <span class="badge bg-success"><?php echo $recent_submissions; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-success" style="width: <?php echo min(($recent_submissions / max($submissions_in_range, 1)) * 100, 100); ?>%;"></div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>New Notices</span>
                    <span class="badge bg-info"><?php echo $recent_notices; ?></span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-info" style="width: <?php echo min(($recent_notices / max($notices_count, 1)) * 100, 100); ?>%;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-calendar-alt me-2"></i>Period Activity
            </h4>
            <p class="text-muted small mb-3"><?php echo date('M j', strtotime($start_date)); ?> - <?php echo date('M j, Y', strtotime($end_date)); ?></p>
            
            <canvas id="activityChart" style="max-height: 300px;"></canvas>
        </div>
    </div>
</div>

<!-- Top Performers -->
<div class="row">
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-trophy me-2"></i>Top Performing Students
            </h4>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Student</th>
                            <th>Avg Score</th>
                            <th>Attempts</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($top_students_result && $top_students_result->num_rows > 0): ?>
                            <?php while ($student = $top_students_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($student['email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo round($student['avg_score'], 1); ?>%</td>
                                <td><?php echo $student['quiz_attempts']; ?></td>
                                <td>
                                    <?php 
                                    $score = $student['avg_score'];
                                    if ($score >= 90) echo '<span class="badge bg-success">Excellent</span>';
                                    elseif ($score >= 75) echo '<span class="badge bg-primary">Good</span>';
                                    elseif ($score >= 60) echo '<span class="badge bg-warning">Average</span>';
                                    else echo '<span class="badge bg-danger">Needs Help</span>';
                                    ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No quiz data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-user-check me-2"></i>Most Active Teachers
            </h4>
            
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Teacher</th>
                            <th>Assignments</th>
                            <th>Quizzes</th>
                            <th>Materials</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($active_teachers_result && $active_teachers_result->num_rows > 0): ?>
                            <?php while ($teacher = $active_teachers_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($teacher['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($teacher['email']); ?></small>
                                    </div>
                                </td>
                                <td><?php echo $teacher['assignments_created']; ?></td>
                                <td><?php echo $teacher['quizzes_created']; ?></td>
                                <td><?php echo $teacher['materials_uploaded']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center text-muted">No teacher activity data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// User Distribution Pie Chart
const userCtx = document.getElementById('userDistributionChart').getContext('2d');
new Chart(userCtx, {
    type: 'doughnut',
    data: {
        labels: ['Teachers', 'Students', 'Administrators'],
        datasets: [{
            data: [<?php echo $teacher_count; ?>, <?php echo $student_count; ?>, <?php echo $admin_count; ?>],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(14, 165, 233, 0.8)',
                'rgba(245, 158, 11, 0.8)'
            ],
            borderColor: [
                'rgba(16, 185, 129, 1)',
                'rgba(14, 165, 233, 1)',
                'rgba(245, 158, 11, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});

// Activity Bar Chart
const activityCtx = document.getElementById('activityChart').getContext('2d');
new Chart(activityCtx, {
    type: 'bar',
    data: {
        labels: ['Submissions', 'Quiz Attempts', 'New Notices'],
        datasets: [{
            label: 'Activity Count',
            data: [<?php echo $submissions_in_range; ?>, <?php echo $quiz_attempts_in_range; ?>, <?php echo $notices_in_range; ?>],
            backgroundColor: [
                'rgba(16, 185, 129, 0.8)',
                'rgba(14, 165, 233, 0.8)',
                'rgba(6, 182, 212, 0.8)'
            ],
            borderColor: [
                'rgba(16, 185, 129, 1)',
                'rgba(14, 165, 233, 1)',
                'rgba(6, 182, 212, 1)'
            ],
            borderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});

// Export to CSV function
function exportToCSV() {
    const csvData = [
        ['Report Type', 'Value'],
        ['Total Users', '<?php echo $total_users; ?>'],
        ['Teachers', '<?php echo $teacher_count; ?>'],
        ['Students', '<?php echo $student_count; ?>'],
        ['Administrators', '<?php echo $admin_count; ?>'],
        ['Total Assignments', '<?php echo $assignments_count; ?>'],
        ['Total Quizzes', '<?php echo $quizzes_count; ?>'],
        ['Study Materials', '<?php echo $study_materials_count; ?>'],
        ['Attendance Rate', '<?php echo $attendance_percentage; ?>%'],
        ['Submissions (Period)', '<?php echo $submissions_in_range; ?>'],
        ['Quiz Attempts (Period)', '<?php echo $quiz_attempts_in_range; ?>'],
        ['Notices (Period)', '<?php echo $notices_in_range; ?>']
    ];
    
    const csvContent = csvData.map(row => row.join(',')).join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.setAttribute('hidden', '');
    a.setAttribute('href', url);
    a.setAttribute('download', 'system_report_<?php echo date('Y-m-d'); ?>.csv');
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php require_once "../includes/natural_footer.php"; ?>
