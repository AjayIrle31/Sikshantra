<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin']); 
require_once "../includes/natural_header.php";

// Handle class creation
if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);
    $teacher_id = intval($_POST['teacher_id']);
    
    if (!empty($class_name) && $teacher_id > 0) {
        $stmt = $conn->prepare("INSERT INTO classes (class_name, description, teacher_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $class_name, $description, $teacher_id);
        if ($stmt->execute()) {
            $class_id = $conn->insert_id;
            // Create a chat room for the class
            $stmt2 = $conn->prepare("INSERT INTO chat_rooms (room_type, class_id, created_by) VALUES ('class', ?, ?)");
            $stmt2->bind_param("ii", $class_id, $_SESSION['user']['user_id']);
            $stmt2->execute();
            $room_id = $conn->insert_id;
            
            // Add teacher as participant in the chat room
            $stmt3 = $conn->prepare("INSERT INTO chat_participants (room_id, user_id) VALUES (?, ?)");
            $stmt3->bind_param("ii", $room_id, $teacher_id);
            $stmt3->execute();
            $stmt3->close();
            
            $stmt2->close();
            
            echo '<div class="alert alert-success">Class created successfully!</div>';
        } else {
            echo '<div class="alert alert-danger">Error creating class.</div>';
        }
        $stmt->close();
    } else {
        echo '<div class="alert alert-danger">Please fill in all required fields.</div>';
    }
}

// Handle adding student to class
if (isset($_POST['add_student'])) {
    $class_id = intval($_POST['class_id']);
    $student_id = intval($_POST['student_id']);
    
    $stmt = $conn->prepare("INSERT IGNORE INTO class_members (class_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $class_id, $student_id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Add student to class chat room
            $room_query = $conn->prepare("SELECT room_id FROM chat_rooms WHERE class_id = ?");
            $room_query->bind_param("i", $class_id);
            $room_query->execute();
            $room_result = $room_query->get_result();
            if ($room = $room_result->fetch_assoc()) {
                $participant_stmt = $conn->prepare("INSERT IGNORE INTO chat_participants (room_id, user_id) VALUES (?, ?)");
                $participant_stmt->bind_param("ii", $room['room_id'], $student_id);
                $participant_stmt->execute();
                $participant_stmt->close();
            }
            $room_query->close();
            
            echo '<div class="alert alert-success">Student added to class successfully!</div>';
        } else {
            echo '<div class="alert alert-warning">Student is already in this class.</div>';
        }
    } else {
        echo '<div class="alert alert-danger">Error adding student to class.</div>';
    }
    $stmt->close();
}

// Handle class deletion
if (isset($_POST['delete_class'])) {
    $class_id = intval($_POST['class_id']);
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ?");
    $stmt->bind_param("i", $class_id);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Class deleted successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Error deleting class.</div>';
    }
    $stmt->close();
}

// Get all teachers
$teachers_query = "SELECT user_id, name FROM users WHERE role = 'teacher' ORDER BY name";
$teachers_result = $conn->query($teachers_query);

// Get all students
$students_query = "SELECT user_id, name FROM users WHERE role = 'student' ORDER BY name";
$students_result = $conn->query($students_query);

// Get all classes with teacher info
$classes_query = "SELECT c.class_id, c.class_name, c.description, c.created_at, 
                         u.name as teacher_name,
                         COUNT(cm.student_id) as student_count
                  FROM classes c 
                  LEFT JOIN users u ON c.teacher_id = u.user_id 
                  LEFT JOIN class_members cm ON c.class_id = cm.class_id
                  GROUP BY c.class_id, c.class_name, c.description, c.created_at, u.name
                  ORDER BY c.created_at DESC";
