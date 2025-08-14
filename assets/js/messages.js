/**
 * Messages JavaScript
 * Handles messaging functionality, real-time updates, and UI interactions
 */

// Global variables
let messagePollingInterval = null;
let isTyping = false;
let typingTimeout = null;

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeMessages();
});

/**
 * Initialize messages functionality
 */
function initializeMessages() {
    initializeMessagePolling();
    initializeChatSearch();
    initializeMessageInput();
    initializeResponsiveHandlers();
    scrollToBottom();
    
    // Auto-focus message input if chat is active
    const messageInput = document.getElementById('messageInput');
    if (messageInput && window.currentChatId) {
        messageInput.focus();
    }
}

/**
 * Initialize message polling for real-time updates
 */
function initializeMessagePolling() {
    if (window.currentChatId) {
        // Poll for new messages every 3 seconds
        messagePollingInterval = setInterval(pollForNewMessages, 3000);
        
        // Poll for typing indicators every 1 second
        setInterval(pollTypingIndicators, 1000);
    }
}

/**
 * Poll for new messages
 */
async function pollForNewMessages() {
    if (!window.currentChatId) return;
    
    try {
        const response = await fetch(`/api/messages/${window.currentChatId}/poll`, {
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            
            if (data.newMessages && data.newMessages.length > 0) {
                appendNewMessages(data.newMessages);
                markMessagesAsRead();
            }
        }
    } catch (error) {
        console.error('Failed to poll for new messages:', error);
    }
}

/**
 * Poll for typing indicators
 */
async function pollTypingIndicators() {
    if (!window.currentChatId) return;
    
    try {
        const response = await fetch(`/api/chat/${window.currentChatId}/typing`, {
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            updateTypingIndicator(data.isTyping, data.userName);
        }
    } catch (error) {
        console.error('Failed to poll typing indicators:', error);
    }
}

/**
 * Initialize chat search functionality
 */
function initializeChatSearch() {
    const chatSearch = document.getElementById('chatSearch');
    if (chatSearch) {
        chatSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterChats(searchTerm);
        });
    }
}

/**
 * Filter chat list based on search term
 */
function filterChats(searchTerm) {
    const chatItems = document.querySelectorAll('.chat-item');
    
    chatItems.forEach(item => {
        const chatName = item.querySelector('.chat-name').textContent.toLowerCase();
        const lastMessage = item.querySelector('.last-message').textContent.toLowerCase();
        
        if (chatName.includes(searchTerm) || lastMessage.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Initialize message input functionality
 */
function initializeMessageInput() {
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        // Handle typing indicators
        messageInput.addEventListener('input', handleTyping);
        messageInput.addEventListener('keydown', handleKeyDown);
    }
}

/**
 * Handle typing indicators
 */
function handleTyping() {
    if (!isTyping) {
        isTyping = true;
        sendTypingIndicator(true);
    }
    
    // Clear existing timeout
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }
    
    // Set new timeout to stop typing indicator
    typingTimeout = setTimeout(() => {
        isTyping = false;
        sendTypingIndicator(false);
    }, 2000);
}

/**
 * Handle keyboard shortcuts
 */
function handleKeyDown(event) {
    // Send message on Enter (but not Shift+Enter)
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        const form = event.target.closest('form');
        if (form) {
            sendMessage({ target: form, preventDefault: () => {} });
        }
    }
}

/**
 * Send typing indicator
 */
async function sendTypingIndicator(typing) {
    if (!window.currentChatId) return;
    
    try {
        await fetch('/api/typing', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken
            },
            body: JSON.stringify({
                chat_id: window.currentChatId,
                typing: typing
            })
        });
    } catch (error) {
        console.error('Failed to send typing indicator:', error);
    }
}

/**
 * Update typing indicator display
 */
function updateTypingIndicator(isTyping, userName) {
    let typingIndicator = document.getElementById('typingIndicator');
    
    if (isTyping && userName) {
        if (!typingIndicator) {
            typingIndicator = document.createElement('div');
            typingIndicator.id = 'typingIndicator';
            typingIndicator.className = 'typing-indicator';
            
            const messagesList = document.getElementById('messagesList');
            messagesList.appendChild(typingIndicator);
        }
        
        typingIndicator.innerHTML = `
            <div class="message received">
                <div class="message-content">
                    <div class="message-bubble">
                        <div class="typing-dots">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                    <div class="message-meta">
                        <span class="message-time">${userName} is typing...</span>
                    </div>
                </div>
            </div>
        `;
        
        scrollToBottom();
    } else if (typingIndicator) {
        typingIndicator.remove();
    }
}

/**
 * Initialize responsive handlers
 */
