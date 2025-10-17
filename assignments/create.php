<?php
require_once "../includes/db.php";
require_once "../includes/auth.php";
require_role(['admin','teacher']);
require_once "../includes/natural_header.php";
require_once "../includes/class_helpers.php";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title']; 
    $desc = $_POST['description']; 
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
    $path = null;
    
    if(!empty($_FILES['file']['name'])) {
        $dir = __DIR__ . '/uploads'; 
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        $fname = time() . '_' . basename($_FILES['file']['name']); 
        move_uploaded_file($_FILES['file']['tmp_name'], $dir . '/' . $fname); 
        $path = '/fy_proj/assignments/uploads/' . $fname;
    }
    
    $stmt = $conn->prepare("INSERT INTO assignments(title,description,file_path,uploaded_by,class_id) VALUES(?,?,?,?,?)");
    $stmt->bind_param("sssii", $title, $desc, $path, $_SESSION['user']['user_id'], $class_id); 
    $stmt->execute(); 
    echo '<div class="alert alert-success">Assignment created successfully!</div>';
}
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <i class="fas fa-plus-circle me-2"></i>Create Assignment
            </h1>
            <p class="page-subtitle">
                Upload a new assignment for your students with instructions and materials
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon <?= $_SESSION['user']['role'] === 'teacher' ? 'green' : 'red' ?>">
                <i class="fas fa-file-plus"></i>
            </div>
        </div>
    </div>
</div>

<div class="content-card">
    <h4 class="mb-4">
        <i class="fas fa-edit me-2"></i>Assignment Details
    </h4>
    
    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label"><strong>Assignment Title</strong></label>
                    <input name="title" class="form-control" required placeholder="Enter assignment title">
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Description</strong></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter assignment instructions and details"></textarea>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Assign to Class</strong></label>
                    <select name="class_id" class="form-select">
                        <?= getClassDropdownOptions($conn, $_SESSION['user']['user_id']) ?>
                    </select>
                    <small class="text-muted">Select a specific class or leave as "All Students" to make it visible to everyone</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Attachment (Optional)</strong></label>
                    <input type="file" name="file" class="form-control" accept=".pdf,.doc,.docx,.txt,.jpg,.png">
                    <small class="text-muted">Accepted formats: PDF, Word, Text, Images</small>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn <?= $_SESSION['user']['role'] === 'teacher' ? 'btn-success' : 'btn-primary' ?>">
                        <i class="fas fa-check-circle me-1"></i>Create Assignment
                    </button>
                    <a class="btn btn-outline-secondary" href="/fy_proj/assignments/index.php">
                        <i class="fas fa-arrow-left me-1"></i>Back to Assignments
                    </a>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="border rounded-3 p-3" style="background-color: #f8fafc;">
                    <h6 class="mb-3"><i class="fas fa-lightbulb me-1 text-warning"></i>Assignment Tips</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Use clear, descriptive titles</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Include detailed instructions</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Set clear deadlines</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-1"></i> Attach reference materials</li>
                    </ul>
                </div>
            </div>
        </div>
    </form>
</div>

<?php require_once "../includes/natural_footer.php"; ?>
