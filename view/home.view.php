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
    <link rel="stylesheet" href="/assets/css/navigation.css" />
    <link rel="stylesheet" href="/assets/css/style.css" />
    <link rel="stylesheet" href="/assets/css/stories.css" />
    <link rel="stylesheet" href="/assets/css/responsive.css" />
    <script src="/assets/js/home.js" defer></script>
    <script src="/assets/js/stories.js" defer></script>
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
            // Group stories by userId
            $userStories = [];
            foreach ($stories as $story) {
                $userStories[$story['userId']]['user'] = [
                    'firstName' => $story['firstName'],
                    'avatar' => $story['avatar'],
                    'userId' => $story['userId'],
                ];
                $userStories[$story['userId']]['stories'][] = [
                    'id' => $story['id'],
                    'media' => $story['media'],
                    'mediaType' => (isset($story['media']) && preg_match('/\.(mp4|webm|ogg)$/i', $story['media'])) ? 'video' : 'image'
                ];
            }

    foreach($userStories as $userId => $userData): ?>
    <div class="story"
         data-user-id="<?= $userId ?>"
         data-stories='<?= htmlspecialchars(json_encode($userData['stories']), ENT_QUOTES, 'UTF-8') ?>'
         data-story-username="<?= htmlspecialchars($userData['user']['firstName']) ?>"
         data-story-avatar="<?= htmlspecialchars($userData['user']['avatar'] ?? 'images/profile.jpg') ?>">
        <div class="profile-photo" style="position:relative;">
            <img src="<?= htmlspecialchars($userData['user']['avatar'] ?? 'images/profile.jpg') ?>" alt="Story">
            <span class="story-count-badge" style="position:absolute;bottom:0;right:0;background:#6c63ff;color:#fff;font-size:0.8em;padding:2px 7px;border-radius:12px;"><?= count($userData['stories']) ?></span>
        </div>
        <p class="name"><?= htmlspecialchars($userData['user']['firstName']) ?></p>
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
                                <button class="btn btn-primary accept-request" data-user-id="<?= $request['user_id'] ?>">
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
<!-- Story Preview Modal -->
<div id="storyModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.85); z-index:9999; align-items:center; justify-content:center;">
    <div id="storyModalContent" style="position:relative; background:#222; border-radius:12px; max-width:400px; width:90vw; max-height:90vh; margin:auto; display:flex; flex-direction:column; align-items:center; box-shadow:0 4px 24px rgba(0,0,0,0.5);">
        <span id="closeStoryModal" style="position:absolute; top:10px; right:15px; color:#fff; font-size:2rem; cursor:pointer; z-index:2;">&times;</span>
        <div id="storyProgressBarContainer" style="width:90%;margin:1rem auto 0;display:flex;gap:4px;">
            <!-- Progress bars will be injected here -->
        </div>
        <div style="display:flex; align-items:center; margin-top:1.5rem; margin-bottom:1rem; width:90%;">
            <img id="storyModalAvatar" src="" alt="Avatar" style="width:40px; height:40px; border-radius:50%; object-fit:cover; margin-right:10px;">
            <span id="storyModalUsername" style="color:#fff; font-weight:bold;"></span>
        </div>
        <div id="storyMediaContainer" style="width:100%; display:flex; justify-content:center; align-items:center;">
            <img id="storyModalImg" src="" alt="Story" style="max-width:100%; max-height:60vh; border-radius:10px; display:none;">
            <video id="storyModalVideo" controls style="max-width:100%; max-height:60vh; border-radius:10px; display:none;"></video>
        </div>
        <div style="display:flex; justify-content:space-between; width:100%; margin:1rem 0;">
            <button id="prevStoryBtn" style="background:none; border:none; color:#fff; font-size:2rem; cursor:pointer; padding:0 1rem;">&#8592;</button>
            <button id="nextStoryBtn" style="background:none; border:none; color:#fff; font-size:2rem; cursor:pointer; padding:0 1rem;">&#8594;</button>
        </div>
    </div>
</div>
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

// Story preview logic
const stories = Array.from(document.querySelectorAll('.stories .story[data-story-id]'));
let currentStoryIndex = -1;

function showStoryModal(index) {
    if (index < 0 || index >= stories.length) return;
    currentStoryIndex = index;
    const story = stories[index];
    const media = story.getAttribute('data-story-media');
    const type = story.getAttribute('data-story-type');
    const username = story.getAttribute('data-story-username');
    const avatar = story.getAttribute('data-story-avatar');
    document.getElementById('storyModal').style.display = 'flex';
    document.getElementById('storyModalUsername').textContent = username;
    document.getElementById('storyModalAvatar').src = avatar;
    const img = document.getElementById('storyModalImg');
    const video = document.getElementById('storyModalVideo');
    img.style.display = 'none';
    video.style.display = 'none';
    if (type === 'video') {
        video.src = media;
        video.style.display = 'block';
        img.src = '';
    } else {
        img.src = media;
        img.style.display = 'block';
        video.src = '';
    }
}

