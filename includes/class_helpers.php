<?php
// Class-Based Content Filtering Helper Functions

/**
 * Get all classes that a student is enrolled in
 */
function getStudentClasses($conn, $student_id) {
    $query = "SELECT c.* FROM classes c 
              JOIN class_members cm ON c.class_id = cm.class_id 
              WHERE cm.student_id = ? AND c.is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get all classes that a teacher teaches
 */
function getTeacherClasses($conn, $teacher_id) {
    $query = "SELECT * FROM classes WHERE teacher_id = ? AND is_active = 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $teacher_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Check if a student is enrolled in a specific class
 */
function isStudentInClass($conn, $student_id, $class_id) {
    $query = "SELECT 1 FROM class_members WHERE student_id = ? AND class_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $class_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

/**
 * Get class-filtered assignments for students
 */
function getStudentAssignments($conn, $student_id) {
    $query = "SELECT a.*, u.name as uploader, c.class_name 
              FROM assignments a 
              LEFT JOIN users u ON a.uploaded_by = u.user_id
              LEFT JOIN classes c ON a.class_id = c.class_id
              WHERE (a.class_id IS NULL) 
                 OR (a.class_id IN (
                     SELECT class_id FROM class_members WHERE student_id = ?
                 ))
              ORDER BY a.upload_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get class-filtered quizzes for students
 */
function getStudentQuizzes($conn, $student_id) {
    $query = "SELECT q.*, u.name as creator, c.class_name 
              FROM quizzes q 
              LEFT JOIN users u ON q.created_by = u.user_id
              LEFT JOIN classes c ON q.class_id = c.class_id
              WHERE (q.class_id IS NULL) 
                 OR (q.class_id IN (
                     SELECT class_id FROM class_members WHERE student_id = ?
                 ))
              ORDER BY q.created_on DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get class-filtered study materials for students
 */
function getStudentStudyMaterials($conn, $student_id) {
    $query = "SELECT sm.*, u.name as uploader, c.class_name 
              FROM study_material sm 
              LEFT JOIN users u ON sm.uploaded_by = u.user_id
              LEFT JOIN classes c ON sm.class_id = c.class_id
              WHERE (sm.class_id IS NULL) 
                 OR (sm.class_id IN (
                     SELECT class_id FROM class_members WHERE student_id = ?
                 ))
              ORDER BY sm.uploaded_on DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get class-filtered notices for students
 */
function getStudentNotices($conn, $student_id) {
    $query = "SELECT n.*, u.name as poster, c.class_name 
              FROM notices n 
              LEFT JOIN users u ON n.posted_by = u.user_id
              LEFT JOIN classes c ON n.class_id = c.class_id
              WHERE (n.class_id IS NULL) 
                 OR (n.class_id IN (
                     SELECT class_id FROM class_members WHERE student_id = ?
                 ))
              ORDER BY n.posted_on DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get all assignments for teachers/admins (with class info)
 */
function getTeacherAssignments($conn, $teacher_id = null) {
    if ($teacher_id) {
        // Teacher sees only their assignments
        $query = "SELECT a.*, u.name as uploader, c.class_name 
                  FROM assignments a 
                  LEFT JOIN users u ON a.uploaded_by = u.user_id
                  LEFT JOIN classes c ON a.class_id = c.class_id
                  WHERE a.uploaded_by = ?
                  ORDER BY a.upload_date DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teacher_id);
    } else {
        // Admin sees all assignments
        $query = "SELECT a.*, u.name as uploader, c.class_name 
                  FROM assignments a 
                  LEFT JOIN users u ON a.uploaded_by = u.user_id
                  LEFT JOIN classes c ON a.class_id = c.class_id
                  ORDER BY a.upload_date DESC";
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get all study materials for teachers/admins (with class info)
 */
function getTeacherStudyMaterials($conn, $teacher_id = null) {
    if ($teacher_id) {
        // Teacher sees only their study materials
        $query = "SELECT sm.*, u.name as uploader, c.class_name 
                  FROM study_material sm 
                  LEFT JOIN users u ON sm.uploaded_by = u.user_id
                  LEFT JOIN classes c ON sm.class_id = c.class_id
                  WHERE sm.uploaded_by = ?
                  ORDER BY sm.uploaded_on DESC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $teacher_id);
    } else {
        // Admin sees all study materials
        $query = "SELECT sm.*, u.name as uploader, c.class_name 
                  FROM study_material sm 
                  LEFT JOIN users u ON sm.uploaded_by = u.user_id
                  LEFT JOIN classes c ON sm.class_id = c.class_id
                  ORDER BY sm.uploaded_on DESC";
        $stmt = $conn->prepare($query);
    }
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Get dropdown options for teacher's classes
 */
function getClassDropdownOptions($conn, $teacher_id, $selected_class_id = null) {
    $classes = getTeacherClasses($conn, $teacher_id);
    $options = '<option value="">All Students (No Class Restriction)</option>';
    
    while ($class = $classes->fetch_assoc()) {
        $selected = ($selected_class_id == $class['class_id']) ? 'selected' : '';
        $options .= '<option value="' . $class['class_id'] . '" ' . $selected . '>';
        $options .= htmlspecialchars($class['class_name']) . ' (' . $class['class_code'] . ')';
        $options .= '</option>';
    }
    
    return $options;
}

/**
 * Format class badge for content display
 */
function getClassBadge($class_name, $role = 'student') {
    if (empty($class_name)) {
        return '<span class="badge bg-secondary">All Students</span>';
    }
    
    $color = $role === 'student' ? 'bg-primary' : ($role === 'teacher' ? 'bg-success' : 'bg-danger');
    return '<span class="badge ' . $color . '">' . htmlspecialchars($class_name) . '</span>';
}

/**
 * Get class selection WHERE clause for SQL queries
 */
function getClassFilterWhere($user_role, $user_id, $table_alias = '') {
    $prefix = $table_alias ? $table_alias . '.' : '';
    
    if ($user_role === 'student') {
        return "({$prefix}class_id IS NULL OR {$prefix}class_id IN (SELECT class_id FROM class_members WHERE student_id = {$user_id}))";
    } elseif ($user_role === 'teacher') {
        return "({$prefix}class_id IS NULL OR {$prefix}class_id IN (SELECT class_id FROM classes WHERE teacher_id = {$user_id}))";
    } else {
        // Admin sees all
        return "1=1";
    }
}
?>