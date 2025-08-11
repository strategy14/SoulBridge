<?php
$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://kit.fontawesome.com/ce328ec234.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/responsive.css" />
    <script src="/assets/js/home.js" defer></script>
    <style>
        .right .request .action button {
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .right .request .action .accept-request {
            background-color: var(--color-primary);
            color: white;
        }

        .right .request .action .accept-request:hover {
            background-color: hsl(252, 75%, 50%);
        }

        .right .request .action .decline-request {
            background-color: var(--color-gray);
            color: white;
        }

        .right .request .action .decline-request:hover {
            background-color: hsl(0, 95%, 55%);
        }
    </style>
</head>
<body>
<?php include 'view/nav.view.php'; ?>
    <!-- Middle section -->
    <div class="middle">
        <!-- Stories section -->
        <div class="stories">
            <!-- Add Story Button -->
            <div class="story add-story" onclick="document.getElementById('storyUpload').click()">
                <div class="profile-photo">
                    <img src="<?= htmlspecialchars($data['user']['avatar'] ?? 'images/profile.jpg') ?>" alt="Your Story">
                    <div class="add-icon">+</div>
                </div>
                <p class="name">Your Story</p>
            </div>
            
            <!-- Display Active Stories -->
            <?php 
            $stories = $queryBuilder->getActiveStories();
            foreach($stories as $story): 
            ?>
            <div class="story" data-story-id="<?= $story['id'] ?>">
                <div class="profile-photo">
                    <img src="<?= htmlspecialchars($story['avatar'] ?? 'images/profile.jpg') ?>" alt="Story">
                </div>
                <p class="name"><?= htmlspecialchars($story['firstName']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Hidden Story Upload Form -->
        <form action="story-upload" method="POST" enctype="multipart/form-data" style="display: none;">
            <input type="file" id="storyUpload" name="story_media" accept="image/*,video/*" onchange="this.form.submit()">
            <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
        </form>
        
        <!-- Create post form -->
        <form action="post-create" method="POST" enctype="multipart/form-data">
            <div class="create-post">
                <div class="first-line">
                    <div class="profile-photo">
                        <img src="<?= htmlspecialchars($data['user']['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
                    </div>
                    <input type="text" placeholder="What's on your mind, <?= htmlspecialchars($data['user']['firstName']) ?>?" id="create-post-input" name="content" required>
                </div>

                <div class="second-line">
                    <div class="checkbox-wrapper-4">
                        <input class="inp-cbx" id="morning" type="checkbox" name="is_public" value="1">
                        <label class="cbx" for="morning">
                            <span>
                                <svg width="12px" height="10px">
                                    <use xlink:href="#check-4"></use>
                                </svg>
                            </span>
                            <span>
                                <i class="fa-solid fa-globe"></i>
                            </span>
                        </label>
                        <svg class="inline-svg">
                            <symbol id="check-4" viewBox="0 0 12 10">
                                <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                            </symbol>
                        </svg>
                    </div>
                    <input type="file" name="fileUpload" id="imagefile" accept="image/*,video/*" style="display: none;" onchange="previewImage(event)">
                    <label for="imagefile" class="btn btn-primary"><i class="fa-regular fa-image"></i></label>
                    <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i></button>
                </div>
                <div class="image-preview" id="imagePreview" style="display: none; position: relative;">
                    <img id="previewImg" src="" alt="Image Preview" style="max-width: 100%; height: auto;">
                    <video id="previewVideo" controls style="max-width: 100%; height: auto; display: none;"></video>
                    <span id="closePreview" style="position: absolute; top: 10px; right: 10px; background: rgba(0, 0, 0, 0.5); color: white; border-radius: 50%; padding: 5px 8px; cursor: pointer; font-size: 16px;">&times;</span>
                </div>
            </div>
        </form>
        <!-- Posts section -->
        <div class="feeds">
            <?php if (empty($data['posts'])): ?>
                <div class="empty-state" style="text-align: center; color: #888; padding: 20px;">
                    <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>No posts to show yet.</p>
                </div>
            <?php else: ?>
                <?php foreach($data['posts'] as $post): ?>
                    <div class="feed" data-post-id="<?= $post['post_id'] ?>">
                        <div class="head">
                            <div class="user">
                                <a href="profile?id=<?= $post['userId'] ?>">
                                    <div class="profile-photo">
                                        <img src="<?= htmlspecialchars($post['profile_pic'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
                                    </div>
                                </a>
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
                            <?php if (preg_match('/\.(mp4|webm|ogg)$/i', $post['post_photo'])): ?>
                                <div class="photo">
                                    <video controls style="max-width:100%">
                                        <source src="<?= htmlspecialchars($post['post_photo']) ?>">
                                        Your browser does not support the video tag.
                                    </video>
                                </div>
                            <?php else: ?>
                                <div class="photo">
                                    <img src="<?= htmlspecialchars($post['post_photo']) ?>" alt="Post Image">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($post['content'])): ?>
                            <div class="caption">
                                <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <div class="action-button">
                            <div class="interaction-buttons">
                                <span class="like-btn" data-post-id="<?= $post['post_id'] ?>">
                                    <a href="#" class="like-link">
                                        <?php
                                        $like_count = $queryBuilder->getLikesCountForPost($post['post_id']);
                                        $liked = $queryBuilder->hasUserLikedPost($_SESSION['user_id'], $post['post_id']);
                                        ?>
                                        <i class="<?= $liked ? 'fa-solid' : 'fa-regular' ?> fa-heart" style="<?= $liked ? 'color: red;' : '' ?>"></i>
                                        <span class="like-count"><i style="font-size: large;"><?= $like_count ?></i></span>
                                    </a>
                                </span>
                                <span class="comment-btn">
                                    <?php
                                    $comment_count = $queryBuilder->getCommentsCountForPost($post['post_id']);
                                    ?>
                                    <i class="fa-regular fa-comment"></i>
                                    <span class="comment-count"><i style="font-size: large;"><?= $comment_count ?></i></span>
                                </span>
                            </div>
                            <div class="bookmark">
                                <span><i class=""></i></span>
                            </div>
                        </div>
                        <div class="comments-section">
                            <div class="comments-list">
                            </div>
                            <form class="comment-form" action="/comment" method="POST">
                                <input type="hidden" name="post_id" value="<?= $post['post_id']; ?>">
                                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                                <input type="hidden" name="csrf_token" value="<?= $data['csrf_token'] ?>">
                                <div class="input-group">
                                    <input type="text" class="input" id="comment-input-<?= $post['post_id'] ?>" name="comment" placeholder="Write a comment..." autocomplete="off" required>
                                    <input class="button--submit" value="Comment" type="submit">
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="empty-state" style="text-align: center; color: #888; padding: 20px;">
                    <i class="fas fa-newspaper" style="font-size: 48px; margin-bottom: 10px;"></i>
                    <p>No more posts to display.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="container">
        <!-- Right sidebar -->
        <div class="right">
            <div class="friend-requests">
                <div style="margin-top: 1rem; background-color: var(--color-white); padding: 1rem; border-radius: 15px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);"> 
                    <h3 style="margin: 1rem 0; color: var(--color-dark);">Requests (<?= count($data['friend_requests']) ?>)</h3>
                    <?php foreach($data['friend_requests'] as $request): ?>
                        <?php
                        $mutual = $queryBuilder->getMutualFriendsCount($_SESSION['user_id'], $request['user_id']);
                        ?>
                        <div class="request">
                            <div class="info">
                                <div class="profile-photo">
                                    <a href="pf.php?id=<?= $request['user_id'] ?>">
                                        <img src="<?= htmlspecialchars($request['avatar'] ?? 'images/profile.jpg') ?>" 
                                            alt="Profile Picture"
                                            onerror="this.src='images/profile.jpg'">
                                    </a>
                                </div>
                                <div class="sender">
                                    <h4><?= htmlspecialchars($request['firstName'] . ' ' . $request['lastName']) ?></h4>
                                    <small class="text-muted"><?= $mutual ?> mutual friend<?php if($mutual!=1) echo 's'; ?></small>
                                </div>
                            </div>
                            <div class="action">
                                <button class="btn btn- accept-request" data-user-id="<?= $request['user_id'] ?>">
                                    Confirm
                                </button>
                                <button class="btn btn-danger decline-request" data-user-id="<?= $request['user_id'] ?>">
                                    Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <?php if (empty($data['friend_requests'])): ?>
                        <p class="text-muted">No pending requests</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
</body>
</html>