stories.forEach((story, idx) => {
    story.addEventListener('click', function(e) {
        // Prevent add-story click from opening modal
        if (story.classList.contains('add-story')) return;
        showStoryModal(idx);
    });
});

const storyDivs = Array.from(document.querySelectorAll('.stories .story[data-user-id]'));
let currentUserStories = [];
let currentStoryIdx = 0;
let storyTimer = null;
let progressBarEls = [];

function renderProgressBars(storyCount, activeIdx) {
    const container = document.getElementById('storyProgressBarContainer');
    container.innerHTML = '';
    progressBarEls = [];
    for (let i = 0; i < storyCount; i++) {
        const bar = document.createElement('div');
        bar.className = 'story-progress-bar';
        bar.style.flex = '1';
        bar.style.height = '4px';
        bar.style.background = i < activeIdx ? '#6c63ff' : '#444';
        bar.style.borderRadius = '2px';
        bar.style.transition = 'background 0.3s';
        bar.style.marginRight = i < storyCount - 1 ? '2px' : '0';
        container.appendChild(bar);
        progressBarEls.push(bar);
    }
}

function animateProgressBar(idx, duration) {
    if (!progressBarEls[idx]) return;
    progressBarEls[idx].style.background = '#6c63ff';
    progressBarEls[idx].style.position = 'relative';
    progressBarEls[idx].innerHTML = `<div class="story-progress-inner" style="background:#fff;height:100%;width:0%;border-radius:2px;position:absolute;left:0;top:0;transition:width linear;"></div>`;
    setTimeout(() => {
        const inner = progressBarEls[idx].querySelector('.story-progress-inner');
        if (inner) {
            inner.style.transition = `width ${duration}ms linear`;
            inner.style.width = '100%';
        }
    }, 10);
}

function showUserStoryModal(stories, userData, idx = 0) {
    if (!stories.length) return;
    clearTimeout(storyTimer);
    currentUserStories = stories;
    currentStoryIdx = idx;
    const story = stories[currentStoryIdx];
    document.getElementById('storyModal').style.display = 'flex';
    document.getElementById('storyModalUsername').textContent = userData.username;
    document.getElementById('storyModalAvatar').src = userData.avatar;
    const img = document.getElementById('storyModalImg');
    const video = document.getElementById('storyModalVideo');
    img.style.display = 'none';
    video.style.display = 'none';

    renderProgressBars(stories.length, idx);
    animateProgressBar(idx, story.mediaType === 'video' ? 0 : 10000);

    if (story.mediaType === 'video') {
        video.src = story.media;
        video.style.display = 'block';
        img.src = '';
        video.currentTime = 0;
        video.onloadedmetadata = function() {
            // Progress bar for video
            animateProgressBar(idx, video.duration * 1000);
            video.play();
            clearTimeout(storyTimer);
            storyTimer = setTimeout(() => {
                if (currentStoryIdx < currentUserStories.length - 1) {
                    showUserStoryModal(currentUserStories, userData, currentStoryIdx + 1);
                } else {
                    document.getElementById('storyModal').style.display = 'none';
                }
            }, video.duration * 1000);
        };
    } else {
        img.src = story.media;
        img.style.display = 'block';
        video.src = '';
        clearTimeout(storyTimer);
        storyTimer = setTimeout(() => {
            if (currentStoryIdx < currentUserStories.length - 1) {
                showUserStoryModal(currentUserStories, userData, currentStoryIdx + 1);
            } else {
                document.getElementById('storyModal').style.display = 'none';
            }
        }, 10000); // 10 seconds for images
    }
}

storyDivs.forEach(storyDiv => {
    storyDiv.addEventListener('click', function(e) {
        if (storyDiv.classList.contains('add-story')) return;
        const stories = JSON.parse(storyDiv.getAttribute('data-stories'));
        const userData = {
            username: storyDiv.getAttribute('data-story-username'),
            avatar: storyDiv.getAttribute('data-story-avatar')
        };
        showUserStoryModal(stories, userData, 0);
    });
});

document.getElementById('closeStoryModal').onclick = function() {
    document.getElementById('storyModal').style.display = 'none';
    document.getElementById('storyModalImg').src = '';
    document.getElementById('storyModalVideo').src = '';
    clearTimeout(storyTimer);
};

document.getElementById('prevStoryBtn').onclick = function() {
    if (currentStoryIdx > 0) showUserStoryModal(currentUserStories, {
        username: document.getElementById('storyModalUsername').textContent,
        avatar: document.getElementById('storyModalAvatar').src
    }, currentStoryIdx - 1);
};
document.getElementById('nextStoryBtn').onclick = function() {
    if (currentStoryIdx < currentUserStories.length - 1) showUserStoryModal(currentUserStories, {
        username: document.getElementById('storyModalUsername').textContent,
        avatar: document.getElementById('storyModalAvatar').src
    }, currentStoryIdx + 1);
};

// Close modal on outside click
document.getElementById('storyModal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.style.display = 'none';
        document.getElementById('storyModalImg').src = '';
        document.getElementById('storyModalVideo').src = '';
        clearTimeout(storyTimer);
    }
});
    </script>
</body>
</html>
