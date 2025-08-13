// Comments functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeComments();
});

function initializeComments() {
    // Initialize comment form
    initializeCommentForm();
    
    // Initialize comment actions
    initializeCommentActions();
    
    // Initialize sorting
    initializeSorting();
    
    // Auto-resize textarea
    initializeTextareaResize();
}

function initializeCommentForm() {
    const commentForm = document.getElementById('mainCommentForm');
    if (!commentForm) return;
    
    commentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(commentForm);
        const submitBtn = commentForm.querySelector('.submit-btn');
        const textarea = commentForm.querySelector('.comment-textarea');
        
        if (!textarea.value.trim()) {
            showToast('Please write a comment', 'error');
            return;
        }
        
        // Add loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Posting...</span>';
        
        try {
            const response = await fetch('/comment', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                // Clear form
                textarea.value = '';
                textarea.style.height = 'auto';
                
                // Add comment to list
                await addCommentToList(formData.get('comment'));
                
                // Update comment count
                updateCommentCount(1);
                
                showToast('Comment posted successfully!', 'success');
            } else {
                throw new Error(result.error || 'Failed to post comment');
            }
        } catch (error) {
            console.error('Error:', error);
            showToast(error.message || 'Failed to post comment', 'error');
        } finally {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i><span>Post</span>';
        }
    });
}

async function addCommentToList(commentText) {
    const commentsList = document.getElementById('commentsList');
    const emptyState = commentsList.querySelector('.empty-comments');
    
    // Remove empty state if it exists
    if (emptyState) {
        emptyState.remove();
    }
    
    // Create new comment element
    const commentElement = document.createElement('div');
    commentElement.className = 'comment-item';
    commentElement.innerHTML = `
        <div class="comment-avatar-container">
            <img src="${document.querySelector('.comment-avatar').src}" 
                 alt="Your avatar" 
                 class="comment-avatar">
        </div>
        
        <div class="comment-content">
            <div class="comment-bubble">
                <div class="comment-header">
                    <h4 class="commenter-name">You</h4>
                    <time class="comment-time">Just now</time>
                </div>
                <p class="comment-text">${escapeHtml(commentText)}</p>
            </div>
            
            <div class="comment-actions">
                <button class="action-btn like-comment-btn">
                    <i class="far fa-heart"></i>
                    <span>Like</span>
                </button>
                <button class="action-btn reply-btn">
                    <i class="fas fa-reply"></i>
                    <span>Reply</span>
                </button>
                <button class="action-btn delete-btn">
                    <i class="fas fa-trash"></i>
                    <span>Delete</span>
                </button>
            </div>
        </div>
    `;
    
    // Add to top of comments list
    commentsList.insertBefore(commentElement, commentsList.firstChild);
    
    // Animate in
    commentElement.style.opacity = '0';
    commentElement.style.transform = 'translateY(-20px)';
    
    setTimeout(() => {
        commentElement.style.transition = 'all 0.3s ease';
        commentElement.style.opacity = '1';
        commentElement.style.transform = 'translateY(0)';
    }, 100);
    
    // Initialize actions for new comment
    initializeCommentActionsForElement(commentElement);
}

function initializeCommentActions() {
    const commentItems = document.querySelectorAll('.comment-item');
    commentItems.forEach(initializeCommentActionsForElement);
}

