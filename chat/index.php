<?php 
require_once "../includes/db.php"; 
require_once "../includes/auth.php"; 
require_login(); 
require_once "../includes/natural_header.php";

$user_id = $_SESSION['user']['user_id'];
$user_role = $_SESSION['user']['role'];

// Get all users for private chat (excluding current user)
$users_query = "SELECT user_id, name, role FROM users WHERE user_id != ? ORDER BY role, name";
$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param("i", $user_id);
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Get classes the user belongs to
$classes_query = "";
if ($user_role === 'teacher') {
    $classes_query = "SELECT c.class_id, c.class_name, cr.room_id 
                      FROM classes c 
                      JOIN chat_rooms cr ON c.class_id = cr.class_id 
                      WHERE c.teacher_id = ?";
} else if ($user_role === 'student') {
    $classes_query = "SELECT c.class_id, c.class_name, cr.room_id 
                      FROM classes c 
                      JOIN class_members cm ON c.class_id = cm.class_id 
                      JOIN chat_rooms cr ON c.class_id = cr.class_id 
                      WHERE cm.student_id = ?";
} else {
    // Admin can see all classes
    $classes_query = "SELECT c.class_id, c.class_name, cr.room_id 
                      FROM classes c 
                      JOIN chat_rooms cr ON c.class_id = cr.class_id";
}

$classes_result = null;
if (!empty($classes_query)) {
    $classes_stmt = $conn->prepare($classes_query);
    if ($user_role !== 'admin') {
        $classes_stmt->bind_param("i", $user_id);
    }
    $classes_stmt->execute();
    $classes_result = $classes_stmt->get_result();
}

// Get existing private chat rooms
$private_rooms_query = "SELECT cr.room_id, u.user_id, u.name, u.role,
                               (SELECT COUNT(*) FROM chat_messages cm 
                                WHERE cm.room_id = cr.room_id AND cm.sender_id != ? AND cm.is_read = 0) as unread_count,
                               (SELECT cm.message FROM chat_messages cm 
                                WHERE cm.room_id = cr.room_id 
                                ORDER BY cm.sent_at DESC LIMIT 1) as last_message,
                               (SELECT cm.sent_at FROM chat_messages cm 
                                WHERE cm.room_id = cr.room_id 
                                ORDER BY cm.sent_at DESC LIMIT 1) as last_message_time
                        FROM chat_rooms cr
                        JOIN chat_participants cp1 ON cr.room_id = cp1.room_id AND cp1.user_id = ?
                        JOIN chat_participants cp2 ON cr.room_id = cp2.room_id AND cp2.user_id != ?
                        JOIN users u ON cp2.user_id = u.user_id
                        WHERE cr.room_type = 'private'
                        ORDER BY last_message_time DESC";
$private_rooms_stmt = $conn->prepare($private_rooms_query);
$private_rooms_stmt->bind_param("iii", $user_id, $user_id, $user_id);
$private_rooms_stmt->execute();
$private_rooms_result = $private_rooms_stmt->get_result();
?>

<!-- Page Header -->
<div class="page-header">
    <div class="row align-items-center">
        <div class="col-lg-8">
            <h1 class="page-title">
                <?php if ($user_role === 'student'): ?>
                    <i class="fas fa-comments me-2"></i>Messages & Chat
                <?php else: ?>
                    <i class="fas fa-comment-dots me-2"></i>Communication Center
                <?php endif; ?>
            </h1>
            <p class="page-subtitle">
                <?php if ($user_role === 'student'): ?>
                    Connect with classmates and teachers through private messages and class discussions
                <?php else: ?>
                    Communicate with students and colleagues in real-time through private and group chats
                <?php endif; ?>
            </p>
        </div>
        <div class="col-lg-4 text-end">
            <div class="stats-icon <?= $user_role === 'student' ? 'blue' : ($user_role === 'teacher' ? 'green' : 'red') ?>">
                <i class="fas fa-message"></i>
            </div>
        </div>
    </div>
</div>
    