function initializeResponsiveHandlers() {
    // Handle window resize
    window.addEventListener('resize', handleResize);
    
    // Initial check
    handleResize();
}

/**
 * Handle window resize
 */
function handleResize() {
    const isMobile = window.innerWidth <= 768;
    const chatSidebar = document.getElementById('chatSidebar');
    const chatArea = document.getElementById('chatArea');
    const backBtn = document.getElementById('backBtn');
    
    if (isMobile) {
        if (window.currentChatId) {
            // Show chat area, hide sidebar
            chatSidebar.classList.add('hidden');
            chatArea.classList.add('active');
            if (backBtn) backBtn.style.display = 'flex';
        } else {
            // Show sidebar, hide chat area
            chatSidebar.classList.remove('hidden');
            chatArea.classList.remove('active');
        }
    } else {
        // Desktop view - show both
        chatSidebar.classList.remove('hidden');
        chatArea.classList.remove('active');
        if (backBtn) backBtn.style.display = 'none';
    }
}

/**
 * Open chat with user
 */
function openChat(userId) {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        // On mobile, navigate to chat URL
        window.location.href = `/message?start_chat=1&user_id=${userId}`;
    } else {
        // On desktop, update URL and reload
        window.location.href = `/message?start_chat=1&user_id=${userId}`;
    }
}

/**
 * Close chat on mobile
 */
function closeChatOnMobile() {
    const isMobile = window.innerWidth <= 768;
    
    if (isMobile) {
        window.location.href = '/message';
    }
}

/**
 * Send message
 */
async function sendMessage(event) {
    event.preventDefault();
    
    const form = event.target;
    const messageInput = form.querySelector('.message-input');
    const sendBtn = form.querySelector('.send-btn');
    const message = messageInput.value.trim();
    
    if (!message) {
        showToast('error', 'Please enter a message');
        return;
    }
    
    // Disable send button and show loading
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Stop typing indicator
    if (isTyping) {
        isTyping = false;
        sendTypingIndicator(false);
    }
    
    // Optimistically add message to UI
    const optimisticMessage = {
        content: message,
        senderId: window.currentUserId,
        created_at: new Date().toISOString(),
        isOptimistic: true
    };
    
    appendMessage(optimisticMessage);
    messageInput.value = '';
    scrollToBottom();
    
    try {
        const formData = new FormData(form);
        
        const response = await fetch('/sendMessage', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            // Message sent successfully
            showToast('success', 'Message sent');
            
            // Remove optimistic message and let polling add the real one
            removeOptimisticMessages();
        } else {
            throw new Error('Failed to send message');
        }
    } catch (error) {
        console.error('Send message error:', error);
        showToast('error', 'Failed to send message');
        
        // Remove optimistic message on error
        removeOptimisticMessages();
    } finally {
        // Re-enable send button
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    }
}

/**
 * Append new messages to the chat
 */
function appendNewMessages(messages) {
    const messagesList = document.getElementById('messagesList');
    if (!messagesList) return;
    
    messages.forEach(message => {
        appendMessage(message);
    });
    
    scrollToBottom();
}

/**
 * Append a single message
 */
function appendMessage(message) {
    const messagesList = document.getElementById('messagesList');
    if (!messagesList) return;
    
    const messageElement = createMessageElement(message);
    messagesList.appendChild(messageElement);
}

/**
 * Create message element
 */