function initializeCommentActionsForElement(commentElement) {
    // Like button
    const likeBtn = commentElement.querySelector('.like-comment-btn');
    if (likeBtn) {
        likeBtn.addEventListener('click', () => {
            const icon = likeBtn.querySelector('i');
            const span = likeBtn.querySelector('span');
            
            if (icon.classList.contains('far')) {
                icon.classList.remove('far');
                icon.classList.add('fas');
                icon.style.color = '#e0245e';
                span.textContent = 'Liked';
                likeBtn.style.color = '#e0245e';
            } else {
                icon.classList.remove('fas');
                icon.classList.add('far');
                icon.style.color = '';
                span.textContent = 'Like';
                likeBtn.style.color = '';
            }
        });
    }
    
    // Reply button
    const replyBtn = commentElement.querySelector('.reply-btn');
    if (replyBtn) {
        replyBtn.addEventListener('click', () => {
            // Focus on main comment textarea and add @username
            const textarea = document.querySelector('.comment-textarea');
            const commenterName = commentElement.querySelector('.commenter-name').textContent;
            
            if (textarea) {
                textarea.focus();
                textarea.value = `@${commenterName} `;
                textarea.style.height = 'auto';
                textarea.style.height = textarea.scrollHeight + 'px';
            }
        });
    }
    
    // Delete button
    const deleteBtn = commentElement.querySelector('.delete-btn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', async () => {
            if (!confirm('Are you sure you want to delete this comment?')) {
                return;
            }
            
            const commentId = commentElement.dataset.commentId;
            
            try {
                // Add loading state
                deleteBtn.disabled = true;
                deleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Deleting...</span>';
                
                // Simulate delete request (you'll need to implement the backend)
                await new Promise(resolve => setTimeout(resolve, 1000));
                
                // Remove comment with animation
                commentElement.style.transition = 'all 0.3s ease';
                commentElement.style.opacity = '0';
                commentElement.style.transform = 'translateX(-100%)';
                
                setTimeout(() => {
                    commentElement.remove();
                    updateCommentCount(-1);
                    showToast('Comment deleted', 'success');
                }, 300);
                
            } catch (error) {
                console.error('Error deleting comment:', error);
                deleteBtn.disabled = false;
                deleteBtn.innerHTML = '<i class="fas fa-trash"></i><span>Delete</span>';
                showToast('Failed to delete comment', 'error');
            }
        });
    }
}

function initializeSorting() {
    const sortSelect = document.getElementById('sortComments');
    if (!sortSelect) return;
    
    sortSelect.addEventListener('change', (e) => {
        const sortOrder = e.target.value;
        const commentsList = document.getElementById('commentsList');
        const comments = Array.from(commentsList.querySelectorAll('.comment-item'));
        
        comments.sort((a, b) => {
            const timeA = new Date(a.querySelector('.comment-time').textContent);
            const timeB = new Date(b.querySelector('.comment-time').textContent);
            
            if (sortOrder === 'newest') {
                return timeB - timeA;
            } else {
                return timeA - timeB;
            }
        });
        
        // Re-append sorted comments
        comments.forEach(comment => {
            commentsList.appendChild(comment);
        });
        
        // Add sorting animation
        comments.forEach((comment, index) => {
            comment.style.animation = `slideIn 0.3s ease ${index * 0.05}s both`;
        });
    });
}

function initializeTextareaResize() {
    const textarea = document.querySelector('.comment-textarea');
    if (!textarea) return;
    
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 120) + 'px';
    });
    
    // Handle Enter key
    textarea.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            const form = this.closest('form');
            if (form) {
                form.dispatchEvent(new Event('submit'));
            }
        }
    });
}

function updateCommentCount(change) {
    const commentCountElements = document.querySelectorAll('.stat-item span, .comments-header-section h2');
    
    commentCountElements.forEach(element => {
        const text = element.textContent;
        const currentCount = parseInt(text.match(/\d+/)?.[0] || '0');
        const newCount = Math.max(0, currentCount + change);
        
        if (element.tagName === 'H2') {
            element.textContent = `Comments (${newCount})`;
        } else {
            element.textContent = text.replace(/\d+/, newCount);
        }
    });
}

function showToast(message, type = 'info') {
    const toast = document.getElementById('toast');
    if (!toast) return;
    
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.classList.add('show');
    
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

// Emoji picker functionality (basic)
document.addEventListener('click', function(e) {
    if (e.target.closest('.emoji-btn')) {
        const textarea = document.querySelector('.comment-textarea');
        if (textarea) {
            // Simple emoji insertion - you can expand this with a proper emoji picker
            const emojis = ['😀', '😂', '😍', '🤔', '👍', '❤️', '🎉', '🔥', '💯', '🙌'];
            const randomEmoji = emojis[Math.floor(Math.random() * emojis.length)];
            
            const cursorPos = textarea.selectionStart;
            const textBefore = textarea.value.substring(0, cursorPos);
            const textAfter = textarea.value.substring(cursorPos);
            
            textarea.value = textBefore + randomEmoji + textAfter;
            textarea.focus();
            textarea.setSelectionRange(cursorPos + randomEmoji.length, cursorPos + randomEmoji.length);
            
            // Trigger resize
            textarea.style.height = 'auto';
            textarea.style.height = Math.min(textarea.scrollHeight, 120) + 'px';
        }
    }
});

// Add slide-in animation keyframes
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
`;
document.head.appendChild(style);

// Auto-focus textarea on page load
window.addEventListener('load', () => {
    const textarea = document.querySelector('.comment-textarea');
    if (textarea) {
        textarea.focus();
    }
});