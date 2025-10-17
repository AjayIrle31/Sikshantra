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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$room_id = intval($_GET['room_id'] ?? 0);
$current_user_id = $_SESSION['user']['user_id'];

if ($room_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid room ID']);
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
    
    // Get messages
    $messages_query = "SELECT cm.message_id, cm.message, cm.sent_at, cm.sender_id, u.name as sender_name 
                       FROM chat_messages cm 
                       JOIN users u ON cm.sender_id = u.user_id 
                       WHERE cm.room_id = ? 
                       ORDER BY cm.sent_at ASC 
                       LIMIT 100";
    
    $messages_stmt = $conn->prepare($messages_query);
    $messages_stmt->bind_param("i", $room_id);
    $messages_stmt->execute();
    $messages_result = $messages_stmt->get_result();
    
    $messages = [];
    while ($message = $messages_result->fetch_assoc()) {
        $messages[] = [
            'message_id' => $message['message_id'],
            'message' => htmlspecialchars($message['message']),
            'sent_at' => $message['sent_at'],
            'sender_id' => $message['sender_id'],
            'sender_name' => htmlspecialchars($message['sender_name'])
        ];
    }
    
    // Mark messages as read for current user
    $mark_read_stmt = $conn->prepare("UPDATE chat_messages SET is_read = 1 WHERE room_id = ? AND sender_id != ?");
    $mark_read_stmt->bind_param("ii", $room_id, $current_user_id);
    $mark_read_stmt->execute();
    
    echo json_encode(['success' => true, 'messages' => $messages]);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>