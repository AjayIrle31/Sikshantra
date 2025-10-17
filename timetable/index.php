<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$role = $_SESSION['user']['role']; 

if(isset($_GET['del']) && in_array($role, ['admin', 'teacher'])) { 
    $id = intval($_GET['del']); 
    $conn->query("DELETE FROM timetable WHERE timetable_id=$id"); 
    echo '<div class="alert alert-success"><i class="bi bi-trash me-2"></i>Timetable entry deleted successfully!</div>'; 
}

$res = $conn->query("SELECT t.*, u.name as teacher FROM timetable t LEFT JOIN users u ON t.teacher_id=u.user_id ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_slot");

// Group timetable by day for better display
$timetableByDay = [];
while($r = $res->fetch_assoc()) {
    $timetableByDay[$r['day']][] = $r;
}

$dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
?>

<!-- Page Header -->
<?php if ($role == 'student'): ?>
<div class="welcome-card">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 style="color: var(--student-primary); font-weight: bold; margin-bottom: 10px;">
                📅 Class Schedule
            </h1>
            <p style="font-size: 1.2rem; color: var(--secondary-gray); margin-bottom: 0;">
                Your weekly class timetable - never miss a class! ⏰
            </p>
        </div>
        <div class="col-lg-4 text-center">
            <div style="font-size: 4rem; opacity: 0.8;">🕐</div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="professional-card">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-2">Timetable Management</h1>
            <p class="page-subtitle mb-0">Manage class schedules and time slots</p>
        </div>
        <a class="btn btn-teacher" href="/fy_proj/timetable/manage.php">
            <i class="fas fa-plus-circle me-1"></i>Add Schedule
        </a>
    </div>
</div>
<?php endif; ?>

<?php if ($role == 'student'): ?>
<!-- Student View: Weekly Calendar Layout -->
<div class="row g-3">
    <?php foreach($dayOrder as $day): ?>
        <?php if(isset($timetableByDay[$day])): ?>
        <div class="col-lg-6 col-xl-4">
            <div class="fun-card">
                <h5 style="color: var(--student-secondary); font-weight: bold; margin-bottom: 15px; text-align: center;">
                    <?= $day ?>
                </h5>
                <?php foreach($timetableByDay[$day] as $entry): ?>
                <div class="p-2 mb-2" style="background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 227, 194, 0.1)); border-radius: 8px; border-left: 4px solid var(--student-primary);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong style="color: var(--student-primary);"><?= htmlspecialchars($entry['subject']) ?></strong><br>
                            <small style="color: var(--text-secondary);">
                                <i class="fas fa-clock me-1"></i><?= htmlspecialchars($entry['time_slot']) ?>
                            </small>
                            <?php if($entry['teacher']): ?>
                            <br><small style="color: var(--text-secondary);">
                                <i class="fas fa-user me-1"></i><?= htmlspecialchars($entry['teacher']) ?>
                            </small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<?php if(empty($timetableByDay)): ?>
<div class="fun-card text-center">
    <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 20px;">📚</div>
    <h4 style="color: var(--student-secondary);">No Classes Scheduled</h4>
    <p class="text-muted">Your timetable is empty. Check back later for updates!</p>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Teacher/Admin View: Table Layout -->
<div class="professional-card">
    <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
        <i class="fas fa-calendar-week me-2"></i>All Scheduled Classes
        <?php if($res->num_rows > 0): ?>
        <span class="badge" style="background-color: var(--teacher-primary); margin-left: 10px;">
            <?= $res->num_rows ?> entries
        </span>
        <?php endif; ?>
    </h4>
    
    <?php if(count($timetableByDay) > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Day</th>
                    <th>Time Slot</th>
                    <th>Subject</th>
                    <th>Teacher</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                // Reset result for table display
                $res = $conn->query("SELECT t.*, u.name as teacher FROM timetable t LEFT JOIN users u ON t.teacher_id=u.user_id ORDER BY FIELD(day,'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'), time_slot");
                while($r = $res->fetch_assoc()): 
                ?>
                <tr>
                    <td><strong><?= $r['timetable_id'] ?></strong></td>
                    <td>
                        <span class="badge bg-primary"><?= htmlspecialchars($r['day']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($r['time_slot']) ?></td>
                    <td><strong><?= htmlspecialchars($r['subject']) ?></strong></td>
                    <td><?= htmlspecialchars($r['teacher']) ?: '—' ?></td>
                    <td>
                        <a class="btn btn-sm btn-warning me-1" 
                           href="/fy_proj/timetable/manage.php?id=<?= $r['timetable_id'] ?>" 
                           title="Edit Entry">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a class="btn btn-sm btn-danger" 
                           href="/fy_proj/timetable/index.php?del=<?= $r['timetable_id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this timetable entry?')" 
                           title="Delete Entry">
                            <i class="fas fa-trash"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-4">
        <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;">📅</div>
        <h5 style="color: var(--secondary-gray);">No Timetable Entries</h5>
        <p class="text-muted">Create your first timetable entry to get started.</p>
        <a class="btn btn-teacher" href="/fy_proj/timetable/manage.php">
            <i class="fas fa-plus-circle me-1"></i>Add Schedule
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once "../includes/natural_footer.php"; ?>