function createMessageElement(message) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${message.senderId == window.currentUserId ? 'sent' : 'received'}`;
    
    if (message.isOptimistic) {
        messageDiv.classList.add('optimistic');
    }
    
    const time = new Date(message.created_at).toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        hour12: false
    });
    
    let avatarHtml = '';
    if (message.senderId != window.currentUserId) {
        avatarHtml = `
            <div class="message-avatar">
                <img src="${message.avatar || 'images/profile.jpg'}" 
                     alt="Avatar"
                     onerror="this.src='images/profile.jpg'">
            </div>
        `;
    }
    
    let statusHtml = '';
    if (message.senderId == window.currentUserId) {
        statusHtml = `
            <span class="message-status">
                <i class="fas fa-check-double"></i>
            </span>
        `;
    }
    
    messageDiv.innerHTML = `
        ${avatarHtml}
        <div class="message-content">
            <div class="message-bubble">
                <p>${escapeHtml(message.content)}</p>
            </div>
            <div class="message-meta">
                <span class="message-time">${time}</span>
                ${statusHtml}
            </div>
        </div>
    `;
    
    return messageDiv;
}

/**
 * Remove optimistic messages
 */
function removeOptimisticMessages() {
    const optimisticMessages = document.querySelectorAll('.message.optimistic');
    optimisticMessages.forEach(message => message.remove());
}

/**
 * Mark messages as read
 */
async function markMessagesAsRead() {
    if (!window.currentChatId) return;
    
    try {
        await fetch('/api/messages/mark-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': window.csrfToken
            },
            body: JSON.stringify({
                chat_id: window.currentChatId
            })
        });
    } catch (error) {
        console.error('Failed to mark messages as read:', error);
    }
}

/**
 * Scroll to bottom of messages
 */
function scrollToBottom() {
    const messagesList = document.getElementById('messagesList');
    if (messagesList) {
        messagesList.scrollTop = messagesList.scrollHeight;
    }
}

/**
 * Open new chat modal
 */
async function openNewChatModal() {
    const modal = document.getElementById('newChatModal');
    const friendsList = document.getElementById('friendsList');
    
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Load friends list
    try {
        friendsList.innerHTML = `
            <div class="loading">
                <i class="fas fa-spinner fa-spin"></i>
                <p>Loading friends...</p>
            </div>
        `;
        
        const response = await fetch('/api/friends', {
            headers: {
                'X-CSRF-Token': window.csrfToken
            }
        });
        
        if (response.ok) {
            const friends = await response.json();
            displayFriendsList(friends);
        } else {
            throw new Error('Failed to load friends');
        }
    } catch (error) {
        console.error('Failed to load friends:', error);
        friendsList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-exclamation-circle"></i>
                <p>Failed to load friends</p>
            </div>
        `;
    }
}

/**
 * Display friends list in modal
 */
function displayFriendsList(friends) {
    const friendsList = document.getElementById('friendsList');
    
    if (friends.length === 0) {
        friendsList.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-user-friends"></i>
                <p>No friends to chat with</p>
            </div>
        `;
        return;
    }
    
    friendsList.innerHTML = friends.map(friend => `
        <div class="friend-item" onclick="startChatWithFriend(${friend.id})">
            <img src="${friend.avatar || 'images/profile.jpg'}" 
                 alt="Avatar"
                 onerror="this.src='images/profile.jpg'">
            <div class="friend-info">
                <h4>${escapeHtml(friend.firstName + ' ' + friend.lastName)}</h4>
                <p>Click to start chatting</p>
            </div>
        </div>
    `).join('');
}

/**
 * Start chat with friend
 */
function startChatWithFriend(friendId) {
    closeNewChatModal();
    openChat(friendId);
}

/**
 * Close new chat modal
 */
function closeNewChatModal() {
    const modal = document.getElementById('newChatModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

/**
 * Initialize friend search in modal
 */
function initializeFriendSearch() {
    const friendSearch = document.getElementById('friendSearch');
    if (friendSearch) {
        friendSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            filterFriends(searchTerm);
        });
    }
}

/**
 * Filter friends list
 */
function filterFriends(searchTerm) {
    const friendItems = document.querySelectorAll('.friend-item');
    
    friendItems.forEach(item => {
        const friendName = item.querySelector('h4').textContent.toLowerCase();
        
        if (friendName.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

/**
 * Show toast notification
 */
function showToast(type, message) {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    
    const icon = type === 'success' ? 'check-circle' : 
                 type === 'error' ? 'exclamation-circle' : 'info-circle';
    
    toast.innerHTML = `
        <i class="fas fa-${icon}"></i>
        <span>${message}</span>
    `;
    
    container.appendChild(toast);
    
    // Auto-remove after 4 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.animation = 'slideOut 0.3s ease forwards';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }
    }, 4000);
}

/**
 * Escape HTML to prevent XSS
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Cleanup on page unload
window.addEventListener('beforeunload', function() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    if (typingTimeout) {
        clearTimeout(typingTimeout);
    }
    
    // Send stop typing indicator
    if (isTyping) {
        sendTypingIndicator(false);
    }
});

// Add CSS for typing indicator
const style = document.createElement('style');
style.textContent = `
    .typing-indicator {
        animation: fadeIn 0.3s ease;
    }
    
    .typing-dots {
        display: flex;
        gap: 4px;
        padding: 8px 0;
    }
    
    .typing-dots span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.4;
        animation: typingDot 1.4s infinite;
    }
    
    .typing-dots span:nth-child(2) {
        animation-delay: 0.2s;
    }
    
    .typing-dots span:nth-child(3) {
        animation-delay: 0.4s;
    }
    
    @keyframes typingDot {
        0%, 60%, 100% {
            opacity: 0.4;
            transform: scale(1);
        }
        30% {
            opacity: 1;
            transform: scale(1.2);
        }
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes slideOut {
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .message.optimistic {
        opacity: 0.7;
    }
`;
document.head.appendChild(style);