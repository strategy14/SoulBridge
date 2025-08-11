<?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
    
  ?>
    <nav>
      <div class="container">
        <div class="logo">
          <img src="images/SB1.png" class="logo-img" alt="SoulBridge Logo">
          <span class="logo-text">SoulBridge</span>
        </div>
        <form action="search" method="GET" class="search-bar">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            <input 
                type="search" 
                name="search" 
                placeholder="Search for creators" 
                value="<?= htmlspecialchars($search_term) ?>"
            >
        </form>
        <div class="create" onclick="window.location.href='home'">
          <label class="btn btn-primary" for="create-post">Create</label>
          <div class="profile-photo">
            <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
          </div>
        </div>
      </div>
    </nav>
    <main>
        <div class="container">
            <!-- Left sidebar -->
            <div class="left">
            <a class="profile" href="profile">
            <div class="profile-photo">
              <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
            </div>
            <div class="handle">
              <p><?= htmlspecialchars($user['firstName'] . " " . $user['lastName']) ?></p>
            </div>
          </a>
          <!-- ===================== sidebar ========================= -->
          <div class="sidebar">
            <a class="manu-item" href="home">
              <span><i class="fa-solid fa-house"></i></span>
              <h3>Feed</h3>
            </a>
            <a class="manu-item" id="Notifications" href="notification">
                        <span>
                            <i class="fa-solid fa-bell">
                                <?php if($noti_count > 0): ?>
                                    <small class="notification-count"><?= $noti_count ?></small>
                                <?php endif; ?>
                            </i>
                        </span>
                        <h3>Notifications</h3>
                        </a>
                <a class="manu-item" id="messages-notifications" href="message">
                  <span>
                    <i class="fa-solid fa-envelope">
                      <?php if ($unread_count > 0): ?>
                        <small class="notification-count"><?= $unread_count ?></small>
                      <?php endif; ?>
                    </i>
                  </span>
                  <h3>Messages</h3>
                </a>
            <a class="manu-item" href="logout">
                <span><i class="fa-solid fa-sign-out-alt"></i></span>
              <h3>Log out</h3>
            </a>
                          <div class="">
                <div>
                  <div class="">
                  </div>
                  <div class="">
                  </div>
              </div>
          </div>
          <div class="end-sidebar" onclick="window.location.href='home'">
            <label for="create-post" class="btn btn-primary">Create Post</label>
          </div>
        </div>
            </div>