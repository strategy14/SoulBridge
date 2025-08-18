// Messages functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeMessages();
});

function initializeMessages() {
    // Auto-scroll to bottom of messages
    const messagesArea = document.getElementById('messagesArea');
    if (messagesArea) {
        scrollToBottom(messagesArea);
    }
    
    // Handle message form submission
    const messageForm = document.getElementById('messageForm');
    if (messageForm) {
        messageForm.addEventListener('submit', handleMessageSubmit);
    }
    
    // Handle attachment button
    const attachmentBtn = document.querySelector('.attachment-btn');
    if (attachmentBtn) {
        attachmentBtn.addEventListener('click', () => {
            document.getElementById('messageMedia').click();
        });
    }
    
    // Handle new chat modal
    const newChatBtn = document.getElementById('newChatBtn');
    const startChatBtn = document.getElementById('startChatBtn');
    const newChatModal = document.getElementById('newChatModal');
    const closeModal = document.getElementById('closeModal');
    
    if (newChatBtn) {
        newChatBtn.addEventListener('click', () => showModal(newChatModal));
    }
    
    if (startChatBtn) {
        startChatBtn.addEventListener('click', () => showModal(newChatModal));
    }
    
    if (closeModal) {
        closeModal.addEventListener('click', () => hideModal(newChatModal));
    }
    
    if (newChatModal) {
        newChatModal.addEventListener('click', (e) => {
            if (e.target === newChatModal) {
                hideModal(newChatModal);
            }
        });
    }
    
    // Handle chat search
    const chatSearch = document.getElementById('chatSearch');
    if (chatSearch) {
        chatSearch.addEventListener('input', handleChatSearch);
    }
    
    // Handle user search in modal
    const userSearch = document.getElementById('userSearch');
    if (userSearch) {
        userSearch.addEventListener('input', handleUserSearch);
    }
    
    // Auto-refresh messages if in a chat
    if (chatId) {
        startMessagePolling();
    }
    
    // Handle Enter key in message input
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                messageForm.dispatchEvent(new Event('submit'));
            }
        });
        
        // Auto-focus message input
        messageInput.focus();
    }
}

