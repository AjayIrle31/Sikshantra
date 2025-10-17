<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$role = $_SESSION['user']['role']; 
$user_id = $_SESSION['user']['user_id'];

// Handle attendance marking for teachers/admins
if($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['admin', 'teacher'])) {
    $date = $_POST['date'];
    $success = true;
    
    // Check if attendance already marked for this date
    $existing = $conn->query("SELECT COUNT(*) as count FROM attendance WHERE date='$date' LIMIT 1")->fetch_assoc()['count'];
    if($existing > 0) {
        // Delete existing entries for this date to update
        $conn->query("DELETE FROM attendance WHERE date='$date'");
    }
    
    foreach($_POST['status'] ?? [] as $sid => $st) {
        $sid = intval($sid);
        $st = $st === 'present' ? 'present' : 'absent';
        $stmt = $conn->prepare("INSERT INTO attendance(student_id,date,status,marked_by) VALUES(?,?,?,?)");
        $stmt->bind_param("issi", $sid, $date, $st, $user_id);
        if(!$stmt->execute()) {
            $success = false;
        }
    }
    
    if($success) {
        echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>Attendance marked successfully for ' . date('M j, Y', strtotime($date)) . '!</div>';
    } else {
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error marking attendance. Please try again.</div>';
    }
}

$students = $conn->query("SELECT user_id,name FROM users WHERE role='student' ORDER BY name");

// Get current user's attendance if they are a student
if($role === 'student') {
    $myAttendance = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE student_id=$user_id")->fetch_assoc()['total'];
    $myPresent = $conn->query("SELECT COUNT(*) as present FROM attendance WHERE student_id=$user_id AND status='present'")->fetch_assoc()['present'];
    $myPercentage = $myAttendance > 0 ? round(($myPresent / $myAttendance) * 100, 1) : 0;
}
?>

<!-- Page Header -->
<?php if ($role == 'student'): ?>
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-calendar-check me-3"></i>My Attendance
            </h1>
            <p class="page-subtitle">
                Track your class attendance and stay on top of your academic progress!
            </p>
        </div>
        <div class="col-lg-4 text-center">
            <div class="stats-icon green" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto;">
                <i class="fas fa-check"></i>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-2">
                <i class="fas fa-users me-3"></i>Attendance Management
            </h1>
            <p class="page-subtitle mb-0">Mark and track student attendance</p>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($role == 'student'): ?>
<!-- Student Attendance Summary -->
<div class="content-card mb-4">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h4 class="mb-3">
                <i class="fas fa-chart-line me-2"></i>Your Attendance Summary
            </h4>
            <div class="row text-center g-3">
                <div class="col-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #16a34a, #10b981); color: white; border: none;">
                        <div class="stats-icon green mb-2" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-check"></i>
                        </div>
                        <h3 class="mb-1"><?= $myPresent ?></h3>
                        <small>Present Days</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #dc2626, #ea580c); color: white; border: none;">
                        <div class="stats-icon red mb-2" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-times"></i>
                        </div>
                        <h3 class="mb-1"><?= $myAttendance - $myPresent ?></h3>
                        <small>Absent Days</small>
                    </div>
                </div>
                <div class="col-4">
                    <div class="stats-card" style="background: linear-gradient(135deg, #3b82f6, #06b6d4); color: white; border: none;">
                        <div class="stats-icon blue mb-2" style="background: rgba(255,255,255,0.2); color: white;">
                            <i class="fas fa-percentage"></i>
                        </div>
                        <h3 class="mb-1"><?= $myPercentage ?>%</h3>
                        <small>Attendance Rate</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <?php if($myPercentage >= 80): ?>
                <div class="stats-icon green mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; margin: 0 auto;">
                    <i class="fas fa-trophy"></i>
                </div>
                <p style="color: #16a34a; font-weight: bold; font-size: 1.1rem;">Excellent!</p>
            <?php elseif($myPercentage >= 70): ?>
                <div class="stats-icon amber mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; margin: 0 auto;">
                    <i class="fas fa-thumbs-up"></i>
                </div>
                <p style="color: #d97706; font-weight: bold; font-size: 1.1rem;">Good Job!</p>
            <?php else: ?>
                <div class="stats-icon red mb-3" style="width: 80px; height: 80px; font-size: 2.5rem; margin: 0 auto;">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <p style="color: #dc2626; font-weight: bold; font-size: 1.1rem;">Keep Improving!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Student Attendance History -->
