<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$role = $_SESSION['user']['role']; 

if($_SERVER['REQUEST_METHOD'] === 'POST' && $role === 'student') {
    $subject = trim($_POST['subject']); 
    $teacher_id = intval($_POST['teacher_id']); 
    $text = trim($_POST['feedback_text']); 
    
    $stmt = $conn->prepare("INSERT INTO feedback(subject,student_id,teacher_id,feedback_text) VALUES(?,?,?,?)"); 
    $stmt->bind_param("siis", $subject, $_SESSION['user']['user_id'], $teacher_id, $text); 
    $stmt->execute(); 
    
    echo '<div class="alert alert-success">🎉 Feedback submitted successfully!</div>'; 
}

$teachers = $conn->query("SELECT user_id,name FROM users WHERE role IN ('teacher','admin') ORDER BY name");
$view = $conn->query("SELECT f.*, s.name AS sname, t.name AS tname FROM feedback f LEFT JOIN users s ON f.student_id=s.user_id LEFT JOIN users t ON f.teacher_id=t.user_id ORDER BY f.submitted_on DESC");
?>

<!-- Page Header -->
<?php if ($role == 'student'): ?>
<div class="welcome-card">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 style="color: var(--student-primary); font-weight: bold; margin-bottom: 10px;">
                📝 Feedback Center
            </h1>
            <p style="font-size: 1.2rem; color: var(--secondary-gray); margin-bottom: 0;">
                Share your thoughts and suggestions with your teachers! Your voice matters! ✨
            </p>
        </div>
        <div class="col-lg-4 text-center">
            <div style="font-size: 4rem; opacity: 0.8;">🗨️</div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="professional-card">
    <h1 class="page-title">Feedback Management</h1>
    <p class="page-subtitle">View and manage student feedback and suggestions</p>
</div>
<?php endif; ?>

<div class="row">
    <?php if($role === 'student'): ?>
    <div class="col-lg-5">
        <div class="fun-card">
            <h4 style="color: var(--student-secondary); font-weight: bold; margin-bottom: 20px;">
                <i class="bi bi-chat-square-heart me-2"></i>Share Your Thoughts
            </h4>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Subject</label>
                    <input name="subject" class="form-control" required 
                           placeholder="What's your feedback about?">
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Teacher</label>
                    <select name="teacher_id" class="form-control" required>
                        <option value="">Select a teacher...</option>
                        <?php 
                        $teachers->data_seek(0);
                        while($t = $teachers->fetch_assoc()): 
                        ?>
                        <option value="<?= $t['user_id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Your Feedback</label>
                    <textarea name="feedback_text" class="form-control" rows="4" required 
                              placeholder="Share your thoughts, suggestions, or questions..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-student">
                    <i class="bi bi-send me-1"></i>Send Feedback
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="<?= $role === 'student' ? 'col-lg-7' : 'col-12' ?>">
        <?php if ($role == 'student'): ?>
        <div class="fun-card">
            <h4 style="color: var(--student-secondary); font-weight: bold; margin-bottom: 20px;">
                <i class="bi bi-clock-history me-2"></i>Previous Feedback
            </h4>
        <?php else: ?>
        <div class="professional-card">
            <h3>All Student Feedback</h3>
        <?php endif; ?>
            
            <?php if($view->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>Student</th>
                            <th>Teacher</th>
                            <th>Feedback</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $view->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['subject']) ?></strong></td>
                            <td><?= htmlspecialchars($r['sname']) ?></td>
                            <td><?= htmlspecialchars($r['tname']) ?></td>
                            <td><?= nl2br(htmlspecialchars($r['feedback_text'])) ?></td>
                            <td><?= date('M j, Y', strtotime($r['submitted_on'])) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <div style="font-size: 3rem; opacity: 0.5;">📬</div>
                <p class="text-muted mt-2">No feedback submitted yet</p>
                <?php if($role === 'student'): ?>
                <p>Be the first to share your thoughts!</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