async function handleMessageSubmit(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    const messageInput = form.querySelector('input[name="message"]');
    const sendBtn = form.querySelector('.send-btn');
    const mediaInput = document.getElementById('messageMedia');
    
    if (!messageInput.value.trim() && !mediaInput.files[0]) {
        showToast('Please enter a message or select media', 'error');
        return;
    }
    
    // Disable form while sending
    messageInput.disabled = true;
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    // Add optimistic message to UI
    const messageText = messageInput.value.trim();
    const mediaFile = mediaInput.files[0];
    
    if (messageText || mediaFile) {
        const optimisticMessage = {
            content: messageText,
            senderId: currentUserId,
            created_at: new Date().toISOString(),
            media_path: mediaFile ? URL.createObjectURL(mediaFile) : null,
            media_type: mediaFile ? (mediaFile.type.includes('gif') ? 'gif' : 'image') : 'text',
            isOptimistic: true
        };
        
        addMessageToUI(optimisticMessage);
        
        // Clear inputs
        messageInput.value = '';
        removeMessageMediaPreview();
        
        // Scroll to bottom
        const messagesArea = document.getElementById('messagesArea');
        if (messagesArea) {
            scrollToBottom(messagesArea);
        }
    }
    
    try {
        const response = await fetch('/sendMessage', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove optimistic message and add real message
            removeOptimisticMessages();
            
            if (data.message) {
                addMessageToUI(data.message);
                scrollToBottom(document.getElementById('messagesArea'));
            }
        } else {
            removeOptimisticMessages();
            throw new Error(data.error || 'Failed to send message');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        removeOptimisticMessages();
        showToast('Failed to send message', 'error');
    } finally {
        // Re-enable form
        messageInput.disabled = false;
        sendBtn.disabled = false;
        sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        messageInput.focus();
    }
}

function removeOptimisticMessages() {
    const optimisticMessages = document.querySelectorAll('.message.optimistic');
    optimisticMessages.forEach(msg => msg.remove());
}

function addMessageToUI(message) {
    const messagesArea = document.getElementById('messagesArea');
    if (!messagesArea) return;
    
    const messageElement = document.createElement('div');
    messageElement.className = `message ${message.senderId == currentUserId ? 'sent' : 'received'}${message.isOptimistic ? ' optimistic' : ''}`;
    
    const time = new Date(message.created_at);
    const timeString = time.toLocaleTimeString('en-US', { 
        hour: '2-digit', 
        minute: '2-digit',
        hour12: false 
    });
    
    let mediaContent = '';
    if (message.media_path) {
        if (message.media_type === 'image' || message.media_type === 'gif') {
            mediaContent = `<img src="${message.media_path}" alt="Shared image" class="message-media" onclick="openImageModal('${message.media_path}')">`;
        }
    }
    
    messageElement.innerHTML = `
        ${message.senderId != currentUserId ? `
            <img src="${message.avatar || 'images/profile.jpg'}" 
                 alt="${message.firstName || 'User'}" 
                 class="message-avatar">
        ` : ''}
        <div class="message-content">
            <div class="message-bubble">
                ${mediaContent}
                ${message.content ? `<div class="message-text">${escapeHtml(message.content)}</div>` : ''}
            </div>
            <div class="message-time">
                ${timeString}
            </div>
        </div>
    `;
    
    messagesArea.appendChild(messageElement);
}

function previewMessageMedia(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    const preview = document.getElementById('messageMediaPreview');
    const previewImg = document.getElementById('previewMessageImg');
    
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewImg.style.display = 'block';
            preview.style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}

function removeMessageMediaPreview() {
    const preview = document.getElementById('messageMediaPreview');
    const previewImg = document.getElementById('previewMessageImg');
    const mediaInput = document.getElementById('messageMedia');
    
    previewImg.src = '';
    preview.style.display = 'none';
    mediaInput.value = '';
}

function handleChatSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const chatItems = document.querySelectorAll('.chat-item');
    
    chatItems.forEach(item => {
        const name = item.querySelector('.chat-name').textContent.toLowerCase();
        const preview = item.querySelector('.chat-preview').textContent.toLowerCase();
        
        if (name.includes(searchTerm) || preview.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function handleUserSearch(e) {
    const searchTerm = e.target.value.toLowerCase();
    const userItems = document.querySelectorAll('.user-item');
    
    userItems.forEach(item => {
        const name = item.querySelector('span').textContent.toLowerCase();
        
        if (name.includes(searchTerm)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
}

function startChat(userId) {
    window.location.href = `/message?start_chat=1&user_id=${userId}`;
}

function showModal(modal) {
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function hideModal(modal) {
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function scrollToBottom(element) {
    element.scrollTop = element.scrollHeight;
}

function showToast(message, type = 'info') {
    // Create toast if it doesn't exist
    let toast = document.getElementById('messageToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'messageToast';
        toast.className = 'toast';
        document.body.appendChild(toast);
    }
    
    toast.textContent = message;
    toast.className = `toast ${type} show`;
    
    // Hide after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Message polling for real-time updates
let pollingInterval;
let lastMessageTime = null;

function startMessagePolling() {
    // Poll every 2 seconds for new messages
    pollingInterval = setInterval(async () => {
        try {
            const response = await fetch(`/api/messages/${chatId}/latest?since=${lastMessageTime || 0}`, {
                headers: {
                    'X-CSRF-Token': window.csrfToken
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                
                if (data.messages && data.messages.length > 0) {
                    data.messages.forEach(message => {
                        // Only add messages from other users (avoid duplicating our own)
                        if (message.senderId != currentUserId) {
                            addMessageToUI(message);
                        }
                        lastMessageTime = message.created_at;
                    });
                    
                    scrollToBottom(document.getElementById('messagesArea'));
                }
            }
        } catch (error) {
            console.error('Error polling messages:', error);
        }
    }, 2000);
}

// Clean up polling when leaving the page
window.addEventListener('beforeunload', () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

// Handle online/offline status
window.addEventListener('online', () => {
    if (chatId && !pollingInterval) {
        startMessagePolling();
    }
});

window.addEventListener('offline', () => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
        pollingInterval = null;
    }
});

// Emoji picker functionality (basic)
document.addEventListener('click', function(e) {
    if (e.target.closest('.emoji-btn')) {
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            // Simple emoji insertion - you can expand this with a proper emoji picker
            const emojis = ['😀', '😂', '😍', '🤔', '👍', '❤️', '🎉', '🔥'];
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            messageInput.value += randomEmoji;
            messageInput.focus();
        }
    }
});

// Add toast styles if not already present
if (!document.querySelector('#toastStyles')) {
    const toastStyles = document.createElement('style');
    toastStyles.id = 'toastStyles';
    toastStyles.textContent = `
        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #14171a;
            color: white;
            padding: 16px 20px;
            border-radius: 12px;
            font-weight: 600;
            z-index: 10000;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .toast.show {
            transform: translateX(0);
        }
        
        .toast.success {
            background: #17bf63;
        }
        
        .toast.error {
            background: #e0245e;
        }
        
        @media (max-width: 480px) {
            .toast {
                bottom: 15px;
                right: 15px;
                left: 15px;
                text-align: center;
            }
        }
    `;
    document.head.appendChild(toastStyles);
}