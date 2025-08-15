// Profile functionality
document.addEventListener('DOMContentLoaded', function() {
    initializeProfile();
});

function initializeProfile() {
    // Initialize friend request buttons
    initializeFriendRequests();
    
    // Initialize like functionality
    initializeLikes();
    
    // Initialize comment forms
    initializeComments();
    
    // Initialize post filters
    initializePostFilters();
    
    // Initialize image modal
    initializeImageModal();
}

function initializeFriendRequests() {
    // Add Friend
    const addFriendBtns = document.querySelectorAll('.add-friend-btn');
    addFriendBtns.forEach(btn => {
        btn.addEventListener('click', () => handleFriendRequest('send', btn.dataset.userId, btn));
    });
    
    // Accept Request
    const acceptBtns = document.querySelectorAll('.accept-request-btn');
    acceptBtns.forEach(btn => {
        btn.addEventListener('click', () => handleFriendRequest('accept', btn.dataset.userId, btn));
    });
    
    // Decline Request
    const declineBtns = document.querySelectorAll('.decline-request-btn');
    declineBtns.forEach(btn => {
        btn.addEventListener('click', () => handleFriendRequest('decline', btn.dataset.userId, btn));
    });
    
    // Cancel Request
    const cancelBtns = document.querySelectorAll('.cancel-request-btn');
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', () => handleFriendRequest('cancel', btn.dataset.userId, btn));
    });
    
    // Unfriend
    const unfriendBtns = document.querySelectorAll('.unfriend-btn');
    unfriendBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            if (confirm('Are you sure you want to unfriend this person?')) {
                handleFriendRequest('unfriend', btn.dataset.userId, btn);
            }
        });
    });
}

async function handleFriendRequest(action, userId, button) {
    if (!userId || !button) return;
    
    // Add loading state
    button.classList.add('loading');
    const originalContent = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner"></i> Processing...';
    button.disabled = true;
    
    try {
        const response = await fetch('/friendRequest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: action,
                user_id: parseInt(userId)
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showToast(result.message, 'success');
            
            // Update button based on action
            updateButtonAfterAction(action, button);
            
            // Reload page after a short delay to reflect changes
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            throw new Error(result.error || 'Action failed');
        }
    } catch (error) {
        console.error('Error:', error);
        button.innerHTML = originalContent;
        button.disabled = false;
        showToast(error.message || 'An error occurred', 'error');
    } finally {
        button.classList.remove('loading');
    }
}

function updateButtonAfterAction(action, button) {
    switch(action) {
        case 'send':
            button.innerHTML = '<i class="fas fa-clock"></i> Request Sent';
            button.style.background = '#657786';
            break;
        case 'accept':
            button.innerHTML = '<i class="fas fa-check"></i> Friends';
            button.style.background = '#17bf63';
            break;
        case 'decline':
        case 'cancel':
            button.innerHTML = '<i class="fas fa-check"></i> Cancelled';
            button.style.background = '#657786';
            break;
        case 'unfriend':
            button.innerHTML = '<i class="fas fa-check"></i> Unfriended';
            button.style.background = '#657786';
            break;
    }
    button.disabled = true;
}

function initializeLikes() {
    const likeBtns = document.querySelectorAll('.like-btn');
    likeBtns.forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();
            
            const postId = btn.dataset.postId;
            const heartIcon = btn.querySelector('i');
            const likeCountSpan = btn.querySelector('.like-count');
            
            if (!postId) return;
            
            const isLiked = heartIcon.classList.contains('fas');
            const action = isLiked ? 'unlike' : 'like';
            
            // Optimistic UI update
            if (isLiked) {
                heartIcon.classList.remove('fas', 'liked');
                heartIcon.classList.add('far');
                likeCountSpan.textContent = parseInt(likeCountSpan.textContent) - 1;
            } else {
                heartIcon.classList.remove('far');
                heartIcon.classList.add('fas', 'liked');
                likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + 1;
            }
            
            try {
                const response = await fetch('/like', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        post_id: parseInt(postId),
                        action: action
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Update with actual count from server
                    likeCountSpan.textContent = result.like_count;
                    
                    // Ensure icon state matches server response
                    if (result.liked) {
                        heartIcon.classList.remove('far');
                        heartIcon.classList.add('fas', 'liked');
                    } else {
                        heartIcon.classList.remove('fas', 'liked');
                        heartIcon.classList.add('far');
                    }
                } else {
                    throw new Error(result.error || 'Like action failed');
                }
            } catch (error) {
                console.error('Error:', error);
                
                // Revert optimistic update on error
                if (action === 'like') {
                    heartIcon.classList.remove('fas', 'liked');
                    heartIcon.classList.add('far');
                    likeCountSpan.textContent = parseInt(likeCountSpan.textContent) - 1;
                } else {
                    heartIcon.classList.remove('far');
                    heartIcon.classList.add('fas', 'liked');
                    likeCountSpan.textContent = parseInt(likeCountSpan.textContent) + 1;
                }
                
                showToast('Failed to update like', 'error');
            }
        });
    });
}

