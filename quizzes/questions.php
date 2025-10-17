<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin', 'teacher']); 
require_once "../includes/natural_header.php";

$quiz = intval($_GET['quiz'] ?? 0); 
$quizRow = $conn->query("SELECT * FROM quizzes WHERE quiz_id=$quiz")->fetch_assoc(); 
if(!$quizRow) { 
    echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Quiz not found.</div>'; 
    require_once "../includes/natural_footer.php";
    exit; 
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qt = trim($_POST['question_text']); 
    $a = trim($_POST['option_a']); 
    $b = trim($_POST['option_b']); 
    $c = trim($_POST['option_c']); 
    $d = trim($_POST['option_d']); 
    $co = $_POST['correct_option']; 
    $stmt = $conn->prepare("INSERT INTO quiz_questions(quiz_id,question_text,option_a,option_b,option_c,option_d,correct_option) VALUES(?,?,?,?,?,?,?)"); 
    $stmt->bind_param("issssss", $quiz, $qt, $a, $b, $c, $d, $co); 
    $stmt->execute(); 
    echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Question added successfully!</div>'; 
}

if(isset($_GET['del'])) {
    $qid = intval($_GET['del']); 
    $conn->query("DELETE FROM quiz_questions WHERE question_id=$qid"); 
    echo '<div class="alert alert-success"><i class="bi bi-trash me-2"></i>Question deleted successfully!</div>'; 
}

$qs = $conn->query("SELECT * FROM quiz_questions WHERE quiz_id=$quiz ORDER BY question_id");
?>

<!-- Page Header -->
<div class="professional-card">
    <h1 class="page-title">Quiz Questions</h1>
    <h2 style="color: var(--teacher-primary); font-size: 1.3rem; margin-bottom: 10px;">
        <?= htmlspecialchars($quizRow['title']) ?>
    </h2>
    <p class="page-subtitle">Add and manage questions for this quiz</p>
</div>

<div class="row g-4">
    <!-- Add Question Form -->
    <div class="col-lg-5">
        <div class="professional-card">
            <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
                <i class="bi bi-plus-circle me-2"></i>Add New Question
            </h4>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Question Text</label>
                    <textarea name="question_text" class="form-control" rows="3" required 
                              placeholder="Enter your question here..."></textarea>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Option A</label>
                        <input name="option_a" class="form-control" required 
                               placeholder="Option A...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Option B</label>
                        <input name="option_b" class="form-control" required 
                               placeholder="Option B...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Option C</label>
                        <input name="option_c" class="form-control" required 
                               placeholder="Option C...">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" style="font-weight: bold;">Option D</label>
                        <input name="option_d" class="form-control" required 
                               placeholder="Option D...">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Correct Answer</label>
                    <select name="correct_option" class="form-select" required>
                        <option value="">Select correct option...</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-teacher">
                    <i class="bi bi-plus-lg me-1"></i>Add Question
                </button>
            </form>
        </div>
    </div>
    
    <!-- Questions List -->
    <div class="col-lg-7">
        <div class="professional-card">
            <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
                <i class="bi bi-list-ul me-2"></i>Current Questions
                <span class="badge" style="background-color: var(--teacher-primary); margin-left: 10px;">
                    <?= $qs->num_rows ?>
                </span>
            </h4>
            
            <?php if($qs->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="35%">Question</th>
                            <th width="12%">A</th>
                            <th width="12%">B</th>
                            <th width="12%">C</th>
                            <th width="12%">D</th>
                            <th width="8%">Correct</th>
                            <th width="4%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($r = $qs->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?= $r['question_id'] ?></strong></td>
                            <td style="max-width: 200px; word-wrap: break-word;">
                                <?= htmlspecialchars($r['question_text']) ?>
                            </td>
                            <td><?= htmlspecialchars($r['option_a']) ?></td>
                            <td><?= htmlspecialchars($r['option_b']) ?></td>
                            <td><?= htmlspecialchars($r['option_c']) ?></td>
                            <td><?= htmlspecialchars($r['option_d']) ?></td>
                            <td>
                                <span class="badge bg-success"><?= $r['correct_option'] ?></span>
                            </td>
                            <td>
                                <a class="btn btn-sm btn-danger" 
                                   href="/fy_proj/quizzes/questions.php?quiz=<?= $quiz ?>&del=<?= $r['question_id'] ?>" 
                                   onclick="return confirm('Are you sure you want to delete this question?')" 
                                   title="Delete Question">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;">❓</div>
                <h5 style="color: var(--secondary-gray);">No Questions Yet</h5>
                <p class="text-muted">Add your first question to get started with this quiz.</p>
            </div>
            <?php endif; ?>
            
            <div class="text-center pt-3">
                <a class="btn btn-outline-secondary" href="/fy_proj/quizzes/index.php">
                    <i class="bi bi-arrow-left me-1"></i>Back to Quizzes
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
