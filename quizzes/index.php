<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";
require_once "../includes/class_helpers.php";

$role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['user_id'];

if(isset($_GET['del']) && in_array($role, ['admin', 'teacher'])) {
    $id = intval($_GET['del']); 
    $conn->query("DELETE FROM quizzes WHERE quiz_id=$id"); 
    echo '<div class="alert alert-success">Quiz deleted successfully!</div>'; 
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role, ['admin', 'teacher'])) {
    $title = trim($_POST['title']); 
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
    $stmt = $conn->prepare("INSERT INTO quizzes(title,created_by,class_id) VALUES(?,?,?)"); 
    $stmt->bind_param("sii", $title, $_SESSION['user']['user_id'], $class_id); 
    $stmt->execute(); 
    echo '<div class="alert alert-success">Quiz created successfully!</div>'; 
}

// Get quizzes based on user role and class enrollment
if ($role === 'student') {
    $quizzes = getStudentQuizzes($conn, $user_id);
} elseif ($role === 'teacher') {
    $query = "SELECT q.*, u.name as creator, c.class_name 
              FROM quizzes q 
              LEFT JOIN users u ON q.created_by = u.user_id
              LEFT JOIN classes c ON q.class_id = c.class_id
              WHERE q.created_by = ?
              ORDER BY q.created_on DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $quizzes = $stmt->get_result();
} else {
    // Admin sees all quizzes
    $query = "SELECT q.*, u.name as creator, c.class_name 
              FROM quizzes q 
              LEFT JOIN users u ON q.created_by = u.user_id
              LEFT JOIN classes c ON q.class_id = c.class_id
              ORDER BY q.created_on DESC";
    $quizzes = $conn->query($query);
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <?php if ($role == 'student'): ?>
                    <i class="fas fa-brain me-2"></i>Quiz Center
                <?php else: ?>
                    <i class="fas fa-question-circle me-2"></i>Quiz Management
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">
                <?php if ($role == 'student'): ?>
                    Test your knowledge and challenge yourself with interactive quizzes
                <?php else: ?>
                    Create and manage quizzes for students to assess their learning
                <?php endif; ?>
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon <?= $role === 'student' ? 'blue' : ($role === 'teacher' ? 'green' : 'red') ?>">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <?php if(in_array($role, ['admin', 'teacher'])): ?>
    <!-- Create Quiz Section -->
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-plus-circle me-2"></i>Create New Quiz
            </h4>
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Quiz Title</label>
                    <input name="title" class="form-control" required 
                           placeholder="Enter an engaging quiz title...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Assign to Class</label>
                    <select name="class_id" class="form-select">
                        <?= getClassDropdownOptions($conn, $_SESSION['user']['user_id']) ?>
                    </select>
                    <small class="text-muted">Select a specific class or leave as "All Students"</small>
                </div>
                <button type="submit" class="btn <?= $role === 'teacher' ? 'btn-success' : 'btn-primary' ?> w-100">
                    <i class="fas fa-plus me-2"></i>Create Quiz
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Quizzes List Section -->
    <div class="<?= in_array($role, ['admin', 'teacher']) ? 'col-lg-8 col-md-6' : 'col-12' ?> mb-4">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">
                    <i class="fas fa-list-check me-2"></i>
                    <?php if ($role == 'student'): ?>
                        Available Quizzes
                    <?php else: ?>
                        All Quizzes
                    <?php endif; ?>
                </h4>
                <span class="badge <?= $role === 'student' ? 'bg-primary' : ($role === 'teacher' ? 'bg-success' : 'bg-danger') ?>">
                    <?= $quizzes->num_rows ?> Quiz<?= $quizzes->num_rows !== 1 ? 'es' : '' ?>
                </span>
            </div>
        
            <?php if($quizzes->num_rows > 0): ?>
                <?php if ($role == 'student'): ?>
                <!-- Student Card View -->
                <div class="row">
                    <?php while($q = $quizzes->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4 mb-3">
                        <div class="border rounded-3 p-3" style="background-color: #f8fafc;">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="mb-0"><?= htmlspecialchars($q['title']) ?></h6>
                                <span class="badge bg-primary"># <?= $q['quiz_id'] ?></span>
                            </div>
                            <p class="text-muted small mb-3">
                                <i class="fas fa-user me-1"></i>Created by: <?= htmlspecialchars($q['creator']) ?>
                                <?php if (!empty($q['class_name'])): ?>
                                <br><i class="fas fa-chalkboard me-1"></i><?= getClassBadge($q['class_name'], $role) ?>
                                <?php endif; ?>
                            </p>
                            <div class="d-flex gap-1">
                                <a class="btn btn-primary btn-sm flex-fill" 
                                   href="/fy_proj/quizzes/attempt.php?quiz=<?= $q['quiz_id'] ?>">
                                    <i class="fas fa-play me-1"></i>Take Quiz
                                </a>
                                <a class="btn btn-outline-primary btn-sm" 
                                   href="/fy_proj/quizzes/results.php?quiz=<?= $q['quiz_id'] ?>">
                                    <i class="fas fa-chart-bar"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <!-- Teacher/Admin Table View -->
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>Quiz Title</th>
                                <th class="d-none d-md-table-cell">Created By</th>
                                <th style="width: 200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $quizzes->data_seek(0); // Reset result pointer for admin/teacher view
                            while($q = $quizzes->fetch_assoc()): 
                            ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $q['quiz_id'] ?></span></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($q['title']) ?></div>
                                    <small class="text-muted d-md-none">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($q['creator']) ?>
                                    </small>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <small class="text-muted"><?= htmlspecialchars($q['creator']) ?></small>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a class="btn btn-outline-warning" 
                                           href="/fy_proj/quizzes/questions.php?quiz=<?= $q['quiz_id'] ?>" 
                                           title="Manage Questions">
                                            <i class="fas fa-question-circle"></i>
                                            <span class="d-none d-lg-inline ms-1">Questions</span>
                                        </a>
                                        <a class="btn btn-outline-info" 
                                           href="/fy_proj/quizzes/results.php?quiz=<?= $q['quiz_id'] ?>" 
                                           title="View Results">
                                            <i class="fas fa-chart-bar"></i>
                                            <span class="d-none d-lg-inline ms-1">Results</span>
                                        </a>
                                        <a class="btn btn-outline-danger" 
                                           href="/fy_proj/quizzes/index.php?del=<?= $q['quiz_id'] ?>" 
                                           onclick="return confirm('Are you sure you want to delete this quiz?')" 
                                           title="Delete Quiz">
                                            <i class="fas fa-trash"></i>
                                            <span class="d-none d-lg-inline ms-1">Delete</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            <?php else: ?>
            <div class="text-center py-5">
                <div class="stats-icon <?= $role === 'student' ? 'blue' : ($role === 'teacher' ? 'green' : 'red') ?> mx-auto mb-3" style="opacity: 0.5;">
                    <i class="fas fa-clipboard-question"></i>
                </div>
                <h6 class="text-muted mb-2">No quizzes available yet</h6>
                <?php if($role === 'student'): ?>
                    <p class="text-muted small">Check back later for new quizzes from your teachers!</p>
                <?php else: ?>
                    <p class="text-muted small mb-3">Create your first quiz to get started with assessments.</p>
                    <button class="btn btn-<?= $role === 'teacher' ? 'success' : 'primary' ?> btn-sm" onclick="document.querySelector('input[name=title]').focus()">
                        <i class="fas fa-plus me-1"></i>Create Quiz
                    </button>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
