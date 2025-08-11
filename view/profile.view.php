<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoulBridge</title>
    <link rel="stylesheet" href="/assets/css/profile.css" />
    <script src="/assets/js/script.js" defer></script>
    <script
      src="https://kit.fontawesome.com/ce328ec234.js"
      crossorigin="anonymous"
    ></script>
    <script>
        const CSRF_TOKEN = "<?= $_SESSION['csrf_token'] ?>";
    </script>
</head>
<body>
    <?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    require_once 'view/nav.view.php';
  ?>
  
            <!-- Middle Section -->
            <div class="middle">
                <!-- Profile Header -->
              <div class="profile-section">
                <div class="profile-header">
                    <img src="<?= htmlspecialchars($user['cover_photo'] ?? 'images/SB.png') ?>" 
                         class="cover-photo" 
                         alt="Cover Photo"
                         onerror="this.src='images/SB.png'">
                    <div class="profile-photo profile-photo-lg">
                        <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" 
                             alt="Profile Picture"
                             onerror="this.src='images/profile.jpg'">
                    </div>
                </div>

                <!-- Profile Info -->
                <div class="profile-info">
                    <h2><?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></h2>

                    <!-- Friend Button -->
                    <div class="profile-actions">
    <?php if ($profile_user_id != $current_user_id): ?>
        <?php if ($friend_status === 'pending'): ?>
            <?php if ($action_user_id == $current_user_id): ?>
                <button class="btn btn-danger cancel-request" 
                        data-user-id="<?= $profile_user_id ?>">
                    Cancel Request
                </button>
            <?php else: ?>
                <div class="btn-group">
                    <button class="btn btn-success accept-request" 
                            data-user-id="<?= $profile_user_id ?>">
                        Accept
                    </button>
                    <button class="btn btn-danger decline-request" 
                            data-user-id="<?= $profile_user_id ?>">
                        Decline
                    </button>
                </div>
            <?php endif; ?>
        <?php elseif ($friend_status === 'accepted'): ?>
            <button class="btn btn-danger unfriend-btn" 
                    data-user-id="<?= $profile_user_id ?>">
                Unfriend
            </button>
        <?php else: ?>
            <button class="btn btn-primary send-request" 
                    data-user-id="<?= $profile_user_id ?>">
                Add Friend
            </button>
        <?php endif; ?>
    <?php endif; ?>
</div>
                    <div class="profile-stats">
                        <a href="friends_list.php?id=<?= $profile_user_id ?>"><div class="stat-item">
                            <div class="stat-number"><?= $friends_count ?></div>
                            <div class="stat-label">Friends</div>
                        </div></a>

                        <div class="stat-item">
                            <div class="stat-number"><?= $post_count ?></div>
                            <div class="stat-label">Posts</div>
                        </div>
                        <div class="stat-item">
                  <div class="stat-number"><?= $like_count; ?></div>
                  <div class="stat-label">Likes</div>
                </div>
                    </div>

                    <div class="profile-bio">
                <p><i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($user['bio']) ?></p>
                <p><i class="fa-solid fa-map-marker-alt"></i> <?php echo htmlspecialchars($user['location']) ?></p>
              </div>
              </div>
              </div>

                <div class="feeds">
                <?php if (empty($posts)): ?>
              <div class="empty-state" style="text-align: center; color: #888; padding: 20px;">
              <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 10px;"></i>
              <p>No posts to show yet.</p>
              </div>
              <?php endif; ?>
              <?php foreach($posts as $post): ?>
              <div class="feed" data-post-id="<?= $post['post_id'] ?>">
                <div class="head">
                  <div class="user">
                    <div class="profile-photo">
                      <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
                    </div>
                    <div class="info">
                      <h3><?= htmlspecialchars($post['username']) ?></h3>
                      <small><?= date('M j, Y g:i a', strtotime($post['created_at'])) ?></small>
                    </div>
                  </div>
                  <span class="edit">
                    <i class="fa-solid <?= $post['post_public'] ? 'fa-globe' : 'fa-user-friends' ?>"></i> 
                    <?= $post['post_public'] ? 'Public' : 'Friends' ?>
                  </span>
                </div>
                <?php if (!empty($post['post_photo'])): ?>
                <div class="photo" onclick="window.location.href='comments.php?post_id=<?= $post['post_id'] ?>'" >
                  <img src="<?= htmlspecialchars($post['post_photo']) ?>" alt="Post Image">
                </div>
                <?php endif; ?>
                <?php if (!empty($post['content'])): ?>
                <div class="caption">
                  <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                </div>
                <?php endif; ?>
                <div class="action-button">
                  <div class="interaction-buttons">
                    <!-- Like Button -->
                    <span class="like-btn">
                    <a href="like_handler.php?post_id=<?= $post['post_id']; ?>" class="btn btn-sm btn-outline-primary like-link">
                      <i class="<?= $liked ? 'fa-solid' : 'fa-regular' ?> fa-heart" style="<?= $liked ? 'color: red;' : '' ?>">

                      </i><span class="like-count"><i style="font-size: large;"><?= $like_count = $queryBuilder->getLikesCountForPost($post['post_id']); ?></i></span>
                      </a>
                    </span>

                    <a href="comments.php?post_id=<?php echo $post['post_id']; ?>"><span class="comment-btn">
                        <?php
                         $comment_count = $queryBuilder->getCommentsCountForPost($post['post_id']);
                         ?>
                        <i class="fa-regular fa-comment">
                        <span class="comment-count"><?= $comment_count; ?></span>
                        </i>
                      </span>
                      </a>
                  </div>
                  <div class="bookmark">
                    <span><i class=""></i></span>
                  </div>
                </div>

                <div class="comments-section">
                  <div class="comments-list">
                  </div>
                  <form class="comment-form" action="comment_handler.php" method="POST">
                      <input type="hidden" name="post_id" value="<?= $post['post_id']; ?>">
                      <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                      <div class="input-group">
                      <input type="text" class="input" id="Email" name="comment" placeholder="Write a comment..." autocomplete="off" required>
                     <input class="button--submit" value="Comment" type="submit">
                      </div>
                    </form>
                </div>
              </div>
              <?php endforeach; ?>
          </div>
            <div class="right">
            </div>
        </div>
    </main>
    <script>
const handleRequest = async (action, userId) => {
    try {
        const response = await fetch('friendRequest', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                action: action,
                user_id: userId
            })
        });

        const result = await response.json();

        if (result.success) {
            alert(result.message || 'Action successful');
            location.reload();
        } else {
            alert(result.error || 'Action failed');
        }
    } catch (error) {
        console.error('Error:', error);
        alert('An error occurred. Please check the console for details.');
    }
};

document.querySelectorAll('.send-request').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        handleRequest('send', userId);
    });
});

document.querySelectorAll('.accept-request').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        handleRequest('accept', userId);
    });
});

document.querySelectorAll('.decline-request').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        handleRequest('decline', userId);
    });
});
document.querySelectorAll('.cancel-request').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        handleRequest('cancel', userId);
    });
});

document.querySelectorAll('.unfriend-btn').forEach(button => {
    button.addEventListener('click', () => {
        const userId = button.getAttribute('data-user-id');
        handleRequest('unfriend', userId);
    });
});
    </script>
</body>
</html>