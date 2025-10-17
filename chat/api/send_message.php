<?php
require_once "../../includes/db.php";
require_once "../../includes/auth.php";
require_login();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$room_id = intval($input['room_id']);
$message = trim($input['message']);
$current_user_id = $_SESSION['user']['user_id'];

if ($room_id <= 0 || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Invalid input']);
    exit;
}

try {
    // Verify user has access to this chat room
    $access_query = "SELECT room_id FROM chat_participants WHERE room_id = ? AND user_id = ?";
    $access_stmt = $conn->prepare($access_query);
    $access_stmt->bind_param("ii", $room_id, $current_user_id);
    $access_stmt->execute();
    $access_result = $access_stmt->get_result();
    
    if ($access_result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }
    
    // Insert message
    $message_stmt = $conn->prepare("INSERT INTO chat_messages (room_id, sender_id, message) VALUES (?, ?, ?)");
    $message_stmt->bind_param("iis", $room_id, $current_user_id, $message);
    
    if ($message_stmt->execute()) {
        echo json_encode(['success' => true, 'message_id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to send message']);
    }
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>