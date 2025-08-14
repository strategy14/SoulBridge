<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications - SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/navigation.css" />
    <link rel="stylesheet" href="/assets/css/notifications.css" />
    <script src="/assets/js/notifications.js" defer></script>
</head>
<body>
    <?php include 'view/nav.view.php'; ?>
    
    <main class="main-container">
        <div class="container">
            <div class="notifications-card">
                <header class="card-header">
                    <div class="header-content">
                        <div class="header-text">
                            <h1>Notifications</h1>
                            <p>Stay updated with your latest activities</p>
                        </div>
                        <div class="header-actions">
                            <button class="action-btn" onclick="markAllAsRead()" title="Mark all as read">
                                <i class="fas fa-check-double"></i>
                            </button>
                            <button class="action-btn" onclick="clearAllNotifications()" title="Clear all">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </header>

                <div class="card-content">
                    <!-- Notification Tabs -->
                    <nav class="notification-tabs">
                        <button class="tab-btn active" data-tab="all" onclick="switchTab('all')">
                            <i class="fas fa-bell"></i>
                            <span>All</span>
                            <span class="tab-count" id="allCount"><?= count($notifications) ?></span>
                        </button>
                        <button class="tab-btn" data-tab="friend-requests" onclick="switchTab('friend-requests')">
                            <i class="fas fa-user-plus"></i>
                            <span>Friend Requests</span>
                            <span class="tab-count" id="friendRequestsCount">
                                <?= count(array_filter($notifications, function($n) { return strpos($n['message'], 'friend request') !== false; })) ?>
                            </span>
                        </button>
                        <button class="tab-btn" data-tab="system" onclick="switchTab('system')">
                            <i class="fas fa-cog"></i>
                            <span>System</span>
                            <span class="tab-count" id="systemCount">
                                <?= count(array_filter($notifications, function($n) { return strpos($n['message'], 'system') !== false; })) ?>
                            </span>
                        </button>
                    </nav>

                    <!-- Notification Content -->
                    <div class="notification-content">
                        <!-- All Notifications Tab -->
                        <div class="tab-content active" id="all-tab">
                            <?php if (empty($notifications)): ?>
                                <div class="empty-state">
                                    <div class="empty-icon">
                                        <i class="fas fa-bell-slash"></i>
                                    </div>
                                    <h3>No notifications yet</h3>
                                    <p>When you get notifications, they'll show up here</p>
                                </div>
                            <?php else: ?>
                                <div class="notifications-list" id="notificationsList">
                                    <?php foreach ($notifications as $notification): ?>
                                        <div class="notification-item <?= $notification['status'] === 'unread' ? 'unread' : '' ?>" 
                                             data-id="<?= $notification['id'] ?>"
                                             data-type="<?= getNotificationType($notification['message']) ?>">
                                            <div class="notification-avatar">
                                                <img src="<?= htmlspecialchars($notification['avatar'] ?? 'images/profile.jpg') ?>" 
                                                     alt="Profile"
                                                     onerror="this.src='images/profile.jpg'">
                                                <?php if ($notification['status'] === 'unread'): ?>
                                                    <div class="unread-indicator"></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="notification-content">
                                                <div class="notification-text">
                                                    <strong><?= htmlspecialchars($notification['firstName'] . ' ' . $notification['lastName']) ?></strong>
                                                    <?= htmlspecialchars($notification['message']) ?>
                                                </div>
                                                <div class="notification-meta">
                                                    <time datetime="<?= $notification['created_at'] ?>">
                                                        <?= formatTimeAgo($notification['created_at']) ?>
                                                    </time>
                                                    <?php if ($notification['status'] === 'unread'): ?>
                                                        <span class="unread-badge">New</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                <?php if (strpos($notification['message'], 'friend request') !== false && $notification['status'] === 'unread'): ?>
                                                    <button class="action-btn accept-btn" 
                                                            onclick="handleFriendRequest('accept', <?= $notification['fromUserId'] ?>, <?= $notification['id'] ?>)"
                                                            title="Accept">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="action-btn decline-btn" 
                                                            onclick="handleFriendRequest('decline', <?= $notification['fromUserId'] ?>, <?= $notification['id'] ?>)"
                                                            title="Decline">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="action-btn view-btn" 
                                                            onclick="viewNotification(<?= $notification['id'] ?>, '<?= $notification['post_id'] ? 'comments?post_id=' . $notification['post_id'] : 'profile?id=' . $notification['fromUserId'] ?>')"
                                                            title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                <?php endif; ?>
                                                <button class="action-btn delete-btn" 
                                                        onclick="deleteNotification(<?= $notification['id'] ?>)"
                                                        title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Friend Requests Tab -->
                        <div class="tab-content" id="friend-requests-tab">
                            <div class="notifications-list">
                                <?php 
                                $friendRequestNotifications = array_filter($notifications, function($n) { 
                                    return strpos($n['message'], 'friend request') !== false; 
                                });
                                ?>
                                <?php if (empty($friendRequestNotifications)): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-user-plus"></i>
                                        </div>
                                        <h3>No friend requests</h3>
                                        <p>Friend requests will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($friendRequestNotifications as $notification): ?>
                                        <div class="notification-item <?= $notification['status'] === 'unread' ? 'unread' : '' ?>" 
                                             data-id="<?= $notification['id'] ?>"
                                             data-type="friend-request">
                                            <div class="notification-avatar">
                                                <img src="<?= htmlspecialchars($notification['avatar'] ?? 'images/profile.jpg') ?>" 
                                                     alt="Profile"
                                                     onerror="this.src='images/profile.jpg'">
                                                <?php if ($notification['status'] === 'unread'): ?>
                                                    <div class="unread-indicator"></div>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="notification-content">
                                                <div class="notification-text">
                                                    <strong><?= htmlspecialchars($notification['firstName'] . ' ' . $notification['lastName']) ?></strong>
                                                    <?= htmlspecialchars($notification['message']) ?>
                                                </div>
                                                <div class="notification-meta">
                                                    <time datetime="<?= $notification['created_at'] ?>">
                                                        <?= formatTimeAgo($notification['created_at']) ?>
                                                    </time>
                                                    <?php if ($notification['status'] === 'unread'): ?>
                                                        <span class="unread-badge">New</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                <?php if ($notification['status'] === 'unread'): ?>
                                                    <button class="action-btn accept-btn" 
                                                            onclick="handleFriendRequest('accept', <?= $notification['fromUserId'] ?>, <?= $notification['id'] ?>)">
                                                        <i class="fas fa-check"></i>
                                                        Accept
                                                    </button>
                                                    <button class="action-btn decline-btn" 
                                                            onclick="handleFriendRequest('decline', <?= $notification['fromUserId'] ?>, <?= $notification['id'] ?>)">
                                                        <i class="fas fa-times"></i>
                                                        Decline
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- System Tab -->
                        <div class="tab-content" id="system-tab">
                            <div class="notifications-list">
                                <?php 
                                $systemNotifications = array_filter($notifications, function($n) { 
                                    return strpos($n['message'], 'system') !== false; 
                                });
                                ?>
                                <?php if (empty($systemNotifications)): ?>
                                    <div class="empty-state">
                                        <div class="empty-icon">
                                            <i class="fas fa-cog"></i>
                                        </div>
                                        <h3>No system notifications</h3>
                                        <p>System updates and announcements will appear here</p>
                                    </div>
                                <?php else: ?>
                                    <?php foreach ($systemNotifications as $notification): ?>
                                        <div class="notification-item <?= $notification['status'] === 'unread' ? 'unread' : '' ?>" 
                                             data-id="<?= $notification['id'] ?>"
                                             data-type="system">
                                            <div class="notification-avatar">
                                                <div class="system-icon">
                                                    <i class="fas fa-cog"></i>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-content">
                                                <div class="notification-text">
                                                    <strong>System</strong>
                                                    <?= htmlspecialchars($notification['message']) ?>
                                                </div>
                                                <div class="notification-meta">
                                                    <time datetime="<?= $notification['created_at'] ?>">
                                                        <?= formatTimeAgo($notification['created_at']) ?>
                                                    </time>
                                                    <?php if ($notification['status'] === 'unread'): ?>
                                                        <span class="unread-badge">New</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="notification-actions">
                                                <button class="action-btn delete-btn" 
                                                        onclick="deleteNotification(<?= $notification['id'] ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <script>
        // Pass PHP data to JavaScript
        window.csrfToken = '<?= $_SESSION['csrf_token'] ?>';
        window.notifications = <?= json_encode($notifications) ?>;
    </script>
</body>
</html>

<?php
/**
 * Helper function to determine notification type
 */
function getNotificationType($message) {
    if (strpos($message, 'friend request') !== false) {
        return 'friend-request';
    } elseif (strpos($message, 'system') !== false) {
        return 'system';
    } else {
        return 'general';
    }
}

/**
 * Helper function to format time ago
 */
function formatTimeAgo($dateString) {
    $date = new DateTime($dateString);
    $now = new DateTime();
    $diff = $now->diff($date);
    
    if ($diff->days > 7) {
        return $date->format('M j, Y');
    } elseif ($diff->days > 0) {
        return $diff->days . 'd ago';
    } elseif ($diff->h > 0) {
        return $diff->h . 'h ago';
    } elseif ($diff->i > 0) {
        return $diff->i . 'm ago';
    } else {
        return 'Just now';
    }
}
?>