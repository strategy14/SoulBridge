<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SoulBridge</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <script src="script.js" defer></script>
    <script
      src="https://kit.fontawesome.com/ce328ec234.js"
      crossorigin="anonymous"
    ></script>
  </head>
<body>
<?php
  $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
  require_once "view/nav.view.php";
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
      <div class="create">
          <label class="btn btn-primary" for="create-post">Create</label>
          <div class="profile-photo">
              <a href="profile.php"><img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture"></a>
          </div>
      </div>
  </div>
</nav>
    
    <main>
        <div class="container">
            <h2>Search Results for "<?= htmlspecialchars($search_term) ?>"</h2>

            <?php if (count($users) > 0): ?>
                <div class="search-results">
                <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <div class="profile-photo">
                            <img src="<?= htmlspecialchars($user['avatar'] ?? 'images/profile.jpg') ?>" alt="Profile Picture">
                        </div>
                        <div class="user-info">
                            <h3><?= htmlspecialchars(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? '')) ?>
                            <?php if (($user['id'] ?? null) == $_SESSION['user_id']): ?>
                                <span style="font-size: 16px; float:right; color: gray;">you</span>
                            <?php endif; ?></h3>
                            <a href="profile?id=<?= $user['id'] ?? '' ?>" class="btn btn-primary">
                                View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>

                </div>
            <?php else: ?>
                <p>No users found matching your search.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>