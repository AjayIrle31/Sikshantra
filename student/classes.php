<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['student']); 
require_once "../includes/natural_header.php";

$student_id = $_SESSION['user']['user_id'];

// Join class by code
if (isset($_POST['join_class'])) {
    $class_code = trim(strtoupper($_POST['class_code']));
    
    if (!empty($class_code)) {
        // Find class by code
        $class_stmt = $conn->prepare("SELECT class_id, class_name, max_students FROM classes WHERE class_code = ? AND is_active = 1");
        $class_stmt->bind_param("s", $class_code);
        $class_stmt->execute();
        $class_result = $class_stmt->get_result();
        
        if ($class_result->num_rows > 0) {
            $class = $class_result->fetch_assoc();
            
            // Check if already member
            $member_check = $conn->prepare("SELECT * FROM class_members WHERE class_id = ? AND student_id = ?");
            $member_check->bind_param("ii", $class['class_id'], $student_id);
            $member_check->execute();
            $member_result = $member_check->get_result();
            
            if ($member_result->num_rows > 0) {
                echo '<div class="alert alert-info">You are already a member of this class!</div>';
            } else {
                // Check if class is full
                $count_stmt = $conn->prepare("SELECT COUNT(*) as current_count FROM class_members WHERE class_id = ?");
                $count_stmt->bind_param("i", $class['class_id']);
                $count_stmt->execute();
                $count_result = $count_stmt->get_result();
                $current_count = $count_result->fetch_assoc()['current_count'];
                
                if ($current_count >= $class['max_students']) {
                    echo '<div class="alert alert-danger">This class is full! Cannot join.</div>';
                } else {
                    // Join class
                    $join_stmt = $conn->prepare("INSERT INTO class_members (class_id, student_id) VALUES (?, ?)");
                    $join_stmt->bind_param("ii", $class['class_id'], $student_id);
                    
                    if ($join_stmt->execute()) {
                        // Add to class chat room
                        $room_stmt = $conn->prepare("SELECT room_id FROM chat_rooms WHERE class_id = ?");
                        $room_stmt->bind_param("i", $class['class_id']);
                        $room_stmt->execute();
                        $room_result = $room_stmt->get_result();
                        
                        if ($room = $room_result->fetch_assoc()) {
                            $chat_join = $conn->prepare("INSERT INTO chat_participants (room_id, user_id) VALUES (?, ?)");
                            $chat_join->bind_param("ii", $room['room_id'], $student_id);
                            $chat_join->execute();
                            $chat_join->close();
                        }
                        $room_stmt->close();
                        
                        echo '<div class="alert alert-success">Successfully joined ' . htmlspecialchars($class['class_name']) . '!</div>';
                    }
                    $join_stmt->close();
                }
                $count_stmt->close();
            }
            $member_check->close();
        } else {
            echo '<div class="alert alert-danger">Invalid class code! Please check and try again.</div>';
        }
        $class_stmt->close();
    }
}

// Leave class
if (isset($_POST['leave_class'])) {
    $class_id = intval($_POST['class_id']);
    
    $leave_stmt = $conn->prepare("DELETE FROM class_members WHERE class_id = ? AND student_id = ?");
    $leave_stmt->bind_param("ii", $class_id, $student_id);
    
    if ($leave_stmt->execute()) {
        // Remove from class chat room
        $room_stmt = $conn->prepare("SELECT room_id FROM chat_rooms WHERE class_id = ?");
        $room_stmt->bind_param("i", $class_id);
        $room_stmt->execute();
        $room_result = $room_stmt->get_result();
        
        if ($room = $room_result->fetch_assoc()) {
            $chat_leave = $conn->prepare("DELETE FROM chat_participants WHERE room_id = ? AND user_id = ?");
            $chat_leave->bind_param("ii", $room['room_id'], $student_id);
            $chat_leave->execute();
            $chat_leave->close();
        }
        $room_stmt->close();
        
        echo '<div class="alert alert-success">You have left the class successfully.</div>';
    }
    $leave_stmt->close();
}

// Get student's classes
$my_classes_query = "SELECT c.*, u.name as teacher_name, cm.joined_at 
                     FROM class_members cm 
                     JOIN classes c ON cm.class_id = c.class_id 
                     JOIN users u ON c.teacher_id = u.user_id 
                     WHERE cm.student_id = ? AND c.is_active = 1
                     ORDER BY cm.joined_at DESC";
$my_classes_stmt = $conn->prepare($my_classes_query);
$my_classes_stmt->bind_param("i", $student_id);
$my_classes_stmt->execute();
$my_classes_result = $my_classes_stmt->get_result();

// Get available classes (not joined)
$available_classes_query = "SELECT c.*, u.name as teacher_name, 
                            COUNT(cm.student_id) as current_students
                            FROM classes c 
                            JOIN users u ON c.teacher_id = u.user_id 
                            LEFT JOIN class_members cm ON c.class_id = cm.class_id
                            WHERE c.is_active = 1 
                            AND c.class_id NOT IN (
                                SELECT class_id FROM class_members WHERE student_id = ?
                            )
                            GROUP BY c.class_id
                            HAVING current_students < c.max_students
                            ORDER BY c.created_at DESC
                            LIMIT 10";
