<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comments - SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navigation.css" />
    <link rel="stylesheet" href="/assets/css/comments.css" />
    <script src="/assets/js/comments.js" defer></script>
</head>
<body>
    <?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    require_once 'view/nav.view.php';
    ?>
    
    <div class="comments-main">
        <div class="comments-container">
            <!-- Back Button -->
            <div class="comments-header">
                <button class="back-btn" onclick="history.back()">
                    <i class="fas fa-arrow-left"></i>
                    <span>Back</span>
                </button>
                <h1>Post Comments</h1>
            </div>
            
            <!-- Post Content -->
            <?php if (isset($post)): ?>
                <div class="original-post">
                    <div class="post-header">
                        <div class="post-author">
                            <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'images/profile.jpg') ?>" 
                                 alt="<?= htmlspecialchars($post['username']) ?>" 
                                 class="author-avatar">
                            <div class="author-info">
                                <h3><?= htmlspecialchars($post['username']) ?></h3>
                                <time><?= date('M j, Y g:i a', strtotime($post['created_at'])) ?></time>
                            </div>
                        </div>
                        <div class="post-privacy">
                            <i class="fas <?= $post['post_public'] ? 'fa-globe' : 'fa-user-friends' ?>"></i>
                            <span><?= $post['post_public'] ? 'Public' : 'Friends' ?></span>
                        </div>
                    </div>
                    
                    <?php if (!empty($post['content'])): ?>
                        <div class="post-content">
                            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($post['post_photo'])): ?>
                        <div class="post-media">
                            <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $post['post_photo'])): ?>
                                <video controls class="post-video">
                                    <source src="<?= htmlspecialchars($post['post_photo']) ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php else: ?>
                                <img src="<?= htmlspecialchars($post['post_photo']) ?>" 
                                     alt="Post Image" 
                                     class="post-image">
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="post-stats">
                        <div class="stat-item">
                            <i class="fas fa-heart"></i>
                            <span><?= $like_count ?> likes</span>
                        </div>
                        <div class="stat-item">
                            <i class="fas fa-comment"></i>
                            <span><?= $comment_count ?> comments</span>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Comments Section -->
            <div class="comments-section">
                <div class="comments-header-section">
                    <h2>Comments (<?= count($comments ?? []) ?>)</h2>
                    <div class="sort-options">
                        <select id="sortComments">
                            <option value="newest">Newest first</option>
                            <option value="oldest">Oldest first</option>
                        </select>
                    </div>
                </div>
                
                <!-- Add Comment Form -->
                <div class="add-comment-section">
                    <form class="comment-form" id="mainCommentForm">
                        <input type="hidden" name="post_id" value="<?= $post_id ?? '' ?>">
                        <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="comment-input-wrapper">
                            <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" 
                                 alt="Your avatar" 
                                 class="comment-avatar">
                            <div class="input-container">
                                <textarea name="comment" 
                                         placeholder="Write a comment..." 
                                         class="comment-textarea"
                                         rows="1"
                                         required></textarea>
                                <div class="comment-actions">
                                    <button type="button" class="emoji-btn">
                                        <i class="fas fa-smile"></i>
                                    </button>
                                    <button type="submit" class="submit-btn">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Post</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                
                <!-- Comments List -->
                <div class="comments-list" id="commentsList">
                    <?php if (empty($comments)): ?>
                        <div class="empty-comments">
                            <div class="empty-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h3>No comments yet</h3>
                            <p>Be the first to share your thoughts!</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item" data-comment-id="<?= $comment['id'] ?>">
                                <div class="comment-avatar-container">
                                    <img src="<?= htmlspecialchars($comment['avatar'] ?? 'images/profile.jpg') ?>" 
                                         alt="<?= htmlspecialchars($comment['firstName'] . ' ' . $comment['lastName']) ?>" 
                                         class="comment-avatar">
                                </div>
                                
                                <div class="comment-content">
                                    <div class="comment-bubble">
                                        <div class="comment-header">
                                            <h4 class="commenter-name">
                                                <?= htmlspecialchars($comment['firstName'] . ' ' . $comment['lastName']) ?>
                                            </h4>
                                            <time class="comment-time">
                                                <?= date('M j, Y g:i a', strtotime($comment['created_at'])) ?>
                                            </time>
                                        </div>
                                        <p class="comment-text">
                                            <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                        </p>
                                    </div>
                                    
                                    <div class="comment-actions">
                                        <button class="action-btn like-comment-btn" data-comment-id="<?= $comment['id'] ?>">
                                            <i class="far fa-heart"></i>
                                            <span>Like</span>
                                        </button>
                                        <button class="action-btn reply-btn" data-comment-id="<?= $comment['id'] ?>">
                                            <i class="fas fa-reply"></i>
                                            <span>Reply</span>
                                        </button>
                                        <?php if ($comment['userId'] == $_SESSION['user_id']): ?>
                                            <button class="action-btn delete-btn" data-comment-id="<?= $comment['id'] ?>">
                                                <i class="fas fa-trash"></i>
                                                <span>Delete</span>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Toast for notifications -->
    <div id="toast" class="toast"></div>
    
    <script>
        const postId = <?= $post_id ?? 'null' ?>;
        const currentUserId = <?= $_SESSION['user_id'] ?>;
        const csrfToken = "<?= $_SESSION['csrf_token'] ?>";
    </script>
</body>
</html>