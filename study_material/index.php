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
    $conn->query("DELETE FROM study_material WHERE material_id=$id");
    echo '<div class="alert alert-success">Study material deleted successfully!</div>';
}

// Get study materials based on user role and class enrollment
if ($role === 'student') {
    $res = getStudentStudyMaterials($conn, $user_id);
} elseif ($role === 'teacher') {
    $res = getTeacherStudyMaterials($conn, $user_id);
} else {
    // Admin sees all materials
    $res = getTeacherStudyMaterials($conn);
}

// Helper function to get file extension icon
function getFileIcon($filename) {
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    switch($ext) {
        case 'pdf':
            return '📄';
        case 'doc':
        case 'docx':
            return '📄';
        case 'ppt':
        case 'pptx':
            return '📊';
        case 'xls':
        case 'xlsx':
            return '📈';
        case 'jpg':
        case 'jpeg':
        case 'png':
        case 'gif':
            return '🖼️';
        case 'mp4':
        case 'avi':
        case 'mov':
            return '🎥';
        case 'mp3':
        case 'wav':
            return '🎵';
        default:
            return '📁';
    }
}
?>

<!-- Page Header -->
<?php if ($role == 'student'): ?>
<div class="welcome-card">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 style="color: var(--student-primary); font-weight: bold; margin-bottom: 10px;">
                📚 Study Materials
            </h1>
            <p style="font-size: 1.2rem; color: var(--secondary-gray); margin-bottom: 0;">
                Access course materials, notes, and resources to boost your learning! 🚀
            </p>
        </div>
        <div class="col-lg-4 text-center">
            <div style="font-size: 4rem; opacity: 0.8;">📋</div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="professional-card">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="page-title mb-2">Study Materials</h1>
            <p class="page-subtitle mb-0">Manage and share learning resources</p>
        </div>
        <a class="btn btn-teacher" href="/fy_proj/study_material/upload.php">
            <i class="fas fa-cloud-upload-alt me-1"></i>Upload Material
        </a>
    </div>
</div>
<?php endif; ?>

<?php if ($role == 'student'): ?>
<!-- Student View: Card-based Layout -->
<?php if($res->num_rows > 0): ?>
<div class="row g-3">
    <?php while($r = $res->fetch_assoc()): ?>
    <div class="col-lg-4 col-md-6">
        <div class="fun-card h-100">
            <div class="text-center mb-3">
                <div style="font-size: 3rem; opacity: 0.7;">
                    <?= getFileIcon($r['file_path'] ?? '') ?>
                </div>
            </div>
            
            <h5 style="color: var(--student-primary); font-weight: bold; margin-bottom: 10px;">
                <?= htmlspecialchars($r['title']) ?>
            </h5>
            
            <div class="mb-3">
                <small style="color: var(--text-secondary);">
                    <i class="fas fa-user me-1"></i>By: <?= htmlspecialchars($r['uploader'] ?: 'Unknown') ?>
                </small><br>
                <small style="color: var(--text-secondary);">
                    <i class="fas fa-calendar me-1"></i><?= date('M j, Y', strtotime($r['uploaded_on'])) ?>
                </small><br>
                <div class="mt-2">
                    <?= getClassBadge($r['class_name'] ?? null, $role) ?>
                </div>
            </div>
            
            <?php if($r['file_path']): ?>
            <div class="text-center">
                <a class="btn btn-student" href="<?= htmlspecialchars($r['file_path']) ?>" target="_blank">
                    <i class="fas fa-download me-1"></i>Download
                </a>
            </div>
            <?php else: ?>
            <div class="text-center">
                <span class="text-muted">No file available</span>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php else: ?>
<div class="fun-card text-center">
    <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 20px;">📚</div>
    <h4 style="color: var(--student-secondary);">No Study Materials</h4>
    <p class="text-muted">Materials will appear here once uploaded by teachers.</p>
</div>
<?php endif; ?>

<?php else: ?>
<!-- Teacher/Admin View: Table Layout -->
<div class="professional-card">
    <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
        <i class="fas fa-folder me-2"></i>All Study Materials
        <?php if($res->num_rows > 0): ?>
        <span class="badge" style="background-color: var(--teacher-primary); margin-left: 10px;">
            <?= $res->num_rows ?> files
        </span>
        <?php endif; ?>
    </h4>
    
    <?php if($res->num_rows > 0): ?>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th width="5%">#</th>
                    <th width="30%">Title</th>
                    <th width="12%">File</th>
                    <th width="15%">Class</th>
                    <th width="15%">Uploaded By</th>
                    <th width="13%">Date</th>
                    <th width="10%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $res->data_seek(0);
                while($r = $res->fetch_assoc()): 
                ?>
                <tr>
                    <td><strong><?= $r['material_id'] ?></strong></td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span style="font-size: 1.2rem; margin-right: 8px;">
                                <?= getFileIcon($r['file_path'] ?? '') ?>
                            </span>
                            <strong><?= htmlspecialchars($r['title']) ?></strong>
                        </div>
                    </td>
                    <td>
                        <?php if($r['file_path']): ?>
                        <a class="btn btn-sm btn-outline-primary" 
                           href="<?= htmlspecialchars($r['file_path']) ?>" 
                           target="_blank" title="Download File">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <?php else: ?>
                        <span class="text-muted">No file</span>
                        <?php endif; ?>
                    </td>
                    <td><?= getClassBadge($r['class_name'] ?? null, $role) ?></td>
                    <td><?= htmlspecialchars($r['uploader'] ?: '—') ?></td>
                    <td><?= date('M j, Y', strtotime($r['uploaded_on'])) ?></td>
                    <td>
                        <a class="btn btn-sm btn-danger" 
                           href="/fy_proj/study_material/index.php?del=<?= $r['material_id'] ?>" 
                           onclick="return confirm('Are you sure you want to delete this material?')" 
                           title="Delete Material">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="text-center py-4">
        <div style="font-size: 3rem; opacity: 0.5; margin-bottom: 15px;">📁</div>
        <h5 style="color: var(--secondary-gray);">No Study Materials</h5>
        <p class="text-muted">Upload your first study material to get started.</p>
        <a class="btn btn-teacher" href="/fy_proj/study_material/upload.php">
            <i class="fas fa-cloud-upload-alt me-1"></i>Upload Material
        </a>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once "../includes/natural_footer.php"; ?>