$available_stmt = $conn->prepare($available_classes_query);
$available_stmt->bind_param("i", $student_id);
$available_stmt->execute();
$available_result = $available_stmt->get_result();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-graduation-cap me-2"></i>My Classes
            </h1>
            <p class="page-subtitle">
                Join classes, view assignments, and participate in class discussions
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon green">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Join Class Section -->
    <div class="col-lg-4 mb-4">
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-plus-circle me-2"></i>Join a Class
            </h4>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Class Code</label>
                    <input type="text" class="form-control" name="class_code" 
                           placeholder="e.g., CLS0001" required style="text-transform: uppercase;">
                    <small class="text-muted">Enter the class code provided by your teacher</small>
                </div>
                <div class="d-grid">
                    <button type="submit" name="join_class" class="btn btn-success">
                        <i class="fas fa-user-plus me-2"></i>Join Class
                    </button>
                </div>
            </form>
            
            <div class="mt-4 p-3 bg-light rounded">
                <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>How to join:</h6>
                <ol class="mb-0 small">
                    <li>Get the class code from your teacher</li>
                    <li>Enter the code above and click "Join Class"</li>
                    <li>You'll gain access to assignments, quizzes, and class discussions</li>
                </ol>
            </div>
            </div>
        </div>
    </div>
    
    <!-- My Classes -->
    <div class="col-lg-8 mb-4">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">
                    <i class="fas fa-chalkboard-teacher me-2"></i>My Classes
                </h4>
                <span class="badge bg-success">
                    <?php echo $my_classes_result->num_rows; ?> Class<?php echo $my_classes_result->num_rows !== 1 ? 'es' : ''; ?>
                </span>
            </div>
            
            <?php if ($my_classes_result->num_rows > 0): ?>
                <div class="row g-3">
                    <?php while ($class = $my_classes_result->fetch_assoc()): ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h6 class="card-title mb-0"><?php echo htmlspecialchars($class['class_name']); ?></h6>
                                    <span class="badge bg-success"><?php echo $class['class_code']; ?></span>
                                </div>
                                
                                <div class="mb-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($class['teacher_name']); ?>
                                    </small>
                                </div>
                                
                                <?php if (!empty($class['description'])): ?>
                                <p class="card-text small text-muted mb-3">
                                    <?php echo htmlspecialchars($class['description']); ?>
                                </p>
                                <?php endif; ?>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="fas fa-calendar me-1"></i>Joined <?php echo date('M j, Y', strtotime($class['joined_at'])); ?>
                                    </small>
                                    
                                    <form method="POST" style="display: inline;" 
                                          onsubmit="return confirm('Are you sure you want to leave this class?');">
                                        <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                        <button type="submit" name="leave_class" class="btn btn-outline-danger btn-sm">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="stats-icon green mx-auto mb-3" style="opacity: 0.5;">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h6 class="text-muted mb-2">No classes joined yet</h6>
                    <p class="text-muted small mb-3">Use a class code from your teacher to join your first class!</p>
                    <button class="btn btn-success btn-sm" onclick="document.querySelector('input[name=class_code]').focus()">
                        <i class="fas fa-plus me-1"></i>Join Your First Class
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Available Classes -->
<?php if ($available_result->num_rows > 0): ?>
<div class="content-card">
    <h4 class="mb-4">
        <i class="fas fa-search me-2"></i>Available Classes
    </h4>
    
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        These are some available classes you can join if you have the class code from your teacher.
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Class Details</th>
                    <th class="d-none d-md-table-cell">Teacher</th>
                    <th>Enrollment</th>
                    <th class="d-none d-lg-table-cell">Description</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($class = $available_result->fetch_assoc()): ?>
                <tr>
                    <td>
                        <div class="fw-medium"><?php echo htmlspecialchars($class['class_name']); ?></div>
                        <div class="d-md-none">
                            <small class="text-muted">Teacher: <?php echo htmlspecialchars($class['teacher_name']); ?></small>
                        </div>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <small><?php echo htmlspecialchars($class['teacher_name']); ?></small>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="badge bg-primary me-1"><?php echo $class['current_students']; ?> / <?php echo $class['max_students']; ?></span>
                            <div class="progress" style="width: 60px; height: 4px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: <?php echo ($class['current_students'] / $class['max_students']) * 100; ?>%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <small class="text-muted"><?php echo !empty($class['description']) ? htmlspecialchars($class['description']) : 'No description'; ?></small>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    
    <div class="alert alert-light">
        <small><i class="fas fa-lightbulb me-1"></i>Ask your teacher for the class code to join any of these classes.</small>
    </div>
</div>
<?php endif; ?>

<?php require_once "../includes/natural_footer.php"; ?>