<div class="row">
    <!-- Chat List Sidebar -->
    <div class="col-lg-4 mb-4">
        <div class="content-card" style="height: 600px;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="fas fa-list me-2"></i>Conversations</h5>
            </div>
            <ul class="nav nav-pills mb-3" role="tablist">
                <li class="nav-item flex-fill">
                    <button class="nav-link active w-100" id="private-tab" data-bs-toggle="pill" 
                            data-bs-target="#private-chats" type="button">
                        <i class="fas fa-user me-1"></i>Private
                    </button>
                </li>
                <li class="nav-item flex-fill">
                    <button class="nav-link w-100" id="class-tab" data-bs-toggle="pill" 
                            data-bs-target="#class-chats" type="button">
                        <i class="fas fa-users me-1"></i>Classes
                    </button>
                </li>
            </ul>
            <div style="height: calc(100% - 120px); overflow-y: auto;">
                <div class="tab-content">
                    <!-- Private Chats -->
                    <div class="tab-pane fade show active" id="private-chats">
                        <div class="mb-3">
                            <h6 class="mb-2 text-muted">Active Conversations</h6>
                            <div id="existing-private-chats">
                                <?php if ($private_rooms_result->num_rows > 0): ?>
                                    <?php while ($room = $private_rooms_result->fetch_assoc()): ?>
                                    <div class="chat-item d-flex align-items-center p-2 mb-2 border rounded cursor-pointer" 
                                         style="transition: all 0.2s ease;" 
                                         data-action="open-private-chat" 
                                         data-user-id="<?php echo $room['user_id']; ?>" 
                                         data-user-name="<?php echo htmlspecialchars($room['name']); ?>" 
                                         data-room-id="<?php echo $room['room_id']; ?>">
                                        <div class="stats-icon <?= $user_role === 'student' ? 'blue' : 'green' ?> me-2" style="width: 40px; height: 40px; font-size: 1rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <span class="fw-medium"><?php echo htmlspecialchars($room['name']); ?></span>
                                                <?php if ($room['unread_count'] > 0): ?>
                                                <span class="badge bg-danger"><?php echo $room['unread_count']; ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <small class="text-muted"><?php echo htmlspecialchars(substr($room['last_message'] ?? 'No messages yet', 0, 40)); ?>...</small>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-comment-slash mb-2"></i>
                                        <p class="small mb-0">No active chats</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="border-top pt-3">
                            <h6 class="mb-2 text-muted">Start New Chat</h6>
                            <div class="new-chat-users">
                                <?php 
                                $users_result->data_seek(0); // Reset result pointer
                                while ($user = $users_result->fetch_assoc()): 
                                ?>
                                <div class="d-flex align-items-center p-2 mb-1 border rounded cursor-pointer" 
                                     style="transition: all 0.2s ease;" 
                                     data-action="start-new-private-chat" 
                                     data-user-id="<?php echo $user['user_id']; ?>" 
                                     data-user-name="<?php echo htmlspecialchars($user['name']); ?>">
                                    <div class="stats-icon <?= $user['role'] === 'student' ? 'blue' : ($user['role'] === 'teacher' ? 'green' : 'red') ?> me-2" style="width: 32px; height: 32px; font-size: 0.875rem;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo htmlspecialchars($user['name']); ?></div>
                                        <small class="text-muted"><?php echo ucfirst($user['role']); ?></small>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Class Chats -->
                    <div class="tab-pane fade" id="class-chats">
                        <?php if ($classes_result && $classes_result->num_rows > 0): ?>
                            <?php while ($class = $classes_result->fetch_assoc()): ?>
                            <div class="d-flex align-items-center p-2 mb-2 border rounded cursor-pointer" 
                                 style="transition: all 0.2s ease;" 
                                 data-action="open-class-chat" 
                                 data-class-id="<?php echo $class['class_id']; ?>" 
                                 data-class-name="<?php echo htmlspecialchars($class['class_name']); ?>" 
                                 data-room-id="<?php echo $class['room_id']; ?>">
                                <div class="stats-icon green me-2" style="width: 40px; height: 40px; font-size: 1rem;">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="fw-medium"><?php echo htmlspecialchars($class['class_name']); ?></div>
                                    <small class="text-muted">Class Group Chat</small>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-chalkboard mb-2"></i>
                                <p class="small mb-0">No classes available</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="col-lg-8 mb-4">
        <div class="content-card" style="height: 600px; display: flex; flex-direction: column;">
            <div class="border-bottom pb-3 mb-3" id="chat-header">
                <h5 class="mb-0">Select a chat to start messaging</h5>
                <small class="text-muted">Choose a conversation from the sidebar</small>
            </div>
            <div id="chat-area" style="flex: 1; display: flex; flex-direction: column;">
                <div style="flex: 1; display: flex; align-items: center; justify-content: center;">
                    <div class="text-center text-muted">
                        <div class="stats-icon <?= $user_role === 'student' ? 'blue' : ($user_role === 'teacher' ? 'green' : 'red') ?> mx-auto mb-3" style="opacity: 0.5;">
                            <i class="fas fa-comment-dots"></i>
                        </div>
                        <h6>Start a Conversation</h6>
                        <p class="small mb-0">Select someone from the sidebar to begin chatting</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.cursor-pointer { cursor: pointer; }
