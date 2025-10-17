<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$role = $_SESSION['user']['role'];

if(isset($_GET['del']) && in_array($role,['admin','teacher'])) { 
    $id = intval($_GET['del']); 
    $conn->query("DELETE FROM notices WHERE notice_id=$id"); 
    echo '<div class="alert alert-success">Notice deleted successfully!</div>'; 
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && in_array($role,['admin','teacher'])) { 
    $title = trim($_POST['title']); 
    $message = trim($_POST['message']); 
    $stmt = $conn->prepare("INSERT INTO notices(title,message,posted_by) VALUES(?,?,?)"); 
    $stmt->bind_param("ssi", $title, $message, $_SESSION['user']['user_id']); 
    $stmt->execute(); 
    echo '<div class="alert alert-success">Notice posted successfully!</div>'; 
}

$res = $conn->query("SELECT n.*, u.name as poster FROM notices n LEFT JOIN users u ON n.posted_by=u.user_id ORDER BY n.posted_on DESC");
?>

<!-- Page Header -->
<?php if ($role == 'student'): ?>
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-bullhorn me-3"></i>Important Notices
            </h1>
            <p class="page-subtitle">
                Stay updated with the latest announcements and news from your school!
            </p>
        </div>
        <div class="col-lg-4 text-center">
            <div class="stats-icon blue" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto;">
                <i class="fas fa-newspaper"></i>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="page-header">
    <h1 class="page-title mb-2">
        <i class="fas fa-clipboard-list me-3"></i>Notice Board
    </h1>
    <p class="page-subtitle mb-0">Manage and view important announcements</p>
</div>
<?php endif; ?>

<div class="row">
    <?php if(in_array($role,['admin','teacher'])): ?>
    <div class="col-lg-5">
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-plus-square me-2"></i>Post New Notice
            </h4>
            
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Notice Title</label>
                    <input name="title" class="form-control" required placeholder="Enter notice title">
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Message</label>
                    <textarea name="message" class="form-control" rows="4" required 
                              placeholder="Enter your announcement or message"></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-bullhorn me-1"></i>Post Notice
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="<?= in_array($role,['admin','teacher']) ? 'col-lg-7' : 'col-12' ?>">
        <?php if ($role == 'student'): ?>
            <?php if($res->num_rows > 0): ?>
                <?php while($r = $res->fetch_assoc()): ?>
                <div class="content-card mb-4">
                    <h4 class="mb-3">
                        <i class="fas fa-bullhorn me-2"></i><?= htmlspecialchars($r['title']) ?>
                    </h4>
                    <div class="alert alert-info">
                        <p class="mb-0">
                            <?= nl2br(htmlspecialchars($r['message'])) ?>
                        </p>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-user me-1"></i>By: <?= htmlspecialchars($r['poster']) ?>
                        </small>
                        <small class="text-muted">
                            <i class="fas fa-calendar me-1"></i>Posted: <?= date('M j, Y', strtotime($r['posted_on'])) ?>
                        </small>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="content-card text-center py-5">
                    <div class="stats-icon blue mb-3 mx-auto" style="opacity: 0.5;">
                        <i class="fas fa-inbox"></i>
                    </div>
                    <h4>No Notices Yet</h4>
                    <p class="text-muted">Check back later for important announcements!</p>
                </div>
            <?php endif; ?>
        <?php else: ?>
        <div class="content-card">
            <h4 class="mb-4">
                <i class="fas fa-list me-2"></i>All Notices
                <?php if($res->num_rows > 0): ?>
                <span class="badge bg-info ms-2"><?= $res->num_rows ?> notices</span>
                <?php endif; ?>
            </h4>
            
            <?php if($res->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Message</th>
                            <th>Posted By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $res->data_seek(0);
                        while($r = $res->fetch_assoc()): 
                        ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                            <td><?= nl2br(htmlspecialchars($r['message'])) ?></td>
                            <td><?= htmlspecialchars($r['poster']) ?></td>
                            <td><?= date('M j, Y', strtotime($r['posted_on'])) ?></td>
                            <td>
                                <a class="btn btn-sm btn-outline-danger" 
                                   href="/fy_proj/notices/index.php?del=<?= $r['notice_id'] ?>" 
                                   onclick="return confirm('Delete this notice?')">
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
                <div class="stats-icon blue mb-3 mx-auto" style="opacity: 0.5;">
                    <i class="fas fa-bullhorn"></i>
                </div>
                <p class="text-muted">No notices posted yet</p>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
