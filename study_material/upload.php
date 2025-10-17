<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_role(['admin', 'teacher']); 
require_once "../includes/natural_header.php";
require_once "../includes/class_helpers.php";

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description'] ?? '');
    $class_id = !empty($_POST['class_id']) ? intval($_POST['class_id']) : null;
    
    if(!empty($_FILES['file']['name'])) {
        $dir = __DIR__ . '/files';
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $originalName = $_FILES['file']['name'];
        $fileExtension = pathinfo($originalName, PATHINFO_EXTENSION);
        $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
        $uploadPath = $dir . '/' . $fname;
        
        // Check file size (limit to 50MB)
        if($_FILES['file']['size'] > 50 * 1024 * 1024) {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>File size too large. Maximum size is 50MB.</div>';
        } else if(move_uploaded_file($_FILES['file']['tmp_name'], $uploadPath)) {
            $path = '/fy_proj/study_material/files/' . $fname;
            $stmt = $conn->prepare("INSERT INTO study_material(title,file_path,uploaded_by,class_id) VALUES(?,?,?,?)");
            $stmt->bind_param("ssii", $title, $path, $_SESSION['user']['user_id'], $class_id);
            
            if($stmt->execute()) {
                echo '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>Study material uploaded successfully!</div>';
            } else {
                echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error saving to database. Please try again.</div>';
            }
        } else {
            echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Error uploading file. Please try again.</div>';
        }
    } else {
        echo '<div class="alert alert-danger"><i class="bi bi-exclamation-triangle me-2"></i>Please select a file to upload.</div>';
    }
}
?>

<!-- Page Header -->
<div class="professional-card">
    <h1 class="page-title">Upload Study Material</h1>
    <p class="page-subtitle">Share educational resources with students</p>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="professional-card">
            <h4 style="color: var(--teacher-primary); margin-bottom: 20px;">
                <i class="bi bi-cloud-upload me-2"></i>Upload New Material
            </h4>
            
            <form method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Material Title</label>
                    <input name="title" class="form-control" required 
                           placeholder="Enter a descriptive title for the material">
                    <small class="form-text text-muted">e.g., "Chapter 5 - Mathematics Notes", "Physics Lab Manual"</small>
                </div>
                
                <?php if($_SESSION['user']['role'] == 'teacher'): ?>
                <div class="mb-3">
                    <label class="form-label" style="font-weight: bold;">Target Class</label>
                    <select name="class_id" class="form-control">
                        <?= getClassDropdownOptions($conn, $_SESSION['user']['user_id']) ?>
                    </select>
                    <small class="form-text text-muted">Select a specific class or leave as "All Students" to make it visible to everyone</small>
                </div>
                <?php endif; ?>
                
                <div class="mb-4">
                    <label class="form-label" style="font-weight: bold;">Select File</label>
                    <input type="file" name="file" class="form-control" required 
                           accept=".pdf,.doc,.docx,.ppt,.pptx,.xls,.xlsx,.txt,.jpg,.jpeg,.png,.gif,.mp4,.mp3,.zip,.rar">
                    <small class="form-text text-muted">
                        Supported formats: PDF, DOC, PPT, XLS, Images, Videos, Audio, Archives<br>
                        Maximum file size: 50MB
                    </small>
                </div>
                
                <div class="alert" style="background: linear-gradient(135deg, rgba(74, 144, 226, 0.1), rgba(80, 227, 194, 0.1)); border-left: 4px solid var(--teacher-primary);">
                    <h6 style="color: var(--teacher-primary); margin-bottom: 8px;">
                        <i class="bi bi-info-circle me-1"></i>Upload Guidelines
                    </h6>
                    <ul class="mb-0" style="font-size: 0.9rem;">
                        <li>Use descriptive titles that help students identify the content</li>
                        <li>Ensure files are properly formatted and readable</li>
                        <li>Check that the content is appropriate for all students</li>
                        <li>Large files may take longer to upload</li>
                    </ul>
                </div>
                
                <div class="d-flex gap-2 pt-3">
                    <button type="submit" class="btn btn-teacher">
                        <i class="bi bi-upload me-1"></i>Upload Material
                    </button>
                    <a class="btn btn-outline-secondary" href="/fy_proj/study_material/index.php">
                        <i class="bi bi-arrow-left me-1"></i>Back to Materials
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Upload Progress (Hidden by default) -->
<div id="uploadProgress" class="row justify-content-center" style="display: none;">
    <div class="col-lg-8">
        <div class="professional-card text-center">
            <div class="spinner-border" style="color: var(--teacher-primary);" role="status">
                <span class="visually-hidden">Uploading...</span>
            </div>
            <p class="mt-2">Uploading file, please wait...</p>
        </div>
    </div>
</div>

<script>
// Show progress indicator when form is submitted
document.querySelector('form').addEventListener('submit', function() {
    document.getElementById('uploadProgress').style.display = 'block';
    this.style.display = 'none';
});
</script>

<?php require_once "../includes/natural_footer.php"; ?>