function initializeComments() {
    const commentForms = document.querySelectorAll('.comment-form');
    commentForms.forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = new FormData(form);
            const submitBtn = form.querySelector('.comment-submit-btn');
            const commentInput = form.querySelector('.comment-input');
            
            if (!commentInput.value.trim()) return;
            
            // Add loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
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
                    commentInput.value = '';
                    
                    // Update comment count
                    const postId = formData.get('post_id');
                    const commentBtn = document.querySelector(`[data-post-id="${postId}"] .comment-btn`);
                    if (commentBtn) {
                        const countSpan = commentBtn.querySelector('.comment-count');
                        if (countSpan) {
                            countSpan.textContent = parseInt(countSpan.textContent) + 1;
                        }
                    }
                    
                    showToast('Comment added successfully!', 'success');
                } else {
                    throw new Error('Failed to add comment');
                }
            } catch (error) {
                console.error('Error:', error);
                showToast('Failed to add comment', 'error');
            } finally {
                // Reset button
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
            }
        });
    });
}

function initializePostFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    const postCards = document.querySelectorAll('.post-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Update active filter
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            
            const filter = btn.dataset.filter;
            
            // Filter posts
            postCards.forEach(card => {
                const hasPhoto = card.querySelector('.post-image');
                const hasVideo = card.querySelector('.post-video');
                
                let show = true;
                
                if (filter === 'photos' && !hasPhoto) {
                    show = false;
                } else if (filter === 'videos' && !hasVideo) {
                    show = false;
                }
                
                card.style.display = show ? 'block' : 'none';
            });
        });
    });
}

function initializeImageModal() {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (!modal || !modalImage) return;
    
    // Close modal on backdrop click
    modal.addEventListener('click', (e) => {
        if (e.target === modal || e.target.classList.contains('modal-backdrop')) {
            closeImageModal();
        }
    });
    
    // Close modal on escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            closeImageModal();
        }
    });
}

function openImageModal(imageSrc) {
    const modal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    
    if (modal && modalImage) {
        modalImage.src = imageSrc;
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
    }
}

function openComments(postId) {
    // Redirect to comments page
    window.location.href = `/comments?post_id=${postId}`;
}

function startChat(userId) {
    window.location.href = `/message?start_chat=1&user_id=${userId}`;
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

// Smooth scroll to top when clicking stats
document.addEventListener('click', function(e) {
    if (e.target.closest('.stat-item')) {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
});

// Lazy loading for images
if ('IntersectionObserver' in window) {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;
                img.src = img.dataset.src;
                img.classList.remove('lazy');
                observer.unobserve(img);
            }
        });
    });
    
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
}
// /**
//  * Home Page JavaScript
//  * Handles post interactions, real-time updates, and UI functionality
//  */

// // Global variables
// let currentStoryIndex = 0;
// let stories = [];
// let isStoryPlaying = false;
// let storyTimer = null;
// let sharePostId = null;

// // Initialize when DOM is loaded
// document.addEventListener('DOMContentLoaded', function() {
//     initializeHomePage();
//     initializeRealTimeUpdates();
//     loadStories();
// });

// /**
//  * Initialize home page functionality
//  */
// function initializeHomePage() {
//     initializePostInteractions();
//     initializeMediaPreview();
//     initializePrivacyToggle();
//     initializeFriendRequests();
//     initializeInfiniteScroll();
//     initializeShareFunctionality();
// }

// /**
//  * Initialize post interaction handlers
//  */
// function initializePostInteractions() {
//     // Like button handlers
//     document.addEventListener('click', function(e) {
//         if (e.target.closest('.like-btn')) {
//             e.preventDefault();
//             const likeBtn = e.target.closest('.like-btn');
//             const postId = likeBtn.dataset.postId;
//             toggleLike(postId);
//         }
        
