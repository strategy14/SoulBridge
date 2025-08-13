<?php
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
?>
<nav class="main-nav">
    <div class="nav-container">
        <div class="nav-brand">
            <img src="images/SB1.png" class="brand-logo" alt="SoulBridge Logo">
            <span class="brand-text">SoulBridge</span>
        </div>
        
        <form action="search" method="GET" class="nav-search">
            <div class="search-input-wrapper">
                <i class="fas fa-search search-icon"></i>
                <input 
                    type="search" 
                    name="search" 
                    placeholder="Search for people..." 
                    value="<?= htmlspecialchars($search_term) ?>"
                    class="search-input"
                >
            </div>
        </form>
        
        <div class="nav-actions">
            <button class="nav-btn create-btn" onclick="window.location.href='home'">
                <i class="fas fa-plus"></i>
                <span class="btn-text">Create</span>
            </button>
            <div class="nav-profile" onclick="window.location.href='profile'">
                <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile" class="nav-avatar">
            </div>
        </div>
        
        <!-- Mobile menu toggle -->
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </div>
</nav>

<main class="main-content">
    <div class="content-container">
        <!-- Left sidebar -->
        <aside class="sidebar-left">
            <a class="user-profile-link" href="profile">
                <div class="user-profile-card">
                    <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile" class="profile-avatar">
                    <div class="profile-info">
                        <h4><?= htmlspecialchars($user['firstName'] . " " . $user['lastName']) ?></h4>
                    </div>
                </div>
            </a>
            
            <!-- Sidebar Navigation -->
            <nav class="sidebar-nav">
                <a class="nav-item" href="home">
                    <i class="fas fa-home"></i>
                    <span>Feed</span>
                </a>
                <a class="nav-item" href="notification">
                    <i class="fas fa-bell">
                        <?php if($noti_count > 0): ?>
                            <span class="notification-badge"><?= $noti_count ?></span>
                        <?php endif; ?>
                    </i>
                    <span>Notifications</span>
                </a>
                <a class="nav-item" href="message">
                    <i class="fas fa-envelope">
                        <?php if ($unread_count > 0): ?>
                            <span class="notification-badge"><?= $unread_count ?></span>
                        <?php endif; ?>
                    </i>
                    <span>Messages</span>
                </a>
                <a class="nav-item" href="logout">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Log out</span>
                </a>
            </nav>
            
            <button class="create-post-btn" onclick="window.location.href='home'">
                <i class="fas fa-plus"></i>
                Create Post
            </button>
        </aside>