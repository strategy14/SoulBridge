<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?> - SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navigation.css" />
    <link rel="stylesheet" href="/assets/css/profile.css" />
    <script src="/assets/js/profile.js" defer></script>
    <script>
        const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?>";
        const currentUserId = <?= $current_user_id ?>;
        const profileUserId = <?= $profile_user_id ?>;
    </script>
</head>
<body>
    <?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    $login_user = $queryBuilder->getUserData($current_user_id);
    $user = $login_user; 
    require_once 'view/nav.view.php';
    $user = $queryBuilder->getUserData($profile_user_id);
    ?>

    <div class="profile-main">
        <!-- Profile Header -->
        <div class="profile-header-section">
            <div class="cover-photo-container">
                <img src="<?= htmlspecialchars($user['coverPhoto'] ?? 'images/SB.png') ?>" 
                     class="cover-photo" 
                     alt="Cover Photo"
                     onclick="openImageModal('<?= htmlspecialchars($user['coverPhoto'] ?? 'images/SB.png') ?>')"
                     onerror="this.src='images/SB.png'">
                <div class="cover-overlay"></div>
            </div>
            
            <div class="profile-info-container">
                <div class="profile-avatar-section">
                    <div class="profile-avatar-wrapper">
                        <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" 
                             alt="Profile Picture"
                             class="profile-avatar-large"
                             onclick="openImageModal('<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>')"
                             onerror="this.src='images/profile.jpg'">
                        <?php if ($profile_user_id == $current_user_id): ?>
                            <button class="edit-avatar-btn" onclick="window.location.href='/edit-profile'">
                                <i class="fas fa-camera"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="profile-details">
                    <h1 class="profile-name"><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h1>
                    <p class="profile-username">@<?= htmlspecialchars(strtolower(str_replace(' ', '', $user['firstName'] . $user['lastName']))) ?></p>
                    
                    <!-- Zodiac Sign -->
                    <?php 
                    $zodiacSign = getZodiacSign($user['birthdate'] ?? '');
                    if ($zodiacSign): 
                    ?>
                        <div class="zodiac-info">
                            <i class="fas fa-star"></i>
                            <span><?= $zodiacSign ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Profile Stats -->
                    <div class="profile-stats">
                        <div class="stat-item">
                            <span class="stat-number"><?= $friends_count ?></span>
                            <span class="stat-label">Friends</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $post_count ?></span>
                            <span class="stat-label">Posts</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-number"><?= $like_count ?? 0 ?></span>
                            <span class="stat-label">Likes</span>
                        </div>
                    </div>
                    
                    <!-- Profile Viewers (only for own profile) -->
                    <?php if ($profile_user_id == $current_user_id && !empty($profile_viewers)): ?>
                        <div class="profile-viewers">
                            <h4><i class="fas fa-eye"></i> Recent Profile Views</h4>
                            <div class="viewers-list">
                                <?php foreach (array_slice($profile_viewers, 0, 5) as $viewer): ?>
                                    <div class="viewer-item">
                                        <img src="<?= htmlspecialchars($viewer['avatar'] ?? 'images/profile.jpg') ?>" 
                                             alt="<?= htmlspecialchars($viewer['firstName']) ?>"
                                             onerror="this.src='images/profile.jpg'">
                                        <div class="viewer-info">
                                            <span class="viewer-name"><?= htmlspecialchars($viewer['firstName'] . ' ' . $viewer['lastName']) ?></span>
                                            <span class="viewer-time"><?= formatTimeAgo($viewer['viewed_at']) ?></span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <?php if (count($profile_viewers) > 5): ?>
                                    <div class="view-all-viewers">
                                        <span>+<?= count($profile_viewers) - 5 ?> more</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Profile Bio -->
                    <?php if (!empty($user['bio']) || !empty($user['location'])): ?>
                        <div class="profile-bio">
                            <?php if (!empty($user['bio'])): ?>
                                <p><i class="fas fa-quote-left"></i> <?= htmlspecialchars($user['bio']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($user['location'])): ?>
                                <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($user['location']) ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="profile-actions">
                        <?php if ($profile_user_id != $current_user_id): ?>
                            <?php if ($friend_status === 'pending'): ?>
                                <?php if ($action_user_id == $current_user_id): ?>
                                    <button class="action-btn cancel-request-btn" data-user-id="<?= $profile_user_id ?>">
                                        <i class="fas fa-times"></i>
                                        Cancel Request
                                    </button>
                                <?php else: ?>
                                    <div class="button-group">
                                        <button class="action-btn accept-request-btn" data-user-id="<?= $profile_user_id ?>">
                                            <i class="fas fa-check"></i>
                                            Accept
                                        </button>
                                        <button class="action-btn decline-request-btn" data-user-id="<?= $profile_user_id ?>">
                                            <i class="fas fa-times"></i>
                                            Decline
                                        </button>
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($friend_status === 'accepted'): ?>
                                <button class="action-btn message-btn" onclick="startChat(<?= $profile_user_id ?>)">
                                    <i class="fas fa-envelope"></i>
                                    Message
                                </button>
                                <button class="action-btn unfriend-btn" data-user-id="<?= $profile_user_id ?>">
                                    <i class="fas fa-user-minus"></i>
                                    Unfriend
                                </button>
                            <?php else: ?>
                                <button class="action-btn add-friend-btn" data-user-id="<?= $profile_user_id ?>">
                                    <i class="fas fa-user-plus"></i>
                                    Add Friend
                                </button>
                                <button class="action-btn message-btn" onclick="startChat(<?= $profile_user_id ?>)">
                                    <i class="fas fa-envelope"></i>
                                    Message
                                </button>
                            <?php endif; ?>
                        <?php else: ?>
                            <button class="action-btn edit-profile-btn">
                                <i class="fas fa-edit"></i>
                                Edit Profile
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Profile Content -->
        <div class="profile-content">
            <div class="content-wrapper">
                <!-- Posts Section -->
                <div class="posts-section">
                    <div class="section-header">
                        <h2>Posts</h2>
                        <div class="post-filters">
                            <button class="filter-btn active" data-filter="all">All</button>
                            <button class="filter-btn" data-filter="photos">Photos</button>
                            <button class="filter-btn" data-filter="videos">Videos</button>
                        </div>
                    </div>
                    
                    <div class="posts-grid">
                        <?php if (empty($posts)): ?>
                            <div class="empty-posts">
                                <div class="empty-icon">
                                    <i class="fas fa-images"></i>
                                </div>
                                <h3>No posts yet</h3>
                                <p>
                                    <?php if ($profile_user_id == $current_user_id): ?>
                                        Share your first post to get started!
                                    <?php else: ?>
                                        <?= htmlspecialchars($user['firstName']) ?> hasn't shared any posts yet.
                                    <?php endif; ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <?php foreach($posts as $post): ?>
                                <article class="post-card" data-post-id="<?= $post['post_id'] ?>">
                                    <div class="post-header">
                                        <div class="post-author">
                                            <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'images/profile.jpg') ?>" 
                                                 alt="<?= htmlspecialchars($post['username']) ?>" 
                                                 class="author-avatar">
                                            <div class="author-info">
                                                <h4><?= htmlspecialchars($post['username']) ?></h4>
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
                                                     class="post-image"
                                                     onclick="openImageModal('<?= htmlspecialchars($post['post_photo']) ?>')">
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="post-actions">
                                        <div class="action-buttons">
                                            <button class="action-btn like-btn" data-post-id="<?= $post['post_id'] ?>">
                                                <?php
                                                $like_count = $queryBuilder->getLikesCountForPost($post['post_id']);
                                                $liked = $queryBuilder->hasUserLikedPost($current_user_id, $post['post_id']);
                                                ?>
                                                <i class="<?= $liked ? 'fas' : 'far' ?> fa-heart <?= $liked ? 'liked' : '' ?>"></i>
                                                <span class="like-count"><?= $like_count ?></span>
                                            </button>
                                            
                                            <button class="action-btn comment-btn" onclick="openComments(<?= $post['post_id'] ?>)">
                                                <?php $comment_count = $queryBuilder->getCommentsCountForPost($post['post_id']); ?>
                                                <i class="far fa-comment"></i>
                                                <span class="comment-count"><?= $comment_count ?></span>
                                            </button>
                                            
                                            <button class="action-btn share-btn">
                                                <i class="far fa-share"></i>
                                            <span>Like</span>
                                        </div>
                                        
                                        <button class="action-btn share-btn" data-post-id="<?= $post['post_id'] ?>" onclick="openShareModal(<?= $post['post_id'] ?>)">
                                            <span>Comment</span>
                                            <span>Share</span>
                                        </button>
                                    </div>
                                    
                                    <!-- Post Stats -->
                                    <div class="post-stats">
                                        <div class="stats-row">
                                            <span class="stat-item">
                                                <i class="fas fa-heart text-red"></i>
                                                <span class="like-count"><?= $like_count ?></span>
                                            </span>
                                            <span class="stat-item">
                                                <i class="fas fa-comment text-blue"></i>
                                                <span class="comment-count"><?= $comment_count ?></span>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Quick Comment Form -->
                                    <div class="quick-comment">
                                        <form class="comment-form" data-post-id="<?= $post['post_id'] ?>">
                                            <input type="hidden" name="post_id" value="<?= $post['post_id'] ?>">
                                            <input type="hidden" name="user_id" value="<?= $current_user_id ?>">
                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                            <div class="comment-input-wrapper">
                                                <img src="<?= htmlspecialchars($login_user['avatar'] ?? 'images/profile.jpg') ?>" 
                                                     alt="Your avatar" 
                                                     class="comment-avatar">
                                                <input type="text" 
                                                       name="comment" 
                                                       placeholder="Write a comment..." 
                                                       class="comment-input"
                                                       required>
                                                <button type="submit" class="comment-submit-btn">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Image Modal -->
    <div class="image-modal" id="imageModal" style="display: none;" onclick="closeImageModal()">
        <div class="modal-backdrop" onclick="closeImageModal()"></div>
        <div class="modal-content">
            <button class="modal-close" onclick="closeImageModal()">
                <i class="fas fa-times"></i>
            </button>
            <img src="" alt="Full size image" id="modalImage">
        </div>
    </div>
    
    <!-- Share Modal -->
    <div id="shareModal" class="share-modal" style="display: none;">
        <div class="share-modal-backdrop" onclick="closeShareModal()"></div>
        <div class="share-content">
            <div class="share-header">
                <h3>Share Post</h3>
                <button class="close-share-btn" onclick="closeShareModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="share-options">
                <button class="share-option" onclick="copyPostLink()">
                    <i class="fas fa-link"></i>
                    <span>Copy Link</span>
                </button>
                <button class="share-option" onclick="shareToFriends()">
                    <i class="fas fa-user-friends"></i>
                    <span>Share to Friends</span>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Toast for notifications -->
    <div id="toast" class="toast"></div>
    
    <script>
        // Pass PHP data to JavaScript
        window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        window.currentUserId = <?= $current_user_id ?>;
        window.profileUserId = <?= $profile_user_id ?>;
        
        let sharePostId = null;
    </script>
