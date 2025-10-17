<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['teacher']); 
require_once "../includes/natural_header.php";

$teacher_id = $_SESSION['user']['user_id'];

// Create new class
if (isset($_POST['create_class'])) {
    $class_name = trim($_POST['class_name']);
    $description = trim($_POST['description']);
    $max_students = intval($_POST['max_students']);
    
    if (!empty($class_name)) {
        // Insert class
        $stmt = $conn->prepare("INSERT INTO classes (class_name, description, teacher_id, max_students) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssii", $class_name, $description, $teacher_id, $max_students);
        
        if ($stmt->execute()) {
            $class_id = $conn->insert_id;
            
            // Generate class code
            $class_code = 'CLS' . str_pad($class_id, 4, '0', STR_PAD_LEFT);
            $update_stmt = $conn->prepare("UPDATE classes SET class_code = ? WHERE class_id = ?");
            $update_stmt->bind_param("si", $class_code, $class_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // Create chat room for class
            $chat_stmt = $conn->prepare("INSERT INTO chat_rooms (room_type, class_id, created_by) VALUES ('class', ?, ?)");
            $chat_stmt->bind_param("ii", $class_id, $teacher_id);
            $chat_stmt->execute();
            $room_id = $conn->insert_id;
            
            // Add teacher to chat room
            $participant_stmt = $conn->prepare("INSERT INTO chat_participants (room_id, user_id) VALUES (?, ?)");
            $participant_stmt->bind_param("ii", $room_id, $teacher_id);
            $participant_stmt->execute();
            $participant_stmt->close();
            $chat_stmt->close();
            
            echo '<div class="alert alert-success">Class created successfully! Class Code: ' . $class_code . '</div>';
        } else {
            echo '<div class="alert alert-danger">Error creating class.</div>';
        }
        $stmt->close();
    }
}

// Remove student from class
if (isset($_POST['remove_student'])) {
    $class_id = intval($_POST['class_id']);
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

// Delete class
if (isset($_POST['delete_class'])) {
    $class_id = intval($_POST['class_id']);
    
    $stmt = $conn->prepare("DELETE FROM classes WHERE class_id = ? AND teacher_id = ?");
    $stmt->bind_param("ii", $class_id, $teacher_id);
    if ($stmt->execute()) {
        echo '<div class="alert alert-success">Class deleted successfully.</div>';
    }
    $stmt->close();
}

// Get teacher's classes
$classes_query = "SELECT c.*, COUNT(cm.student_id) as student_count 
                  FROM classes c 
                  LEFT JOIN class_members cm ON c.class_id = cm.class_id 
                  WHERE c.teacher_id = ? AND c.is_active = 1
                  GROUP BY c.class_id
                  ORDER BY c.created_at DESC";
$classes_stmt = $conn->prepare($classes_query);
$classes_stmt->bind_param("i", $teacher_id);
$classes_stmt->execute();
$classes_result = $classes_stmt->get_result();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-chalkboard me-2"></i>Class Management
            </h1>
            <p class="page-subtitle">
                Create and manage your classes, invite students, and track enrollment
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
    <!-- Create New Class Section -->
    <div class="col-lg-4 mb-4">
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-plus-circle me-2"></i>Create New Class
            </h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Class Name *</label>
                    <input type="text" class="form-control" name="class_name" required 
                           placeholder="e.g., Math Team A, Science Group 1">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <input type="text" class="form-control" name="description" 
                           placeholder="Brief description of the class">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Maximum Students</label>
                    <input type="number" class="form-control" name="max_students" value="30" min="1" max="100">
                </div>
                
                <button type="submit" name="create_class" class="btn btn-success w-100">
                    <i class="fas fa-plus me-2"></i>Create Class
                </button>
            </form>
        </div>
    </div>
    
    <!-- Existing Classes Section -->
    <div class="col-lg-8 mb-4">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-list-check me-2"></i>My Classes
                </h4>
                <span class="badge bg-success">
                    <?php echo $classes_result->num_rows; ?> Class<?php echo $classes_result->num_rows !== 1 ? 'es' : ''; ?>
                </span>
            </div>
            
            <?php if ($classes_result->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Class Details</th>
                                <th class="d-none d-md-table-cell">Class Code</th>
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
                                        <span class="badge bg-success"><?php echo $class['class_code']; ?></span> • 
                                        <small class="text-muted"><?php echo date('M j, Y', strtotime($class['created_at'])); ?></small>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-success fw-bold">
                                        <?php echo $class['class_code']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <span class="fw-medium"><?php echo $class['student_count']; ?> / <?php echo $class['max_students']; ?></span>
                                    </div>
                                    <div class="progress mt-1" style="height: 4px;">
                                        <div class="progress-bar bg-success" role="progressbar" 
                                             style="width: <?php echo ($class['student_count'] / $class['max_students']) * 100; ?>%"></div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <small class="text-muted"><?php echo date('M j, Y', strtotime($class['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="view_class.php?id=<?php echo $class['class_id']; ?>" 
                                           class="btn btn-outline-info" title="View Students">
                                            <i class="fas fa-users"></i>
                                            <span class="d-none d-xl-inline ms-1">View</span>
                                        </a>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure? This will delete the class and remove all students.');">
                                            <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                            <button type="submit" name="delete_class" 
                                                    class="btn btn-outline-danger" title="Delete Class">
                                                <i class="fas fa-trash"></i>
                                                <span class="d-none d-xl-inline ms-1">Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="stats-icon green mx-auto mb-3" style="opacity: 0.5;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h6 class="text-muted mb-2">No classes created yet</h6>
                    <p class="text-muted small mb-3">Create your first class to start managing students and organizing your teaching.</p>
                    <button class="btn btn-success btn-sm" onclick="document.querySelector('input[name=class_name]').focus()">
                        <i class="fas fa-plus me-1"></i>Create Your First Class
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
