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

// Debug: Log request details
error_log("Create private chat request received from user: " . ($_SESSION['user']['user_id'] ?? 'unknown'));

$input = json_decode(file_get_contents('php://input'), true);
if ($input === null) {
    echo json_encode(['success' => false, 'error' => 'Invalid JSON data']);
    exit;
}

$recipient_id = intval($input['recipient_id'] ?? 0);
$current_user_id = $_SESSION['user']['user_id'];

error_log("Attempting to create chat between user $current_user_id and recipient $recipient_id");

if ($recipient_id <= 0 || $recipient_id == $current_user_id) {
    echo json_encode(['success' => false, 'error' => 'Invalid recipient']);
    exit;
}

try {
    // Check if private chat room already exists between these two users
    $existing_room_query = "SELECT cr.room_id 
                           FROM chat_rooms cr
                           JOIN chat_participants cp1 ON cr.room_id = cp1.room_id AND cp1.user_id = ?
                           JOIN chat_participants cp2 ON cr.room_id = cp2.room_id AND cp2.user_id = ?
                           WHERE cr.room_type = 'private'";
    
    $existing_stmt = $conn->prepare($existing_room_query);
    $existing_stmt->bind_param("ii", $current_user_id, $recipient_id);
    $existing_stmt->execute();
    $existing_result = $existing_stmt->get_result();
    
    if ($existing_room = $existing_result->fetch_assoc()) {
        // Room already exists
        echo json_encode(['success' => true, 'room_id' => $existing_room['room_id']]);
        exit;
    }
    
    // Create new private chat room
    $conn->begin_transaction();
    
    // Insert chat room
    $room_stmt = $conn->prepare("INSERT INTO chat_rooms (room_type, created_by) VALUES ('private', ?)");
    $room_stmt->bind_param("i", $current_user_id);
    $room_stmt->execute();
    $room_id = $conn->insert_id;
    
    // Add both participants
    $participant_stmt = $conn->prepare("INSERT INTO chat_participants (room_id, user_id) VALUES (?, ?), (?, ?)");
    $participant_stmt->bind_param("iiii", $room_id, $current_user_id, $room_id, $recipient_id);
    $participant_stmt->execute();
    
    $conn->commit();
    
    echo json_encode(['success' => true, 'room_id' => $room_id]);
    
} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'error' => 'Failed to create chat room']);
}
?>