//         // Share button handlers
//         if (e.target.closest('.share-btn')) {
//             e.preventDefault();
//             const shareBtn = e.target.closest('.share-btn');
//             const postId = shareBtn.closest('.post-card').dataset.postId;
//             openShareModal(postId);
//         }
//     });
    
//     // Comment form handlers
//     document.addEventListener('submit', function(e) {
//         if (e.target.classList.contains('comment-form')) {
//             e.preventDefault();
//             const form = e.target;
//             const postId = form.closest('.post-card').dataset.postId;
//             submitComment(e, postId);
//         }
//     });
// }

// /**
//  * Toggle like status for a post
//  */
// async function toggleLike(postId) {
//     const likeBtn = document.querySelector(`[data-post-id="${postId}"].like-btn`);
//     const heartIcon = likeBtn.querySelector('i');
//     const likeCountSpan = likeBtn.closest('.post-card').querySelector('.like-count');
    
//     const isLiked = likeBtn.classList.contains('liked');
//     const action = isLiked ? 'unlike' : 'like';
    
//     // Optimistic UI update
//     updateLikeUI(likeBtn, heartIcon, likeCountSpan, !isLiked);
    
//     try {
//         const response = await fetch('/like', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-Token': window.csrfToken
//             },
//             body: JSON.stringify({
//                 post_id: parseInt(postId),
//                 action: action
//             })
//         });
        
//         const data = await response.json();
        
//         if (data.success) {
//             // Update with server response
//             updateLikeUI(likeBtn, heartIcon, likeCountSpan, data.liked, data.like_count);
//             showToast('success', isLiked ? 'Post unliked' : 'Post liked');
//         } else {
//             // Revert optimistic update on error
//             updateLikeUI(likeBtn, heartIcon, likeCountSpan, isLiked);
//             showToast('error', 'Failed to update like');
//         }
//     } catch (error) {
//         console.error('Like error:', error);
//         // Revert optimistic update on error
//         updateLikeUI(likeBtn, heartIcon, likeCountSpan, isLiked);
//         showToast('error', 'Network error occurred');
//     }
// }

// /**
//  * Update like button UI
//  */
// function updateLikeUI(likeBtn, heartIcon, likeCountSpan, isLiked, likeCount = null) {
//     if (isLiked) {
//         likeBtn.classList.add('liked');
//         heartIcon.classList.remove('far');
//         heartIcon.classList.add('fas');
//     } else {
//         likeBtn.classList.remove('liked');
//         heartIcon.classList.remove('fas');
//         heartIcon.classList.add('far');
//     }
    
//     if (likeCount !== null) {
//         likeCountSpan.textContent = likeCount;
//     } else {
//         // Optimistic update
//         const currentCount = parseInt(likeCountSpan.textContent);
//         likeCountSpan.textContent = isLiked ? currentCount + 1 : currentCount - 1;
//     }
// }

// /**
//  * Submit comment for a post
//  */
// async function submitComment(event, postId) {
//     const form = event.target;
//     const commentInput = form.querySelector('.comment-input');
//     const comment = commentInput.value.trim();
    
//     if (!comment) {
//         showToast('error', 'Please enter a comment');
//         return;
//     }
    
//     const formData = new FormData();
//     formData.append('post_id', postId);
//     formData.append('comment', comment);
//     formData.append('csrf_token', window.csrfToken);
    
//     try {
//         const response = await fetch('/comment', {
//             method: 'POST',
//             headers: {
//                 'X-Requested-With': 'XMLHttpRequest'
//             },
//             body: formData
//         });
        
//         const data = await response.json();
        
//         if (data.success) {
//             commentInput.value = '';
//             updateCommentCount(postId);
//             showToast('success', 'Comment added successfully');
//         } else {
//             showToast('error', 'Failed to add comment');
//         }
//     } catch (error) {
//         console.error('Comment error:', error);
//         showToast('error', 'Network error occurred');
//     }
// }

// /**
//  * Update comment count for a post
//  */
// function updateCommentCount(postId) {
//     const postCard = document.querySelector(`[data-post-id="${postId}"]`);
//     const commentCountSpan = postCard.querySelector('.comment-count');
//     const currentCount = parseInt(commentCountSpan.textContent);
//     commentCountSpan.textContent = currentCount + 1;
// }

