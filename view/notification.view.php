<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/notification.css" />
    <script src="/assets/js/script.js" defer></script>
    <script
      src="https://kit.fontawesome.com/ce328ec234.js"
      crossorigin="anonymous"
    ></script>
</head>
<body>
    <?php include 'view/nav.view.php'; ?>
            <div class="middle">
                <div class="notification-header">
                    <h2>Notifications</h2>
                    <a href="clear_notifications.php" class="btn btn-danger">Clear All</a>
                </div>

                <?php if (empty($notifications)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bell-slash"></i>
                        <p>No notifications yet</p>
                    </div>
                <?php else: ?>
                    <div class="notification-list">
                        <?php foreach ($notifications as $notification): ?>
                            <div class="notification-item <?= $notification['status'] === 'unread' ? 'unread' : '' ?>">
                                <div class="notification-avatar" onclick="window.location.href='pf?id=<?= $notification['fromUserId'] ?>'">
                                    <img src="<?= htmlspecialchars($notification['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile">
                                </div>
                                <div class="notification-content">
                                <?php 
                                     if (!empty($notification['post_id'])) {
                                         $profileLink = "comments?post_id=" . urlencode($notification['post_id']);
                                     } else {
                                         $profileLink = "pf?id=" . urlencode($notification['fromUserId']);
                                     }
                                     ?>
                                     
                                     <a href="<?= htmlspecialchars($profileLink) ?>" style="text-decoration: none; color: aliceblue;">
                                         <p>
                                             <strong><?= htmlspecialchars($notification['firstName'] . ' ' . $notification['lastName']) ?></strong>
                                             <?= htmlspecialchars($notification['message']) ?>
                                         </p>
                                     </a>
                                    <small class="text-muted"><?= date('M j, Y g:i a', strtotime($notification['created_at'])) ?></small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php endif; ?>
            </div>

            <div class="right">
            </div>
        </div>
    </main>
</body>
</html>