.chat-item:hover { 
    background-color: #f1f5f9 !important; 
    transform: translateY(-1px); 
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}
.chat-item {
    border-color: #e2e8f0 !important;
}
.chat-item:hover .stats-icon {
    transform: scale(1.05);
}
#chat-messages {
    flex-grow: 1;
    overflow-y: auto;
    max-height: 450px;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    margin-bottom: 1rem;
}
.message {
    margin-bottom: 1rem;
    padding: 0.75rem 1rem;
    border-radius: 12px;
    max-width: 75%;
    word-wrap: break-word;
}
.message.own {
    background: linear-gradient(135deg, #1e40af, #1d4ed8);
    color: white;
    margin-left: auto;
    text-align: right;
    box-shadow: 0 2px 4px rgba(30, 64, 175, 0.2);
}
.message.other {
    background-color: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.message-info {
    font-size: 0.75rem;
    opacity: 0.8;
    margin-top: 0.5rem;
    font-weight: 500;
}
</style>

<script>
let currentRoomId = null;
let currentChatType = null;
let currentUserId = <?php echo intval($user_id); ?>;
let messageInterval = null;

window.addEventListener('DOMContentLoaded', function() {
    console.log('Chat page loaded. Current user ID:', currentUserId);
    console.log('Chat functions defined:', typeof startNewPrivateChat, typeof openPrivateChat);
    
    // Add click event listeners for chat items
    document.addEventListener('click', function(e) {
        const element = e.target.closest('[data-action]');
        if (!element) return;
        
        const action = element.dataset.action;
        
        if (action === 'open-private-chat') {
            const userId = parseInt(element.dataset.userId);
            const userName = element.dataset.userName;
            const roomId = parseInt(element.dataset.roomId);
            openPrivateChat(userId, userName, roomId);
        } 
        else if (action === 'start-new-private-chat') {
            const userId = parseInt(element.dataset.userId);
            const userName = element.dataset.userName;
            startNewPrivateChat(userId, userName);
        } 
        else if (action === 'open-class-chat') {
            const classId = parseInt(element.dataset.classId);
            const className = element.dataset.className;
            const roomId = parseInt(element.dataset.roomId);
            openClassChat(classId, className, roomId);
        }
    });
});

function startNewPrivateChat(userId, userName) {
    console.log('Starting new private chat with:', userId, userName);
    // Create or get private chat room
    fetch('api/create_private_chat.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ recipient_id: userId })
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Create chat response:', data);
        if (data.success) {
            openPrivateChat(userId, userName, data.room_id);
        } else {
            console.error('Failed to create chat:', data.error);
            alert('Failed to create chat: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error creating chat:', error);
        alert('Error creating chat: ' + error.message);
    });
}

function openPrivateChat(userId, userName, roomId) {
    console.log('Opening private chat:', userId, userName, roomId);
    currentRoomId = roomId;
    currentChatType = 'private';
    
    document.getElementById('chat-header').innerHTML = `
        <h5 class="mb-0">
            <i class="fas fa-user-circle me-2"></i>${userName}
        </h5>
        <small class="text-muted">Private Chat</small>
    `;
    
    loadChatArea();
    loadMessages();
    
    if (messageInterval) clearInterval(messageInterval);
    messageInterval = setInterval(loadMessages, 3000);
}

function openClassChat(classId, className, roomId) {
    currentRoomId = roomId;
    currentChatType = 'class';
    
    document.getElementById('chat-header').innerHTML = `
        <h5 class="mb-0">
            <i class="fas fa-users me-2"></i>${className}
        </h5>
        <small class="text-muted">Class Group Chat</small>
    `;
    
    loadChatArea();
    loadMessages();
    
    if (messageInterval) clearInterval(messageInterval);
    messageInterval = setInterval(loadMessages, 3000);
}

function loadChatArea() {
    document.getElementById('chat-area').innerHTML = `
        <div id="chat-messages" class="flex-grow-1"></div>
        <div class="d-flex gap-2">
            <input type="text" id="message-input" class="form-control" 
                   placeholder="Type your message..." required>
            <button type="submit" id="send-button" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    `;
    
    // Add event listeners for message input
    const messageInput = document.getElementById('message-input');
    const sendButton = document.getElementById('send-button');
    
    if (messageInput && sendButton) {
        sendButton.addEventListener('click', sendMessage);
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendMessage(e);
            }
        });
    }
}

function loadMessages() {
    if (!currentRoomId) return;
    
    fetch(`api/get_messages.php?room_id=${currentRoomId}`)
        .then(response => response.json())
        .then(data => {
            console.log('Messages loaded:', data);
            const messagesDiv = document.getElementById('chat-messages');
            if (!messagesDiv) return;
            
            if (data.success && data.messages) {
                messagesDiv.innerHTML = data.messages.map(msg => {
                    const isOwnMessage = msg.sender_id == currentUserId;
                    return `
                        <div class="message ${isOwnMessage ? 'own' : 'other'}">
                            <div>${msg.message}</div>
                            <div class="message-info">
                                ${!isOwnMessage ? msg.sender_name + ' • ' : ''}
                                ${new Date(msg.sent_at).toLocaleString()}
                            </div>
                        </div>
                    `;
                }).join('');
                
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            } else {
                console.error('Failed to load messages:', data.error);
            }
        })
        .catch(error => {
            console.error('Error loading messages:', error);
        });
}

function sendMessage(event) {
    event.preventDefault();
    
    const messageInput = document.getElementById('message-input');
    const message = messageInput.value.trim();
    
    if (!message || !currentRoomId) {
        console.log('Cannot send message: missing message or room ID');
        return;
    }
    
    console.log('Sending message:', message, 'to room:', currentRoomId);
    
    fetch('api/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ 
            room_id: currentRoomId, 
            message: message 
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Send message response:', data);
        if (data.success) {
            messageInput.value = '';
            loadMessages();
        } else {
            console.error('Failed to send message:', data.error);
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
    });
}
</script>

<?php require_once "../includes/natural_footer.php"; ?>
