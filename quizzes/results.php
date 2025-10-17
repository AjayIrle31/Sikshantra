<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$role = $_SESSION['user']['role'];
$quiz = intval($_GET['quiz'] ?? 0); 
$quizRow = $conn->query("SELECT * FROM quizzes WHERE quiz_id=$quiz")->fetch_assoc(); 
if(!$quizRow) { 
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Quiz not found.</div>'; 
    require_once "../includes/natural_footer.php";
    exit; 
}

$res = $conn->query("SELECT r.*, u.name FROM quiz_results r LEFT JOIN users u ON r.student_id=u.user_id WHERE r.quiz_id=$quiz ORDER BY r.attempted_on DESC");

// Get total questions for percentage calculation
$totalQuestions = $conn->query("SELECT COUNT(*) as total FROM quiz_questions WHERE quiz_id=$quiz")->fetch_assoc()['total'];
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="/fy_proj/quizzes/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Quizzes
                </a>
            </div>
            <h1 class="page-title">
                <i class="fas fa-chart-bar me-2"></i>Quiz Results
            </h1>
            <p class="page-subtitle">
                Results for: <strong><?= htmlspecialchars($quizRow['title']) ?></strong>
                <?php if ($role == 'student'): ?>
                - See how you and your classmates performed! 🎯
                <?php else: ?>
                - View student performance and analyze results
                <?php endif; ?>
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon <?= $role === 'student' ? 'blue' : 'green' ?>">
                <i class="fas fa-trophy"></i>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-<?= $role === 'student' ? 'trophy' : 'chart-line' ?> me-2"></i><?= $role === 'student' ? 'Leaderboard' : 'Student Results' ?>
        </h4>
        <?php if($res->num_rows > 0): ?>
        <span class="badge <?= $role === 'student' ? 'bg-primary' : 'bg-success' ?>">
            <?= $res->num_rows ?> Attempt<?= $res->num_rows !== 1 ? 's' : '' ?>
        </span>
        <?php endif; ?>
    </div>

    <?php if($res->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Score</th>
                    <?php if($totalQuestions > 0): ?>
                    <th>Percentage</th>
                    <?php endif; ?>
                    <th>Attempted On</th>
                    <?php if($role == 'student'): ?>
                    <th>Performance</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $position = 1;
                while($r = $res->fetch_assoc()): 
                    $percentage = $totalQuestions > 0 ? round(($r['score'] / $totalQuestions) * 100, 1) : 0;
                ?>
                <tr <?= ($role == 'student' && $r['student_id'] == $_SESSION['user']['user_id']) ? 'style="background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 227, 194, 0.1)); border-left: 4px solid var(--student-primary);"' : '' ?>>
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if($role == 'student'): ?>
                                <?php if($position <= 3): ?>
                                    <span style="font-size: 1.2rem; margin-right: 8px;">
                                        <?= $position == 1 ? '🥇' : ($position == 2 ? '🥈' : '🥉') ?>
                                    </span>
                                <?php endif; ?>
                            <?php endif; ?>
                            <strong><?= htmlspecialchars($r['name']) ?></strong>
                            <?php if($role == 'student' && $r['student_id'] == $_SESSION['user']['user_id']): ?>
                                <span class="badge" style="background-color: var(--student-primary); margin-left: 8px; font-size: 0.7rem;">YOU</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge <?= $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning text-dark' : 'bg-danger') ?> fs-6">
                            <?= $r['score'] ?> / <?= $totalQuestions ?>
                        </span>
                    </td>
                    <?php if($totalQuestions > 0): ?>
                    <td>
                        <div class="progress" style="height: 20px; width: 100px;">
                            <div class="progress-bar <?= $percentage >= 80 ? 'bg-success' : ($percentage >= 60 ? 'bg-warning' : 'bg-danger') ?>" 
                                 style="width: <?= $percentage ?>%">
                                <?= $percentage ?>%
                            </div>
                        </div>
                    </td>
                    <?php endif; ?>
                    <td><?= date('M j, Y \\a\\t g:i A', strtotime($r['attempted_on'])) ?></td>
                    <?php if($role == 'student'): ?>
                    <td>
                        <?php if($percentage >= 90): ?>
                            <span style="color: #28a745;">🎆 Outstanding!</span>
                        <?php elseif($percentage >= 80): ?>
                            <span style="color: #28a745;">🎉 Excellent!</span>
                        <?php elseif($percentage >= 70): ?>
                            <span style="color: #fd7e14;">👍 Good!</span>
                        <?php elseif($percentage >= 60): ?>
                            <span style="color: #fd7e14;">😊 Fair</span>
                        <?php else: ?>
                            <span style="color: #dc3545;">💪 Keep trying!</span>
                        <?php endif; ?>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php 
                $position++;
                endwhile; 
                ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-4">
        <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;">📈</div>
        <h5 style="color: var(--secondary-gray);">No Results Yet</h5>
        <p class="text-muted">No one has attempted this quiz yet.</p>
        <?php if($role == 'student'): ?>
        <a class="btn btn-student" href="/fy_proj/quizzes/attempt.php?quiz=<?= $quiz ?>">
            <i class="bi bi-play-circle me-1"></i>Be the First to Try!
        </a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="text-center pt-3">
        <a class="btn btn-outline-secondary" href="/fy_proj/quizzes/index.php">
            <i class="bi bi-arrow-left me-1"></i>Back to Quizzes
        </a>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