// /**
//  * Initialize share functionality
//  */
// function initializeShareFunctionality() {
//     // Share modal close handlers
//     document.addEventListener('click', function(e) {
//         if (e.target.closest('.share-modal-backdrop') || e.target.closest('.close-share-btn')) {
//             closeShareModal();
//         }
//     });
// }

// /**
//  * Open share modal
//  */
// function openShareModal(postId) {
//     sharePostId = postId;
//     const modal = document.getElementById('shareModal');
//     modal.style.display = 'flex';
//     document.body.style.overflow = 'hidden';
// }

// /**
//  * Close share modal
//  */
// function closeShareModal() {
//     const modal = document.getElementById('shareModal');
//     modal.style.display = 'none';
//     document.body.style.overflow = 'auto';
//     sharePostId = null;
// }

// /**
//  * Copy post link to clipboard
//  */
// async function copyPostLink() {
//     if (!sharePostId) return;
    
//     const postUrl = `${window.location.origin}/comments?post_id=${sharePostId}`;
    
//     try {
//         await navigator.clipboard.writeText(postUrl);
//         showToast('success', 'Post link copied to clipboard');
//         closeShareModal();
//     } catch (error) {
//         console.error('Copy error:', error);
//         showToast('error', 'Failed to copy link');
//     }
// }

// /**
//  * Share post to friends
//  */
// function shareToFriends() {
//     if (!sharePostId) return;
    
//     // For now, just copy the link - can be enhanced later
//     copyPostLink();
// }

// /**
//  * Initialize media preview functionality
//  */
// function initializeMediaPreview() {
//     const fileInput = document.getElementById('imagefile');
//     if (fileInput) {
//         fileInput.addEventListener('change', previewMedia);
//     }
// }

// /**
//  * Preview selected media file
//  */
// function previewMedia(event) {
//     const file = event.target.files[0];
//     const preview = document.getElementById('mediaPreview');
//     const previewImg = document.getElementById('previewImg');
//     const previewVideo = document.getElementById('previewVideo');
    
//     if (file) {
//         const reader = new FileReader();
//         reader.onload = function(e) {
//             if (file.type.startsWith('video/')) {
//                 previewVideo.src = e.target.result;
//                 previewVideo.style.display = 'block';
//                 previewImg.style.display = 'none';
//             } else {
//                 previewImg.src = e.target.result;
//                 previewImg.style.display = 'block';
//                 previewVideo.style.display = 'none';
//             }
//             preview.style.display = 'block';
//         };
//         reader.readAsDataURL(file);
//     }
// }

// /**
//  * Remove media preview
//  */
// function removeMediaPreview() {
//     const preview = document.getElementById('mediaPreview');
//     const previewImg = document.getElementById('previewImg');
//     const previewVideo = document.getElementById('previewVideo');
//     const fileInput = document.getElementById('imagefile');
    
//     previewImg.src = '';
//     previewVideo.src = '';
//     preview.style.display = 'none';
//     fileInput.value = '';
// }

// /**
//  * Initialize privacy toggle
//  */
// function initializePrivacyToggle() {
//     const privacyCheckbox = document.getElementById('privacy-checkbox');
//     const privacyIcon = document.querySelector('.privacy-icon');
//     const privacyText = document.querySelector('.privacy-text');
    
//     if (privacyCheckbox) {
//         privacyCheckbox.addEventListener('change', function() {
//             if (this.checked) {
//                 privacyIcon.className = 'fas fa-globe privacy-icon';
//                 privacyText.textContent = 'Public';
//             } else {
//                 privacyIcon.className = 'fas fa-user-friends privacy-icon';
//                 privacyText.textContent = 'Friends';
//             }
//         });
//     }
// }

// /**
//  * Initialize friend request handlers
//  */
// function initializeFriendRequests() {
//     document.addEventListener('click', function(e) {
//         if (e.target.classList.contains('accept-btn')) {
//             const userId = e.target.closest('.friend-request').dataset.userId;
//             handleFriendRequest('accept', userId);
//         } else if (e.target.classList.contains('decline-btn')) {
//             const userId = e.target.closest('.friend-request').dataset.userId;
//             handleFriendRequest('decline', userId);
//         }
//     });
// }

