<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_login();
require_once "../includes/natural_header.php";
require_once "../includes/class_helpers.php";

$role = $_SESSION['user']['role'];
$user_id = $_SESSION['user']['user_id'];

if(isset($_GET['del']) && in_array($role,['admin','teacher'])) { 
    $id = intval($_GET['del']); 
    $conn->query("DELETE FROM assignments WHERE assignment_id=$id"); 
    echo '<div class="alert alert-success">Assignment deleted successfully!</div>'; 
}

// Get assignments based on user role and class enrollment
if ($role === 'student') {
    $res = getStudentAssignments($conn, $user_id);
} elseif ($role === 'teacher') {
    $res = getTeacherAssignments($conn, $user_id);
} else {
    // Admin sees all assignments
    $res = getTeacherAssignments($conn);
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <?php if ($role == 'student'): ?>
                    <i class="fas fa-tasks me-2"></i>My Assignments
                <?php else: ?>
                    <i class="fas fa-clipboard-list me-2"></i>Assignment Management
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">
                <?php if ($role == 'student'): ?>
                    View available assignments and submit your completed work
                <?php else: ?>
                    Create, manage, and track student assignments across all classes
                <?php endif; ?>
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <?php if ($role !== 'student'): ?>
                <a href="/fy_proj/assignments/create.php" class="btn <?= $role === 'teacher' ? 'btn-success' : 'btn-primary' ?>">
                    <i class="fas fa-plus me-2"></i>Create Assignment
                </a>
            <?php else: ?>
                <div class="stats-icon blue">
                    <i class="fas fa-file-alt"></i>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Assignments Content -->
<div class="content-card">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="fas fa-list-check me-2"></i>
            <?php if ($role == 'student'): ?>
                Available Assignments
            <?php else: ?>
                All Assignments
            <?php endif; ?>
        </h4>
        <span class="badge <?= $role === 'student' ? 'bg-primary' : ($role === 'teacher' ? 'bg-success' : 'bg-danger') ?>">
            <?= $res->num_rows ?> Assignment<?= $res->num_rows !== 1 ? 's' : '' ?>
        </span>
    </div>

    <?php if($res->num_rows > 0): ?>
        <?php if ($role == 'student'): ?>
        <!-- Student Card View -->
        <div class="row">
            <?php while($r = $res->fetch_assoc()): ?>
            <div class="col-lg-6 col-xl-4 mb-3">
                <div class="border rounded-3 p-3" style="background-color: #f8fafc; transition: all 0.2s ease;">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="mb-0 fw-medium"><?= htmlspecialchars($r['title']) ?></h6>
                        <span class="badge bg-primary"># <?= $r['assignment_id'] ?></span>
                    </div>
                            <div class="text-muted small mb-3">
                                <div class="mb-1">
                                    <i class="fas fa-user me-1"></i>Created by: <?= htmlspecialchars($r['uploader'] ?: 'System') ?>
                                </div>
                                <div class="mb-1">
                                    <i class="fas fa-calendar me-1"></i><?= date('M j, Y', strtotime($r['upload_date'])) ?>
                                </div>
                                <?php if (!empty($r['class_name'])): ?>
                                <div>
                                    <i class="fas fa-chalkboard me-1"></i><?= getClassBadge($r['class_name'], $role) ?>
                                </div>
                                <?php else: ?>
                                <div>
                                    <i class="fas fa-globe me-1"></i><?= getClassBadge('', $role) ?>
                                </div>
                                <?php endif; ?>
                            </div>
                    <div class="d-flex gap-2 align-items-center">
                        <?php if($r['file_path']): ?>
                            <a href="<?= $r['file_path'] ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        <?php endif; ?>
                        <a href="/fy_proj/assignments/submit.php?id=<?= $r['assignment_id'] ?>" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-upload me-1"></i>Submit Work
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
                                <th>Assignment Title</th>
                                <th class="d-none d-lg-table-cell">Class</th>
                                <th class="d-none d-md-table-cell">File</th>
                                <th class="d-none d-lg-table-cell">Created By</th>
                                <th class="d-none d-md-table-cell">Date</th>
                                <th style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                <tbody>
                    <?php 
                    $res->data_seek(0); // Reset result pointer
                    while($r = $res->fetch_assoc()): 
                    ?>
                            <tr>
                                <td><span class="badge bg-secondary"><?= $r['assignment_id'] ?></span></td>
                                <td>
                                    <div class="fw-medium"><?= htmlspecialchars($r['title']) ?></div>
                                    <small class="text-muted d-lg-none">
                                        <?php if (!empty($r['class_name'])): ?>
                                            <?= getClassBadge($r['class_name'], $role) ?> • 
                                        <?php endif; ?>
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($r['uploader'] ?: 'System') ?> • 
                                        <i class="fas fa-calendar me-1"></i><?= date('M j', strtotime($r['upload_date'])) ?>
                                    </small>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <?= getClassBadge($r['class_name'] ?: '', $role) ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                            <?php if($r['file_path']): ?>
                                <a href="<?= $r['file_path'] ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-download me-1"></i>File
                                </a>
                            <?php else: ?>
                                <span class="text-muted small">No file</span>
                            <?php endif; ?>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            <small class="text-muted"><?= htmlspecialchars($r['uploader'] ?: 'System') ?></small>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <small class="text-muted"><?= date('M j, Y', strtotime($r['upload_date'])) ?></small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="/fy_proj/assignments/view.php?id=<?= $r['assignment_id'] ?>" 
                                   class="btn btn-outline-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="/fy_proj/assignments/index.php?del=<?= $r['assignment_id'] ?>" 
                                   class="btn btn-outline-danger" title="Delete Assignment"
                                   onclick="return confirm('Are you sure you want to delete this assignment?')">
                                    <i class="fas fa-trash"></i>
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
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h6 class="text-muted mb-2">No assignments available</h6>
            <?php if($role === 'student'): ?>
                <p class="text-muted small">Check back later for new assignments from your teachers!</p>
            <?php else: ?>
                <p class="text-muted small mb-3">Create your first assignment to get started.</p>
                <a href="/fy_proj/assignments/create.php" class="btn btn-<?= $role === 'teacher' ? 'success' : 'primary' ?> btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Assignment
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