$classes_result = $conn->query($classes_query);
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-chalkboard me-2"></i>Class Management
            </h1>
            <p class="page-subtitle">
                Create and organize classes, assign teachers, and manage student enrollment
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-5">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-plus-circle me-2"></i>Create New Class
            </h4>
            <form method="POST">
                <div class="mb-3">
                    <label for="class_name" class="form-label">Class Name *</label>
                    <input type="text" class="form-control" id="class_name" name="class_name" required 
                           placeholder="e.g., Mathematics Grade 10, Physics Advanced">
                </div>
                
                <div class="mb-3">
                    <label for="teacher_id" class="form-label">Assign Teacher *</label>
                    <select class="form-select" id="teacher_id" name="teacher_id" required>
                        <option value="">Select Teacher</option>
                        <?php 
                        $teachers_result->data_seek(0);
                        while ($teacher = $teachers_result->fetch_assoc()): 
                        ?>
                        <option value="<?php echo $teacher['user_id']; ?>">
                            <?php echo htmlspecialchars($teacher['name']); ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="2" 
                              placeholder="Brief description of the class..."></textarea>
                </div>
                
                <div class="d-grid">
                    <button type="submit" name="create_class" class="btn btn-success">
                        <i class="fas fa-plus me-2"></i>Create Class
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-7">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-list me-2"></i>Existing Classes
                </h4>
                <span class="badge bg-info">
                    <?php echo $classes_result->num_rows; ?> Class<?php echo $classes_result->num_rows !== 1 ? 'es' : ''; ?>
                </span>
            </div>
            <?php if ($classes_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Class Details</th>
                                <th class="d-none d-md-table-cell">Teacher</th>
                                <th>Students</th>
                                <th class="d-none d-lg-table-cell">Created</th>
                                <th style="width: 150px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($class = $classes_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="fw-medium"><?php echo htmlspecialchars($class['class_name']); ?></div>
                                    <?php if (!empty($class['description'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($class['description']); ?></small>
                                    <?php endif; ?>
                                    <div class="d-md-none mt-1">
                                        <small class="text-muted">Teacher: <?php echo htmlspecialchars($class['teacher_name']); ?></small>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <small><?php echo htmlspecialchars($class['teacher_name']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $class['student_count']; ?> student<?php echo $class['student_count'] !== 1 ? 's' : ''; ?></span>
                                    <br><button class="btn btn-sm btn-outline-success mt-1" 
                                            onclick="showAddStudentModal(<?php echo $class['class_id']; ?>, '<?php echo htmlspecialchars($class['class_name']); ?>')">
                                        <i class="fas fa-user-plus"></i> Add
                                    </button>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($class['created_at'])); ?></small>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to delete this class? This will remove all associated data.');">
                                        <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                        <button type="submit" name="delete_class" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                            <span class="d-none d-xl-inline ms-1">Delete</span>
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
                        <i class="fas fa-chalkboard"></i>
                    </div>
                    <h6 class="text-muted mb-2">No classes created yet</h6>
                    <p class="text-muted small">Create your first class to start organizing students and teachers.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus me-2"></i>Add Student to Class
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" id="modal_class_id" name="class_id">
                    <div class="mb-3">
                        <label for="modal_student_id" class="form-label">Select Student</label>
                        <select class="form-select" id="modal_student_id" name="student_id" required>
                            <option value="">Choose a student to add...</option>
                            <?php 
                            $students_result->data_seek(0);
                            while ($student = $students_result->fetch_assoc()): 
                            ?>
                            <option value="<?php echo $student['user_id']; ?>">
                                <?php echo htmlspecialchars($student['name']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                        <small class="text-muted">Only students not already in this class will be available to add.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                    <button type="submit" name="add_student" class="btn btn-success">
                        <i class="fas fa-user-plus me-1"></i>Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showAddStudentModal(classId, className) {
    document.getElementById('modal_class_id').value = classId;
    document.querySelector('#addStudentModal .modal-title').innerHTML = '<i class="fas fa-user-plus me-2"></i>Add Student to ' + className;
    new bootstrap.Modal(document.getElementById('addStudentModal')).show();
}
</script>

<?php require_once "../includes/natural_footer.php"; ?>