</body>
</html>

<?php
/**
 * Helper function to get zodiac sign
 */
function getZodiacSign($birthdate) {
    if (empty($birthdate)) return '';
    
    $date = new DateTime($birthdate);
    $month = (int)$date->format('n');
    $day = (int)$date->format('j');
    
    $zodiacSigns = [
        ['name' => 'Capricorn', 'start' => [12, 22], 'end' => [1, 19]],
        ['name' => 'Aquarius', 'start' => [1, 20], 'end' => [2, 18]],
        ['name' => 'Pisces', 'start' => [2, 19], 'end' => [3, 20]],
        ['name' => 'Aries', 'start' => [3, 21], 'end' => [4, 19]],
        ['name' => 'Taurus', 'start' => [4, 20], 'end' => [5, 20]],
        ['name' => 'Gemini', 'start' => [5, 21], 'end' => [6, 20]],
        ['name' => 'Cancer', 'start' => [6, 21], 'end' => [7, 22]],
        ['name' => 'Leo', 'start' => [7, 23], 'end' => [8, 22]],
        ['name' => 'Virgo', 'start' => [8, 23], 'end' => [9, 22]],
        ['name' => 'Libra', 'start' => [9, 23], 'end' => [10, 22]],
        ['name' => 'Scorpio', 'start' => [10, 23], 'end' => [11, 21]],
        ['name' => 'Sagittarius', 'start' => [11, 22], 'end' => [12, 21]]
    ];
    
    foreach ($zodiacSigns as $sign) {
        if ($sign['name'] === 'Capricorn') {
            if (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) {
                return $sign['name'];
            }
        } else {
            $startMonth = $sign['start'][0];
            $startDay = $sign['start'][1];
            $endMonth = $sign['end'][0];
            $endDay = $sign['end'][1];
            
            if (($month == $startMonth && $day >= $startDay) || 
                ($month == $endMonth && $day <= $endDay)) {
                return $sign['name'];
            }
        }
    }
    
    return '';
}
?>