// /**
//  * Handle friend request actions
//  */
// async function handleFriendRequest(action, userId) {
//     try {
//         const response = await fetch('/friendRequest', {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'X-CSRF-Token': window.csrfToken
//             },
//             body: JSON.stringify({
//                 action: action,
//                 user_id: parseInt(userId)
//             })
//         });
        
//         const data = await response.json();
        
//         if (data.success) {
//             // Remove the friend request from UI
//             const requestElement = document.querySelector(`[data-user-id="${userId}"]`);
//             if (requestElement) {
//                 requestElement.remove();
//                 updateFriendRequestCount(-1);
//             }
//             showToast('success', data.message);
//         } else {
//             showToast('error', data.error || 'Action failed');
//         }
//     } catch (error) {
//         console.error('Friend request error:', error);
//         showToast('error', 'Network error occurred');
//     }
// }

// /**
//  * Update friend request count
//  */
// function updateFriendRequestCount(change) {
//     const badge = document.querySelector('.sidebar-card .badge');
//     if (badge) {
//         const currentCount = parseInt(badge.textContent);
//         const newCount = Math.max(0, currentCount + change);
//         badge.textContent = newCount;
        
//         if (newCount === 0) {
//             badge.style.display = 'none';
//         }
//     }
// }

// /**
//  * Initialize infinite scroll
//  */
// function initializeInfiniteScroll() {
//     const loadMoreBtn = document.querySelector('.load-more-btn');
//     if (loadMoreBtn) {
//         loadMoreBtn.addEventListener('click', loadMorePosts);
//     }
    
//     // Auto-load when near bottom
//     window.addEventListener('scroll', function() {
//         if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 1000) {
//             loadMorePosts();
//         }
//     });
// }

// /**
//  * Load more posts
//  */
// async function loadMorePosts() {
//     const loadMoreBtn = document.querySelector('.load-more-btn');
//     const originalText = loadMoreBtn.innerHTML;
    
//     loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
//     loadMoreBtn.disabled = true;
    
//     try {
//         // Simulate loading more posts
//         await new Promise(resolve => setTimeout(resolve, 1000));
        
//         showToast('info', 'No more posts to load');
//     } catch (error) {
//         console.error('Load more error:', error);
//         showToast('error', 'Failed to load more posts');
//     } finally {
//         loadMoreBtn.innerHTML = originalText;
//         loadMoreBtn.disabled = false;
//     }
// }

// /**
//  * Initialize real-time updates
//  */
// function initializeRealTimeUpdates() {
//     // Poll for new notifications every 30 seconds
//     setInterval(updateNotificationCounts, 30000);
    
//     // Poll for new posts every 60 seconds
//     setInterval(checkForNewPosts, 60000);
// }

// /**
//  * Update notification counts
//  */
// async function updateNotificationCounts() {
//     try {
//         const response = await fetch('/api/notification-counts', {
//             headers: {
//                 'X-CSRF-Token': window.csrfToken
//             }
//         });
        
//         if (response.ok) {
//             const data = await response.json();
            
//             // Update notification badge
//             const notificationBadge = document.getElementById('notification-count');
//             if (data.notifications > 0) {
//                 if (notificationBadge) {
//                     notificationBadge.textContent = data.notifications;
//                     notificationBadge.style.display = 'inline';
//                 } else {
//                     // Create badge if it doesn't exist
//                     const notificationsLink = document.getElementById('notifications-link');
//                     const badge = document.createElement('span');
//                     badge.className = 'notification-badge';
//                     badge.id = 'notification-count';
//                     badge.textContent = data.notifications;
//                     notificationsLink.appendChild(badge);
//                 }
//             } else if (notificationBadge) {
//                 notificationBadge.style.display = 'none';
//             }
            
//             // Update message badge
//             const messageBadge = document.getElementById('message-count');
//             if (data.messages > 0) {
//                 if (messageBadge) {
//                     messageBadge.textContent = data.messages;
//                     messageBadge.style.display = 'inline';
//                 } else {
//                     // Create badge if it doesn't exist
//                     const messagesLink = document.getElementById('messages-link');
//                     const badge = document.createElement('span');
//                     badge.className = 'notification-badge';
//                     badge.id = 'message-count';
//                     badge.textContent = data.messages;
//                     messagesLink.appendChild(badge);
//                 }
//             } else if (messageBadge) {
//                 messageBadge.style.display = 'none';
//             }
//         }
//     } catch (error) {
//         console.error('Failed to update notification counts:', error);
//     }
// }