<div class="content-card">
    <h4 class="mb-4">
        <i class="fas fa-calendar-check me-2"></i>Attendance History
    </h4>
    
    <?php 
    $myHistory = $conn->query("SELECT date, status FROM attendance WHERE student_id=$user_id ORDER BY date DESC LIMIT 20");
    if($myHistory->num_rows > 0):
    ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Day</th>
                </tr>
            </thead>
            <tbody>
                <?php while($record = $myHistory->fetch_assoc()): ?>
                <tr>
                    <td><?= date('M j, Y', strtotime($record['date'])) ?></td>
                    <td>
                        <?php if($record['status'] == 'present'): ?>
                            <span class="badge bg-success">✓ Present</span>
                        <?php else: ?>
                            <span class="badge bg-danger">✗ Absent</span>
                        <?php endif; ?>
                    </td>
                    <td><?= date('l', strtotime($record['date'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-4">
        <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;">📅</div>
        <h5 style="color: var(--secondary-gray);">No Attendance Records</h5>
        <p class="text-muted">Your attendance will appear here once marked by teachers.</p>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- Teacher/Admin View -->
<div class="row g-4">
    <!-- Mark Attendance -->
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-check-square me-2"></i>Mark Attendance
            </h4>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Date</label>
                    <input type="date" name="date" class="form-control" 
                           value="<?= date('Y-m-d') ?>" required>
                    <small class="form-text text-muted">Select the date for attendance marking</small>
                </div>
                
                <?php if($students->num_rows > 0): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold; margin-bottom: 15px;">Students</label>
                    <div style="max-height: 400px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 5px; padding: 10px;">
                        <table class="table table-sm">
                            <thead class="sticky-top" style="background: white;">
                                <tr>
                                    <th>Student Name</th>
                                    <th width="30%">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($s = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['name']) ?></td>
                                    <td>
                                        <select class="form-select form-select-sm" 
                                                name="status[<?= $s['user_id'] ?>]" required>
                                            <option value="present" style="color: #28a745;">✓ Present</option>
                                            <option value="absent" style="color: #dc3545;">✗ Absent</option>
                                        </select>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Mark Attendance
                </button>
                <?php else: ?>
                <div class="text-center py-4">
                    <p class="text-muted">No students found in the system.</p>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <!-- Attendance Reports -->
    <div class="col-lg-6">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-chart-line me-2"></i>Student Reports
            </h4>
            
            <form class="mb-3" method="get">
                <div class="d-flex gap-2">
                    <select name="student" class="form-select" required>
                        <option value="">Select a student...</option>
                        <?php 
                        $opts = $conn->query("SELECT user_id,name FROM users WHERE role='student' ORDER BY name");
                        while($o = $opts->fetch_assoc()) {
                            $sel = (isset($_GET['student']) && intval($_GET['student']) == $o['user_id']) ? 'selected' : '';
                            echo "<option value='{$o['user_id']}' $sel>{$o['name']}</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search"></i> View
                    </button>
                </div>
            </form>
            
            <?php 
            $sid = intval($_GET['student'] ?? 0);
            if($sid) {
                $studentName = $conn->query("SELECT name FROM users WHERE user_id=$sid")->fetch_assoc()['name'];
                $total = $conn->query("SELECT COUNT(*) c FROM attendance WHERE student_id=$sid")->fetch_assoc()['c'];
                $present = $conn->query("SELECT COUNT(*) c FROM attendance WHERE student_id=$sid AND status='present'")->fetch_assoc()['c'];
                $perc = $total ? round(($present / $total) * 100, 1) : 0;
            ?>
            
            <div class="alert alert-info">
                <h6 class="mb-3"><i class="fas fa-user me-2"></i><?= htmlspecialchars($studentName) ?></h6>
                <div class="row text-center">
                    <div class="col-4">
                        <strong style="color: #28a745;"><?= $present ?></strong><br>
                        <small>Present</small>
                    </div>
                    <div class="col-4">
                        <strong style="color: #dc3545;"><?= $total - $present ?></strong><br>
                        <small>Absent</small>
                    </div>
                    <div class="col-4">
                        <strong style="color: #1e40af;"><?= $perc ?>%</strong><br>
                        <small>Rate</small>
                    </div>
                </div>
            </div>
            
            <?php 
            $rows = $conn->query("SELECT date,status FROM attendance WHERE student_id=$sid ORDER BY date DESC LIMIT 10");
            if($rows->num_rows > 0):
            ?>
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $rows->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('M j, Y', strtotime($r['date'])) ?></td>
                            <td>
                                <?php if($r['status'] == 'present'): ?>
                                    <span class="badge bg-success">Present</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">Absent</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
            
            <?php } else { ?>
            <div class="text-center py-4">
                <div style="font-size: 2.5rem; opacity: 0.5; margin-bottom: 10px;">📈</div>
                <p class="text-muted">Select a student to view their attendance report</p>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once "../includes/natural_footer.php"; ?>
