<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_role(['student']);
require_once "../includes/natural_header.php";

$aid = intval($_GET['id'] ?? 0);
$a = $conn->query("SELECT * FROM assignments WHERE assignment_id=$aid")->fetch_assoc();

if(!$a) {
    echo '<div class="alert alert-danger">Assignment not found!</div>';
    require_once "../includes/natural_footer.php";
    exit;
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    if(!empty($_FILES['file']['name'])) {
        $dir = __DIR__ . '/submissions';
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        $fname = time() . '_' . basename($_FILES['file']['name']);
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $fname);
        $path = '/fy_proj/assignments/submissions/' . $fname;
        
        $stmt = $conn->prepare("INSERT INTO submissions(assignment_id,student_id,file_path) VALUES(?,?,?) ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), submitted_on=CURRENT_TIMESTAMP");
        $stmt->bind_param("iis", $aid, $_SESSION['user']['user_id'], $path);
        $stmt->execute();
        echo '<div class="alert alert-success">🎉 Assignment submitted successfully!</div>';
    } else {
        echo '<div class="alert alert-danger">Please select a file to submit.</div>';
    }
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <div class="mb-3">
                <a href="/fy_proj/assignments/index.php" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                </a>
            </div>
            <h1 class="page-title">
                <i class="fas fa-upload me-2"></i>Submit Assignment
            </h1>
            <p class="page-subtitle">
                Upload your completed work for: <strong><?= htmlspecialchars($a['title']) ?></strong>
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon green">
                <i class="fas fa-file-upload"></i>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Details -->
<?php if($a['description']): ?>
<div class="content-card mb-4">
    <h5 class="mb-3"><i class="fas fa-info-circle me-2"></i>Assignment Instructions</h5>
    <div class="alert alert-info">
        <p class="mb-0"><?= nl2br(htmlspecialchars($a['description'])) ?></p>
    </div>
</div>
<?php endif; ?>

<!-- Submission Form -->
<div class="row">
    <div class="col-lg-8">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-cloud-upload-alt me-2"></i>Upload Your Work
            </h4>
            
            <form method="post" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label fw-medium">Choose File to Submit</label>
                    <input type="file" name="file" class="form-control form-control-lg" required 
                           accept=".pdf,.doc,.docx,.txt,.jpg,.png,.zip">
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Accepted formats: PDF, Word (.doc, .docx), Text, Images (JPG, PNG), ZIP files
                    </div>
                </div>
                
                <div class="d-flex gap-3">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Submit Assignment
                    </button>
                    <a class="btn btn-outline-secondary" href="/fy_proj/assignments/index.php">
                        <i class="fas fa-arrow-left me-2"></i>Back to Assignments
                    </a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="mb-3">
                <i class="fas fa-lightbulb me-2"></i>Submission Tips
            </h5>
            
            <div class="list-group list-group-flush">
                <div class="list-group-item border-0 px-0">
                    <i class="fas fa-file-check text-success me-2"></i>
                    <small>Check file format before uploading</small>
                </div>
                <div class="list-group-item border-0 px-0">
                    <i class="fas fa-search text-info me-2"></i>
                    <small>Review your work for completeness</small>
                </div>
                <div class="list-group-item border-0 px-0">
                    <i class="fas fa-clock text-warning me-2"></i>
                    <small>Submit before the deadline</small>
                </div>
                <div class="list-group-item border-0 px-0">
                    <i class="fas fa-shield-alt text-primary me-2"></i>
                    <small>Keep a backup of your work</small>
                </div>
            </div>
            
            <div class="alert alert-light mt-3">
                <small><i class="fas fa-info-circle me-1"></i>You can resubmit if needed - only the latest submission counts.</small>
            </div>
        </div>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