// /**
//  * Check for new posts
//  */
// async function checkForNewPosts() {
//     try {
//         const response = await fetch('/api/new-posts-count', {
//             headers: {
//                 'X-CSRF-Token': window.csrfToken
//             }
//         });
        
//         if (response.ok) {
//             const data = await response.json();
            
//             if (data.newPosts > 0) {
//                 showNewPostsNotification(data.newPosts);
//             }
//         }
//     } catch (error) {
//         console.error('Failed to check for new posts:', error);
//     }
// }

// /**
//  * Show new posts notification
//  */
// function showNewPostsNotification(count) {
//     const notification = document.createElement('div');
//     notification.className = 'new-posts-notification';
//     notification.innerHTML = `
//         <i class="fas fa-arrow-up"></i>
//         ${count} new post${count > 1 ? 's' : ''} available
//         <button onclick="refreshFeed()">Refresh</button>
//     `;
    
//     document.body.appendChild(notification);
    
//     // Auto-remove after 10 seconds
//     setTimeout(() => {
//         if (notification.parentNode) {
//             notification.parentNode.removeChild(notification);
//         }
//     }, 10000);
// }

// /**
//  * Refresh the feed
//  */
// function refreshFeed() {
//     window.location.reload();
// }

// /**
//  * Load stories data
//  */
// function loadStories() {
//     const storyElements = document.querySelectorAll('.story[data-story-id]');
//     stories = Array.from(storyElements).map(element => ({
//         id: element.dataset.storyId,
//         element: element
//     }));
// }

// /**
//  * Open story viewer
//  */
// function openStoryViewer(storyId) {
//     const storyIndex = stories.findIndex(story => story.id == storyId);
//     if (storyIndex !== -1) {
//         currentStoryIndex = storyIndex;
//         showStoryModal();
//         loadStory(storyId);
//     }
// }

// /**
//  * Show story modal
//  */
// function showStoryModal() {
//     const modal = document.getElementById('storyModal');
//     modal.style.display = 'flex';
//     document.body.style.overflow = 'hidden';
// }

// /**
//  * Close story viewer
//  */
// function closeStoryViewer() {
//     const modal = document.getElementById('storyModal');
//     modal.style.display = 'none';
//     document.body.style.overflow = 'auto';
    
//     if (storyTimer) {
//         clearInterval(storyTimer);
//         storyTimer = null;
//     }
//     isStoryPlaying = false;
// }

// /**
//  * Load story content
//  */
// async function loadStory(storyId) {
//     try {
//         const response = await fetch(`/api/story/${storyId}`);
//         const story = await response.json();
        
//         if (story.success) {
//             displayStory(story.data);
//             startStoryProgress();
//         } else {
//             showToast('error', 'Failed to load story');
//             closeStoryViewer();
//         }
//     } catch (error) {
//         console.error('Story load error:', error);
//         showToast('error', 'Failed to load story');
//         closeStoryViewer();
//     }
// }

// /**
//  * Display story content
//  */
// function displayStory(story) {
//     const storyMedia = document.getElementById('storyMedia');
//     const authorAvatar = document.getElementById('storyAuthorAvatar');
//     const authorName = document.getElementById('storyAuthorName');
//     const storyTime = document.getElementById('storyTime');
    
//     // Clear previous content
//     storyMedia.innerHTML = '';
    
//     // Add media
//     if (story.mediaType === 'video') {
//         const video = document.createElement('video');
//         video.src = story.media;
//         video.autoplay = true;
//         video.muted = true;
//         video.loop = true;
//         storyMedia.appendChild(video);
//     } else {
//         const img = document.createElement('img');
//         img.src = story.media;
//         img.alt = 'Story';
//         storyMedia.appendChild(img);
//     }
    
//     // Update info
//     authorAvatar.src = story.avatar || 'images/profile.jpg';
//     authorName.textContent = story.firstName + ' ' + story.lastName;
//     storyTime.textContent = formatTimeAgo(story.created_at);
// }

// /**
//  * Start story progress animation
//  */
// function startStoryProgress() {
//     const progressBar = document.getElementById('storyProgress');
//     let progress = 0;
//     const duration = 5000; // 5 seconds
//     const interval = 50; // Update every 50ms
//     const increment = (interval / duration) * 100;
    
//     isStoryPlaying = true;
//     progressBar.style.width = '0%';
    
