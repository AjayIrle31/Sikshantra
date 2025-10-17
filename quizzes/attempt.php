<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['student']); 
require_once "../includes/natural_header.php";

$quiz = intval($_GET['quiz'] ?? 0); 
$quizRow = $conn->query("SELECT * FROM quizzes WHERE quiz_id=$quiz")->fetch_assoc(); 
if(!$quizRow) { 
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Quiz not found.</div>'; 
    require_once "../includes/natural_footer.php";
    exit; 
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qs = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id=$quiz"); 
    $score = 0; 
    $total = $qs->num_rows; 
    while($q = $qs->fetch_assoc()) {
        $sel = $_POST['q_'.$q['question_id']] ?? ''; 
        if($sel === $q['correct_option']) $score++; 
    } 
    $stmt = $conn->prepare("INSERT INTO quiz_results(quiz_id,student_id,score) VALUES(?,?,?)"); 
    $stmt->bind_param("iii", $quiz, $_SESSION['user']['user_id'], $score); 
    $stmt->execute(); 
    
    echo '<div class="content-card text-center py-4 mb-4">';
    echo '<div class="stats-icon green mx-auto mb-3">';
    echo '<i class="fas fa-trophy"></i>';
    echo '</div>';
    echo '<h3 class="text-success mb-3">Quiz Completed! 🎉</h3>';
    echo '<div class="row justify-content-center">';
    echo '<div class="col-md-6">';
    echo '<div class="stats-card mb-3">';
    echo '<div class="stats-icon blue mb-2"><i class="fas fa-chart-bar"></i></div>';
    echo '<h4 class="text-primary">'.$score.' / '.$total.'</h4>';
    echo '<p class="mb-0">Final Score</p>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    $percentage = $total > 0 ? round(($score / $total) * 100, 1) : 0;
    if($percentage >= 80) {
        echo '<div class="alert alert-success"><i class="fas fa-star me-2"></i>Excellent work! You scored '.$percentage.'%</div>';
    } elseif($percentage >= 60) {
        echo '<div class="alert alert-warning"><i class="fas fa-thumbs-up me-2"></i>Good job! You scored '.$percentage.'%</div>';
    } else {
        echo '<div class="alert alert-info"><i class="fas fa-rocket me-2"></i>Keep practicing! You scored '.$percentage.'%</div>';
    }
    echo '<a href="/fy_proj/quizzes/index.php" class="btn btn-primary mt-3">';
    echo '<i class="fas fa-arrow-left me-2"></i>Back to Quizzes</a>';
    echo '</div>';
}

$qs = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id=$quiz ORDER BY question_id");
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
                <i class="fas fa-brain me-2"></i>Quiz: <?= htmlspecialchars($quizRow['title']) ?>
            </h1>
            <p class="page-subtitle">
                Take your time and choose the best answer for each question. Good luck! 🍀
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon blue">
                <i class="fas fa-question-circle"></i>
            </div>
        </div>
    </div>
</div>

<?php if($qs->num_rows > 0): ?>
<div class="content-card">
    <h4 class="mb-4">
        <i class="fas fa-clipboard-list me-2"></i>Quiz Questions
        <span class="badge bg-primary ms-2"><?= $qs->num_rows ?> Question<?= $qs->num_rows !== 1 ? 's' : '' ?></span>
    </h4>
    
    <form method="post">
        <?php 
        $questionNum = 1;
        while($q = $qs->fetch_assoc()): 
        ?>
        <div class="content-card mb-4">
            <div class="mb-3">
                <h5 class="text-primary mb-3">
                    <i class="fas fa-question me-2"></i>Question <?= $questionNum ?>
                </h5>
                <p class="fs-5 fw-medium">
                    <?= htmlspecialchars($q['question_text']) ?>
                </p>
            </div>
            
            <div class="row g-3">
                <?php foreach(['A', 'B', 'C', 'D'] as $opt): 
                    $field = 'option_'.strtolower($opt); 
                ?>
                <div class="col-md-6">
                    <div class="form-check border rounded-3 p-3 h-100 option-card" style="cursor: pointer; transition: all 0.3s;">
                        <input class="form-check-input me-3" type="radio" 
                               name="q_<?= $q['question_id'] ?>" 
                               value="<?= $opt ?>" 
                               id="q<?= $q['question_id'] ?><?= $opt ?>" 
                               style="transform: scale(1.2);" required>
                        <label class="form-check-label w-100" for="q<?= $q['question_id'] ?><?= $opt ?>" style="cursor: pointer;">
                            <span class="badge bg-primary me-2"><?= $opt ?></span>
                            <?= htmlspecialchars($q[$field]) ?>
                        </label>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php 
        $questionNum++;
        endwhile; 
        ?>
        
        <div class="text-center pt-4 mt-4 border-top">
            <button type="submit" class="btn btn-success btn-lg me-3">
                <i class="fas fa-paper-plane me-2"></i>Submit Quiz
            </button>
            <a class="btn btn-outline-secondary btn-lg" href="/fy_proj/quizzes/index.php">
                <i class="fas fa-arrow-left me-2"></i>Cancel
            </a>
        </div>
    </form>
</div>
<?php else: ?>
<div class="content-card text-center py-5">
    <div class="stats-icon blue mx-auto mb-3" style="opacity: 0.5;">
        <i class="fas fa-question-circle"></i>
    </div>
    <h5 class="text-muted mb-2">No Questions Available</h5>
    <p class="text-muted small mb-3">This quiz doesn't have any questions yet. Please check back later.</p>
    <a class="btn btn-primary" href="/fy_proj/quizzes/index.php">
        <i class="fas fa-arrow-left me-1"></i>Back to Quizzes
    </a>
</div>
<?php endif; ?>

<!-- Add CSS for option cards -->
<style>
.option-card:hover {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.1) !important;
    transform: translateY(-2px);
}

.option-card input[type="radio"]:checked + label {
    color: #1e40af;
    font-weight: 600;
}

.option-card:has(input[type="radio"]:checked) {
    border-color: #3b82f6 !important;
    background-color: rgba(59, 130, 246, 0.1) !important;
}
</style>

<?php require_once "../includes/natural_footer.php"; ?>
