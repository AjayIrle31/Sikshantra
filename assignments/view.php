<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_login();
require_once "../includes/natural_header.php";

$id = intval($_GET['id'] ?? 0);
$a = $conn->query("SELECT a.*,u.name as uploader FROM assignments a LEFT JOIN users u ON a.uploaded_by=u.user_id WHERE assignment_id=$id")->fetch_assoc();

if(!$a) {
    echo '<div class="alert alert-danger">Assignment not found.</div>';
    require_once "../includes/natural_footer.php";
    exit;
}

$subs = $conn->query("SELECT s.*, u.name FROM submissions s LEFT JOIN users u ON s.student_id=u.user_id WHERE s.assignment_id=$id");
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
                <i class="fas fa-clipboard-list me-2"></i><?= htmlspecialchars($a['title']) ?>
            </h1>
            <p class="page-subtitle">
                Assignment details and student submissions
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon blue">
                <i class="fas fa-file-alt"></i>
            </div>
        </div>
    </div>
</div>

<!-- Assignment Details -->
<div class="row">
    <div class="col-lg-8">
        <div class="content-card">
            <h4 class="mb-3">
                <i class="fas fa-info-circle me-2"></i>Assignment Description
            </h4>
            <div class="alert alert-light">
                <?= nl2br(htmlspecialchars($a['description'])) ?>
            </div>
            
            <?php if($a['file_path']): ?>
            <div class="mt-3">
                <h6 class="mb-2">Assignment File</h6>
                <a href="<?= $a['file_path'] ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-download me-1"></i>Download Assignment File
                </a>
            </div>
            <?php endif; ?>
            
            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Created by</div>
                        <div class="info-value"><?= htmlspecialchars($a['uploader']) ?></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Upload Date</div>
                        <div class="info-value"><?= date('M j, Y', strtotime($a['upload_date'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="stats-card">
            <div class="stats-icon green">
                <i class="fas fa-paper-plane"></i>
            </div>
            <h3 class="text-success mb-2"><?= $subs->num_rows ?></h3>
            <p class="mb-1">Student Submissions</p>
            <small class="text-muted">Total received</small>
        </div>
    </div>
</div>

<!-- Student Submissions -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-users me-2"></i>Student Submissions
        </h4>
        <span class="badge bg-success">
            <?= $subs->num_rows ?> Submission<?= $subs->num_rows !== 1 ? 's' : '' ?>
        </span>
    </div>
    
    <?php if($subs->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Student Details</th>
                        <th class="d-none d-md-table-cell">Submission Time</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($s = $subs->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="avatar-circle me-3">
                                    <i class="fas fa-user-graduate"></i>
                                </div>
                                <div>
                                    <div class="fw-medium"><?= htmlspecialchars($s['name']) ?></div>
                                    <small class="text-muted d-md-none">
                                        Submitted: <?= date('M j, Y g:i A', strtotime($s['submitted_on'])) ?>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <small class="text-muted">
                                <?= date('M j, Y g:i A', strtotime($s['submitted_on'])) ?>
                            </small>
                        </td>
                        <td>
                            <a href="<?= $s['file_path'] ?>" target="_blank" 
                               class="btn btn-outline-primary btn-sm" title="Download Submission">
                                <i class="fas fa-download"></i>
                                <span class="d-none d-xl-inline ms-1">Download</span>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="text-center py-5">
            <div class="stats-icon blue mx-auto mb-3" style="opacity: 0.5;">
                <i class="fas fa-inbox"></i>
            </div>
            <h6 class="text-muted mb-2">No submissions yet</h6>
            <p class="text-muted small">Students haven't submitted their work for this assignment.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