//     storyTimer = setInterval(() => {
//         if (!isStoryPlaying) {
//             clearInterval(storyTimer);
//             return;
//         }
        
//         progress += increment;
//         progressBar.style.width = progress + '%';
        
//         if (progress >= 100) {
//             clearInterval(storyTimer);
//             nextStory();
//         }
//     }, interval);
// }

// /**
//  * Navigate to next story
//  */
// function nextStory() {
//     if (currentStoryIndex < stories.length - 1) {
//         currentStoryIndex++;
//         const nextStoryId = stories[currentStoryIndex].id;
//         loadStory(nextStoryId);
//     } else {
//         closeStoryViewer();
//     }
// }

// /**
//  * Navigate to previous story
//  */
// function previousStory() {
//     if (currentStoryIndex > 0) {
//         currentStoryIndex--;
//         const prevStoryId = stories[currentStoryIndex].id;
//         loadStory(prevStoryId);
//     }
// }

// /**
//  * Open image modal
//  */
// function openImageModal(imageSrc) {
//     const modal = document.getElementById('imageModal');
//     const modalImage = document.getElementById('modalImage');
    
//     modalImage.src = imageSrc;
//     modal.style.display = 'flex';
//     document.body.style.overflow = 'hidden';
// }

// /**
//  * Close image modal
//  */
// function closeImageModal() {
//     const modal = document.getElementById('imageModal');
//     modal.style.display = 'none';
//     document.body.style.overflow = 'auto';
// }

// /**
//  * Show toast notification
//  */
// function showToast(type, message) {
//     const container = document.getElementById('toast-container');
//     const toast = document.createElement('div');
//     toast.className = `toast ${type}`;
    
//     const icon = type === 'success' ? 'check-circle' : 
//                  type === 'error' ? 'exclamation-circle' : 'info-circle';
    
//     toast.innerHTML = `
//         <i class="fas fa-${icon}"></i>
//         <span>${message}</span>
//     `;
    
//     container.appendChild(toast);
    
//     // Auto-remove after 4 seconds
//     setTimeout(() => {
//         if (toast.parentNode) {
//             toast.style.animation = 'slideOut 0.3s ease forwards';
//             setTimeout(() => {
//                 if (toast.parentNode) {
//                     toast.parentNode.removeChild(toast);
//                 }
//             }, 300);
//         }
//     }, 4000);
// }

// /**
//  * Format time ago
//  */
// function formatTimeAgo(dateString) {
//     const date = new Date(dateString);
//     const now = new Date();
//     const diffInSeconds = Math.floor((now - date) / 1000);
    
//     if (diffInSeconds < 60) {
//         return 'Just now';
//     } else if (diffInSeconds < 3600) {
//         const minutes = Math.floor(diffInSeconds / 60);
//         return `${minutes}m ago`;
//     } else if (diffInSeconds < 86400) {
//         const hours = Math.floor(diffInSeconds / 3600);
//         return `${hours}h ago`;
//     } else {
//         const days = Math.floor(diffInSeconds / 86400);
//         return `${days}d ago`;
//     }
// }

// // Add CSS for new posts notification and share modal
// const style = document.createElement('style');
// style.textContent = `
//     .new-posts-notification {
//         position: fixed;
//         top: 100px;
//         left: 50%;
//         transform: translateX(-50%);
//         background: var(--primary-color);
//         color: white;
//         padding: 12px 20px;
//         border-radius: 25px;
//         display: flex;
//         align-items: center;
//         gap: 10px;
//         box-shadow: var(--shadow-medium);
//         z-index: 1000;
//         animation: slideDown 0.3s ease;
//     }
    
//     .new-posts-notification button {
//         background: rgba(255, 255, 255, 0.2);
//         color: white;
//         border: none;
//         padding: 4px 12px;
//         border-radius: 12px;
//         cursor: pointer;
//         font-size: 0.9rem;
//         transition: all 0.2s ease;
//     }
    
//     .new-posts-notification button:hover {
//         background: rgba(255, 255, 255, 0.3);
//     }
    
//     @keyframes slideDown {
//         from {
//             transform: translateX(-50%) translateY(-100%);
//             opacity: 0;
//         }
//         to {
//             transform: translateX(-50%) translateY(0);
//             opacity: 1;
//         }
//     }
    
//     @keyframes slideOut {
//         to {
//             transform: translateX(100%);
//             opacity: 0;
//         }
//     }
// `;
// document.head.appendChild(style);