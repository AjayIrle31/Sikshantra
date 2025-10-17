<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin', 'teacher']); 
require_once "../includes/natural_header.php";

$id = intval($_GET['id'] ?? 0); 
$editing = $id > 0; 
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']; 
$row = ['day' => 'Monday', 'time_slot' => '', 'subject' => '', 'teacher_id' => null];

if($editing) { 
    $r = $conn->query("SELECT * FROM timetable WHERE timetable_id=$id")->fetch_assoc(); 
    if($r) {
        $row = $r; 
    } else { 
        echo '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Timetable entry not found.</div>'; 
        require_once "../includes/natural_footer.php"; 
        exit;
    } 
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $day = $_POST['day']; 
    $time = $_POST['time_slot']; 
    $subject = $_POST['subject']; 
    $teacher_id = intval($_POST['teacher_id'] ?: 0) ?: null;
    
    if($editing) { 
        $stmt = $conn->prepare("UPDATE timetable SET day=?, time_slot=?, subject=?, teacher_id=? WHERE timetable_id=?"); 
        $stmt->bind_param("sssii", $day, $time, $subject, $teacher_id, $id); 
        $message = 'Timetable entry updated successfully!';
    } else { 
        $stmt = $conn->prepare("INSERT INTO timetable(day,time_slot,subject,teacher_id) VALUES(?,?,?,?)"); 
        $stmt->bind_param("sssi", $day, $time, $subject, $teacher_id); 
        $message = 'New timetable entry added successfully!';
    }
    
    $stmt->execute(); 
    echo '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' . $message . '</div>';
}

$teachers = $conn->query("SELECT user_id,name FROM users WHERE role IN ('teacher','admin') ORDER BY name");
?>

<!-- Page Header -->
<div class="professional-card">
    <h1 class="page-title"><?= $editing ? 'Edit' : 'Add' ?> Timetable Entry</h1>
    <p class="page-subtitle"><?= $editing ? 'Update existing schedule entry' : 'Create a new schedule entry' ?></p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="professional-card">
            <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
                <i class="bi bi-<?= $editing ? 'pencil' : 'plus-circle' ?> me-2"></i>
                <?= $editing ? 'Edit Schedule' : 'New Schedule Entry' ?>
            </h4>
            
            <form method="post">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Day of Week</label>
                        <select name="day" class="form-select" required>
                            <?php foreach($days as $d): ?>
                            <option value="<?= $d ?>" <?= $row['day'] == $d ? 'selected' : '' ?>>
                                <?= $d ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Time Slot</label>
                        <input name="time_slot" class="form-control" 
                               value="<?= htmlspecialchars($row['time_slot']) ?>" 
                               required placeholder="e.g., 9:00 AM - 10:00 AM">
                        <small class="form-text text-muted">Enter the time range for this class</small>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Subject/Course</label>
                    <input name="subject" class="form-control" 
                           value="<?= htmlspecialchars($row['subject']) ?>" 
                           required placeholder="Enter subject or course name">
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Teacher</label>
                    <select name="teacher_id" class="form-select">
                        <option value="">-- Select Teacher --</option>
                        <?php while($t = $teachers->fetch_assoc()): ?>
                        <option value="<?= $t['user_id'] ?>" 
                                <?= $row['teacher_id'] == $t['user_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <small class="form-text text-muted">Optional: Assign a teacher to this class</small>
                </div>
                
                <div class="d-flex gap-2 pt-3">
                    <button type="submit" class="btn btn-teacher">
                        <i class="bi bi-<?= $editing ? 'check-lg' : 'plus-lg' ?> me-1"></i>
                        <?= $editing ? 'Update Entry' : 'Add Entry' ?>
                    </button>
                    <a class="btn btn-outline-secondary" href="/fy_proj/timetable/index.php">
                        <i class="bi bi-arrow-left me-1"></i>Back to Timetable
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
