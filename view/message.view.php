<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoulBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="styles.css" />
    <script src="script.js" defer></script>
    <script src="https://kit.fontawesome.com/ce328ec234.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="/assets/css/message.css" />"
</head>
<body>
<?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    require 'view/nav.view.php';
?>
<div class="chat-container <?php echo isset($_GET['chat_id']) ? 'chat-active' : ''; ?>">
    <div class="user-list">
        
        <h3>Users</h3>
        <?php foreach ($users as $user): ?>
            <div class="user-item" onclick="window.location='message?start_chat=1&user_id=<?= $user['id'] ?>'">
                <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" class="avatar" alt="avatar" style="margin-right: 0.5rem;">
                <strong><?= htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?></strong>
                <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                    <?php if (!empty($user['last_message'])): ?>
                        <p style="flex-grow: 1;"><?= htmlspecialchars($user['last_message']) ?></p>
                        <small style="margin-left: 0.5rem;">
                            <i><?= !empty($user['last_message_time']) ? date('H:i', strtotime($user['last_message_time'])) : '' ?> </i>
                            <i class="fa fa-clock"></i>
                        </small>
                        <?php if (!empty($user['unread_count']) && $user['unread_count'] > 0): ?>
                            <span class="notification-count" style="background: var(--color-danger); color: white; font-size: 10px; border-radius: 0.8rem; padding: 0.26rem 0.38rem; margin-left: 0.5rem;">
                                <?= $user['unread_count'] ?>
                            </span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php if (isset($_GET['chat_id'])): ?>
      
      <div class="chat-area" id="message-container">
<div class="message-header" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: var(--color-primary); border-radius: var(--card-border-radius); margin-bottom: 1rem; position: sticky; top: 0; z-index: 1; width: 100%;">
    <div class="profile-photo" style="flex-shrink: 0;">
        <img src="<?= htmlspecialchars($chat_partner['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture" style="width: 40px; height: 40px; border-radius: 50%;">
    </div>
    <h3 style="flex-grow: 1; margin-left: 1rem; color: white;"><?= htmlspecialchars($chat_partner['firstName'] . ' ' . $chat_partner['lastName']) ?></h3>
    <span style="cursor: pointer; color: white;" onclick="window.location='message'"><i class="fa fa-arrow-left" style="font-size: large;"></i></span>
</div>
<div class="messages" style="overflow-y: auto; flex: 1;">
            <?php foreach ($messages as $message): ?>
            <div class="message <?= $message['senderId'] == $current_user_id ? 'self' : 'other' ?>">
              <div style="display: flex; align-items: center;">
              <img src="<?= $message['avatar'] ?? 'images/profile.jpg' ?>" class="avatar" alt="avatar" style="margin-right: 0.5rem;">
              <strong><?= $message['senderId'] == $current_user_id ? 'You' : htmlspecialchars($message['firstName']) ?></strong>
              </div>
              <p><?= htmlspecialchars($message['content']) ?>
                <small><i><?= date('H:i', strtotime($message['created_at'])) ?> </i><i class="fa fa-clock"></i></small></p>
            </div>
            <?php endforeach; ?>
          </div>
          <div class="message-input">
          <form id="message-form" method="POST" action="/sendMessage">
            <?= $_GET['chat_id'] ?>
            <input type="hidden" name="chat_id" value="<?= $_GET['chat_id'] ?>">
             <input type="text" name="message" placeholder="Type your message..." style="width: 80%" autofocus>
             <button type="submit">Send</button>
         </form>
          </div>
        </div>
        <style>
          .messages {
          overflow-y: scroll;
          }
        </style>
        <script>
          const messageContainer = document.querySelector('.messages');
          messageContainer.scrollTop = messageContainer.scrollHeight;

          document.getElementById('message-form').addEventListener('submit', function(e) {e.preventDefault();
          
          const form = document.getElementById('message-form');
          const formData = new FormData(form);


          fetch('/sendMessage', {
              method: 'POST',
              body: formData
          })
          .then(response => {
              if (response.ok) {
                  this.reset();
                  window.location.reload();
              } else {
                  console.error('Failed to send message');
              }
          })
          .catch(error => {
              console.error('Error:', error);
          });

          });
          setInterval(() => {
          fetch('message?chat_id=<?= $_GET['chat_id'] ?>')
            .then(response => response.text())
            .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newMessages = doc.querySelector('.messages').innerHTML;
            messageContainer.innerHTML = newMessages;
            messageContainer.scrollTop = messageContainer.scrollHeight;
            });
          }, 5000);
        </script>
        <?php endif; ?>
      </div>
    </div>
  </main>
  </body>
  </html>
