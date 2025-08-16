<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Messages - SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navigation.css" />
    <link rel="stylesheet" href="/assets/css/messages.css" />
    <script src="/assets/js/messages.js" defer></script>
</head>
<body>
    <?php include 'view/nav.view.php'; ?>
    
    <main class="main-container">
        <div class="messages-container">
            <!-- Chat List Sidebar -->
            <aside class="chat-sidebar" id="chatSidebar">
                <header class="sidebar-header">
                    <div class="header-content">
                        <h2>Messages</h2>
                        <button class="new-chat-btn" onclick="openNewChatModal()">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="search-container">
                        <div class="search-input">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search conversations..." id="chatSearch">
                        </div>
                    </div>
                </header>

                <div class="chat-list" id="chatList">
                    <?php if (empty($users)): ?>
                        <div class="empty-state">
                            <i class="fas fa-comments"></i>
                            <h3>No conversations yet</h3>
                            <p>Start a conversation with your friends</p>
                            <button class="btn btn-primary" onclick="openNewChatModal()">
                                <i class="fas fa-plus"></i>
                                New Chat
                            </button>
                        </div>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <div class="chat-item <?= isset($_GET['chat_id']) && $user['chat_id'] == $_GET['chat_id'] ? 'active' : '' ?>" 
                                 data-user-id="<?= $user['id'] ?>"
                                 onclick="openChat(<?= $user['id'] ?>)">
                                <div class="chat-avatar">
                                    <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" 
                                         alt="Avatar"
                                         onerror="this.src='images/profile.jpg'">
                                    <div class="online-indicator"></div>
                                </div>
                                <div class="chat-info">
                                    <div class="chat-header">
                                        <h4 class="chat-name"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h4>
                                        <?php if (!empty($user['last_message_time'])): ?>
                                            <span class="chat-time"><?= date('H:i', strtotime($user['last_message_time'])) ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="chat-preview">
                                        <?php if (!empty($user['last_message'])): ?>
                                            <p class="last-message"><?= htmlspecialchars(substr($user['last_message'], 0, 50)) ?><?= strlen($user['last_message']) > 50 ? '...' : '' ?></p>
                                        <?php else: ?>
                                            <p class="last-message text-muted">No messages yet</p>
                                        <?php endif; ?>
                                        <?php if (!empty($user['unread_count']) && $user['unread_count'] > 0): ?>
                                            <span class="unread-badge"><?= $user['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </aside>

            <!-- Chat Area -->
            <div class="chat-area" id="chatArea">
                <?php if (isset($_GET['chat_id']) && !empty($messages)): ?>
                    <!-- Chat Header -->
                    <header class="chat-header">
                        <button class="back-btn" onclick="closeChatOnMobile()" id="backBtn">
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <div class="chat-user-info">
                            <div class="profile-photo">
                                <img src="<?= htmlspecialchars($chat_partner['avatar'] ?? 'images/profile.jpg') ?>" 
                                     alt="Profile Picture"
                                     onerror="this.src='images/profile.jpg'">
                                <div class="online-indicator active"></div>
                            </div>
                            <div class="user-details">
                                <h3><?= htmlspecialchars($chat_partner['firstName'] . ' ' . $chat_partner['lastName']) ?></h3>
                                <span class="status">Active now</span>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="action-btn" title="Voice call">
                                <i class="fas fa-phone"></i>
                            </button>
                            <button class="action-btn" title="Video call">
                                <i class="fas fa-video"></i>
                            </button>
                            <button class="action-btn" title="More options">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                        </div>
                    </header>

                    <!-- Messages Container -->
                    <div class="messages-container" id="messagesContainer">
                        <div class="messages-list" id="messagesList">
                            <?php foreach ($messages as $message): ?>
                                <div class="message <?= $message['senderId'] == $current_user_id ? 'sent' : 'received' ?>">
                                    <?php if ($message['senderId'] != $current_user_id): ?>
                                        <div class="message-avatar">
                                            <img src="<?= htmlspecialchars($message['avatar'] ?? 'images/profile.jpg') ?>" 
                                                 alt="Avatar"
                                                 onerror="this.src='images/profile.jpg'">
                                        </div>
                                    <?php endif; ?>
                                    <div class="message-content">
                                        <div class="message-bubble">
                                            <p><?= htmlspecialchars($message['content']) ?></p>
                                        </div>
                                        <div class="message-meta">
                                            <span class="message-time"><?= date('H:i', strtotime($message['created_at'])) ?></span>
                                            <?php if ($message['senderId'] == $current_user_id): ?>
                                                <span class="message-status">
                                                    <i class="fas fa-check-double"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="message-input-container">
                        <form id="messageForm" class="message-form" onsubmit="sendMessage(event)">
                            <input type="hidden" name="chat_id" value="<?= $_GET['chat_id'] ?>">
                            <div class="input-wrapper">
                                <button type="button" class="attachment-btn" title="Attach file">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <input type="file" id="imageUpload" name="image" accept="image/*" style="display: none;" onchange="previewImage(event)">
                                <input type="text" 
                                       name="message" 
                                       placeholder="Type a message..." 
                                       class="message-input"
                                       id="messageInput"
                                       autocomplete="off"
                                       required>
                                <button type="button" class="emoji-btn" title="Add emoji">
                                    <i class="fas fa-smile"></i>
                                </button>
                                <button type="submit" class="send-btn" id="sendBtn">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                            <!-- Image Preview -->
                            <div id="imagePreview" class="image-preview" style="display: none;">
                                <div class="preview-container">
                                    <img id="previewImg" src="" alt="Preview">
                                    <button type="button" class="remove-preview" onclick="removeImagePreview()">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <!-- Welcome Screen -->
                    <div class="welcome-screen">
                        <div class="welcome-content">
                            <div class="welcome-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h2>Welcome to Messages</h2>
                            <p>Select a conversation to start messaging or create a new chat</p>
                            <button class="btn btn-primary" onclick="openNewChatModal()">
                                <i class="fas fa-plus"></i>
                                Start New Chat
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- New Chat Modal -->
    <div id="newChatModal" class="modal" style="display: none;">
        <div class="modal-content">
            <header class="modal-header">
                <h3>Start New Chat</h3>
                <button class="close-btn" onclick="closeNewChatModal()">
                    <i class="fas fa-times"></i>
                </button>
            </header>
            <div class="modal-body">
                <div class="search-container">
                    <div class="search-input">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search friends..." id="friendSearch">
                    </div>
                </div>
                <div class="friends-list" id="friendsList">
                    <div class="loading">
                        <i class="fas fa-spinner fa-spin"></i>
                        <p>Loading friends...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        // Pass PHP data to JavaScript
        window.currentUserId = <?= $current_user_id ?>;
        window.currentChatId = <?= isset($_GET['chat_id']) ? $_GET['chat_id'] : 'null' ?>;
        window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
    </script>
</body>
</html>