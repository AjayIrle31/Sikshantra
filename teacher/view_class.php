<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['teacher']); 
require_once "../includes/natural_header.php";

$teacher_id = $_SESSION['user']['user_id'];
$class_id = intval($_GET['id']);

// Check if teacher owns this class
$owner_check = $conn->prepare("SELECT * FROM classes WHERE class_id = ? AND teacher_id = ?");
$owner_check->bind_param("ii", $class_id, $teacher_id);
$owner_check->execute();
$class_result = $owner_check->get_result();

if ($class_result->num_rows == 0) {
    echo "Access denied!";
    exit;
}

$class_info = $class_result->fetch_assoc();

// Remove student from class
if (isset($_POST['remove_student'])) {
    $student_id = intval($_POST['student_id']);
    
    $stmt = $conn->prepare("DELETE FROM class_members WHERE class_id = ? AND student_id = ?");
    $stmt->bind_param("ii", $class_id, $student_id);
    if ($stmt->execute()) {
        // Remove from chat room too
        $room_stmt = $conn->prepare("SELECT room_id FROM chat_rooms WHERE class_id = ?");
        $room_stmt->bind_param("i", $class_id);
        $room_stmt->execute();
        $room_result = $room_stmt->get_result();
        if ($room = $room_result->fetch_assoc()) {
            $remove_chat = $conn->prepare("DELETE FROM chat_participants WHERE room_id = ? AND user_id = ?");
            $remove_chat->bind_param("ii", $room['room_id'], $student_id);
            $remove_chat->execute();
            $remove_chat->close();
        }
        $room_stmt->close();
        
        echo '<div class="alert alert-success">Student removed from class.</div>';
    }
    $stmt->close();
}

// Get students in this class
$students_query = "SELECT u.user_id, u.name, u.email, cm.joined_at 
                   FROM class_members cm 
                   JOIN users u ON cm.student_id = u.user_id 
                   WHERE cm.class_id = ? 
                   ORDER BY u.name";
$students_stmt = $conn->prepare($students_query);
$students_stmt->bind_param("i", $class_id);
$students_stmt->execute();
$students_result = $students_stmt->get_result();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="classes.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Classes
                </a>
            </div>
            <h1 class="page-title">
                <i class="fas fa-chalkboard me-2"></i><?php echo htmlspecialchars($class_info['class_name']); ?>
            </h1>
            <p class="page-subtitle">
                Manage students and view class details
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon green">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Class Information -->
    <div class="col-lg-8 mb-4">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Class Information
                </h4>
                <span class="badge bg-success fs-6">
                    <?php echo $class_info['class_code']; ?>
                </span>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Class Code</div>
                        <div class="info-value">
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <?php echo $class_info['class_code']; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Students Enrolled</div>
                        <div class="info-value">
                            <?php echo $students_result->num_rows; ?> / <?php echo $class_info['max_students']; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Description</div>
                        <div class="info-value">
                            <?php echo !empty($class_info['description']) ? htmlspecialchars($class_info['description']) : 'No description provided'; ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Created Date</div>
                        <div class="info-value">
                            <?php echo date('M j, Y', strtotime($class_info['created_at'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Enrollment Instructions -->
    <div class="col-lg-4 mb-4">
        <div class="content-card">
            <h5 class="mb-3">
                <i class="fas fa-user-plus me-2"></i>Student Enrollment
            </h5>
            <p class="text-muted mb-3">Share this class code with students:</p>
            <div class="alert alert-success text-center">
                <div class="fw-bold fs-4"><?php echo $class_info['class_code']; ?></div>
                <small class="text-muted">Students enter this code to join</small>
            </div>
            <div class="d-grid">
                <button class="btn btn-outline-success" onclick="copyClassCode()">
                    <i class="fas fa-copy me-2"></i>Copy Class Code
                </button>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-users me-2"></i>Class Students
                </h4>
                <span class="badge bg-info">
                    <?php echo $students_result->num_rows; ?> Student<?php echo $students_result->num_rows !== 1 ? 's' : ''; ?>
                </span>
            </div>
            
            <?php if ($students_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Student Details</th>
                                <th class="d-none d-md-table-cell">Email</th>
                                <th class="d-none d-lg-table-cell">Joined Date</th>
                                <th style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-circle me-3">
                                            <i class="fas fa-user-graduate"></i>
                                        </div>
                                        <div>
                                            <div class="fw-medium"><?php echo htmlspecialchars($student['name']); ?></div>
                                            <small class="text-muted d-md-none"><?php echo htmlspecialchars($student['email']); ?></small>
                                            <div class="d-lg-none">
                                                <small class="text-muted">Joined: <?php echo date('M j, Y', strtotime($student['joined_at'])); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <small><?php echo htmlspecialchars($student['email']); ?></small>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($student['joined_at'])); ?></small>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to remove this student from the class?');">
                                        <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                        <button type="submit" name="remove_student" 
                                                class="btn btn-outline-danger btn-sm" title="Remove Student">
                                            <i class="fas fa-user-minus"></i>
                                            <span class="d-none d-xl-inline ms-1">Remove</span>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="stats-icon blue mx-auto mb-3" style="opacity: 0.5;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h6 class="text-muted mb-2">No students enrolled yet</h6>
                    <p class="text-muted small mb-3">
                        Share your class code <strong><?php echo $class_info['class_code']; ?></strong> with students so they can join this class.
                    </p>
                    <button class="btn btn-success btn-sm" onclick="copyClassCode()">
                        <i class="fas fa-share me-1"></i>Share Class Code
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function copyClassCode() {
    const classCode = '<?php echo $class_info['class_code']; ?>';
    navigator.clipboard.writeText(classCode).then(function() {
        // Show success message
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check me-2"></i>Copied!';
        btn.classList.remove('btn-outline-success');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalText;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-success');
        }, 2000);
    }).catch(function() {
        alert('Class code: ' + classCode);
    });
}
</script>

<?php require_once "../includes/natural_footer.php"; ?>
