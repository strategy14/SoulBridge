<?php

class PagesController {
    public function index() {
        require_once 'view/index.view.php';
    }
    public function login() {
        require_once 'view/login.view.php';
    }
    public function signup() {
        require_once 'view/signup.view.php';
    }
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit();
        }
        $queryBuilder = new queryBuilder();
        $current_user_id = $_SESSION['user_id'];
        $profile_user_id = isset($_GET['id']) ? (int)$_GET['id'] : $current_user_id;

        $login_user = $queryBuilder->getUserData($current_user_id);
        $user = $queryBuilder->getUserData($current_user_id);

        // Friend status logic
        $friend_status = null;
        $action_user_id = null;
        if ($profile_user_id !== $current_user_id) {
            $friends = $queryBuilder->select('friends', '*', '(userId = :u1 AND friendId = :u2) OR (userId = :u2 AND friendId = :u1)', [
                'u1' => $current_user_id,
                'u2' => $profile_user_id
            ]);
            if ($friends) {
                $friend_status = $friends[0]['status'];
                $action_user_id = $friends[0]['actionUserId'];
            }
        }
        $posts = $queryBuilder->getProfilePosts($profile_user_id);
        // Counts
        $friends_count = $queryBuilder->select('friends', 'COUNT(*) AS count', '(userId = :id OR friendId = :id) AND status = "accepted"', ['id' => $profile_user_id])[0]['count'];
        $post_count = $queryBuilder->select('posts', 'COUNT(*) AS post_count', 'userId = :id', ['id' => $profile_user_id])[0]['post_count'];
        foreach ($posts as $post) {
            $like_count = $queryBuilder->getLikesCountForPost($post['post_id']);
        }

        $noti_count = $queryBuilder->getUnreadNotificationsCount($current_user_id);
        $unread_count = $queryBuilder->getUnreadMessagesCount($current_user_id);

        require 'view/profile.view.php';
    }
    public function comment() {
        require_once 'view/comment.view.php';
    }
    public function search() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit();
        }
        $queryBuilder = new queryBuilder();
        $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
        $current_user_id = $_SESSION['user_id'];

        $users = $queryBuilder->searchUsers($search_term, $current_user_id);
        $user = $queryBuilder->getUserData($current_user_id);
        $noti_count = $queryBuilder->getUnreadNotificationsCount($current_user_id);
        $unread_count = $queryBuilder->getUnreadMessagesCount($current_user_id);

        require 'view/search.view.php';
    }
    public function notification() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /');
            exit();
        }
        $queryBuilder = new queryBuilder();
        $user_id = $_SESSION['user_id'];

        $queryBuilder->markNotificationsRead($user_id);

        $user = $queryBuilder->getUserData($user_id);
        $notifications = $queryBuilder->getNotifications($user_id);

        $noti_count = $queryBuilder->getUnreadNotificationsCount($user_id);
        $unread_count = $queryBuilder->getUnreadMessagesCount($user_id);
        $comment_count = $queryBuilder-> getCommentsCountForPost($user_id);
        require_once 'view/notification.view.php';
    }
    public function error() {
        require_once 'view/error.view.php';
    }
    public function logout() {
        session_destroy();
        header('Location: /');
        exit(); 
    }
    
    public function home() {
        $queryBuilder = new queryBuilder();

        if (isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];

            $user = $queryBuilder->getUserData($user_id);
            if (!$user) {

                header("Location: /?message=User data not found. Please log in again.");
                exit();
            }

            $_SESSION['user_firstname'] = $user['firstName'];
            $_SESSION['user_lastname'] = $user['lastName'];

            if (empty($_SESSION['csrf_token'])) {
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }

            $posts = $queryBuilder->getPosts($user_id);

            $friend_requests = $queryBuilder->getFriendRequests($user_id);

            $chats = $queryBuilder->getChatsForUser($user_id);

            $noti_count = $queryBuilder->getUnreadNotificationsCount($user_id);

            $unread_count = $queryBuilder->getUnreadMessagesCount($user_id);

            $unread_chat_counts = $queryBuilder->getUnreadChatCounts($user_id);

            $stories = $queryBuilder->getStories();

            $data = [
                'user' => $user,
                'posts' => $posts,
                'friend_requests' => $friend_requests,
                'chats' => $chats,
                'noti_count' => $noti_count,
                'unread_count' => $unread_count,
                'unread_chat_counts' => $unread_chat_counts,
                'csrf_token' => $_SESSION['csrf_token'],
                'stories' => $stories
            ];
            require_once 'view/home.view.php';
        } else {
            header('Location: /'); 
            exit();
        }
    }
    public function postHandler() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
                $queryBuilder = new queryBuilder();
                $userId = $_SESSION['user_id'];
                $content = $_POST['content'];
                $isPublic = isset($_POST['is_public']) ? 1 : 0;

                // Handle file upload
                $filePath = null;
                if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $fileTmp = $_FILES['fileUpload']['tmp_name'];
                    $fileName = time() . '_' . basename($_FILES['fileUpload']['name']);
                    $filePath = $uploadDir . $fileName;
                    move_uploaded_file($fileTmp, $filePath);
                }

                $queryBuilder->createPost($userId, $content, $filePath, $isPublic);
                header('Location: /home');
                exit();
            } else {
                header('Location: /?message=Invalid CSRF token.');
                exit();
            }
        } else {
            header('Location: /');
            exit();
        }
    }
    public function storyUpload() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            
            if (isset($_FILES['story_media']) && $_FILES['story_media']['error'] === UPLOAD_ERR_OK) {
                $fileType = $_FILES['story_media']['type'];
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm'];
                $maxSize = 20 * 1024 * 1024; // 20MB for stories
                
                if (!in_array($fileType, $allowedTypes) || $_FILES['story_media']['size'] > $maxSize) {
                    header('Location: /home?error=invalid_story_file');
                    exit();
                }
                
                $uploadDir = 'uploads/stories/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                $fileTmp = $_FILES['story_media']['tmp_name'];
                $fileExtension = pathinfo($_FILES['story_media']['name'], PATHINFO_EXTENSION);
                $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
                $filePath = $uploadDir . $fileName;
                
                if (move_uploaded_file($fileTmp, $filePath)) {
                    $queryBuilder = new queryBuilder();
                    $mediaType = strpos($fileType, 'video') !== false ? 'video' : 'image';
                    $queryBuilder->addStory($userId, $filePath, $mediaType);
                }
            }
            header('Location: /home');
            exit();
        }
        header('Location: /home');
        exit();
    }

    public function commentHandler() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
            $queryBuilder = new queryBuilder();
            $userId = $_SESSION['user_id'];
            $postId = (int)$_POST['post_id'];
            $comment = trim($_POST['comment']);
            
            if (isset($_POST['csrf_token']) && $_POST['csrf_token'] === $_SESSION['csrf_token']) {
                if (!empty($comment)) {
                    $result = $queryBuilder->addComment($userId, $postId, $comment);
                    
                    if ($result) {
                        // Add notification for comment
                        $postOwnerSql = "SELECT userId FROM posts WHERE id = :postId";
                        $stmt = $queryBuilder->pdo->prepare($postOwnerSql);
                        $stmt->execute(['postId' => $postId]);
                        $postOwner = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        if ($postOwner && $postOwner['userId'] != $userId) {
                            $queryBuilder->addNotification($userId, $postOwner['userId'], "commented on your post.", $postId);
                        }
                    }
                }
                
                // Return JSON for AJAX requests
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Comment added successfully']);
                    exit;
                }
                
                header('Location: /home');
                exit();
            } else {
                header('Location: /?message=Invalid CSRF token.');
                exit();
            }
        }
        header('Location: /home');
        exit();
    }

    public function likePost() {
        header('Content-Type: application/json');
        
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Not logged in']);
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        if (!isset($input['post_id'], $input['action'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid input']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $postId = (int)$input['post_id'];
        $action = $input['action'];

        $queryBuilder = new queryBuilder();

        try {
            if ($action === 'like') {
                $result = $queryBuilder->likePost($userId, $postId);
            } elseif ($action === 'unlike') {
                $result = $queryBuilder->unlikePost($userId, $postId);
            } else {
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
                exit;
            }
            
            $like_count = $queryBuilder->getLikesCountForPost($postId);
            $liked = $queryBuilder->hasUserLikedPost($userId, $postId);

            echo json_encode([
                'success' => true,
                'like_count' => $like_count,
                'liked' => $liked
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error']);
        }
        exit;
    }

    public function message() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /');
        exit();
    }

    $queryBuilder = new queryBuilder();
    $current_user_id = $_SESSION['user_id'];
    $noti_count = $queryBuilder->getUnreadNotificationsCount($current_user_id);
    $unread_count = $queryBuilder->getUnreadMessagesCount($current_user_id);
    $last_message = $queryBuilder->getLastMessageForUser($current_user_id);

    $users_raw = $queryBuilder->getAllUsersWithAvatarExcept($current_user_id);
    $users = [];
    foreach ($users_raw as $user) {
        $last_message = $queryBuilder->getLastMessageWithUser($current_user_id, $user['id']);
        $unread_count = $queryBuilder->getUnreadCountWithUser($current_user_id, $user['id']);
        $users[] = [
            'id' => $user['id'],
            'firstName' => $user['firstName'],
            'lastName' => $user['lastName'],
            'avatar' => $user['avatar'],
            'last_message' => $last_message['last_message'] ?? '',
            'last_message_time' => $last_message['last_message_time'] ?? '',
            'unread_count' => $unread_count
        ];
    }
    // Sort users by last_message_time DESC (most recent first)
    usort($users, function($a, $b) {
        return strtotime($b['last_message_time']) <=> strtotime($a['last_message_time']);
    });

    $user = $queryBuilder->getUserData($current_user_id);

    // Start chat if requested
    if (isset($_GET['start_chat'])) {
        $other_user_id = (int)$_GET['user_id'];
        $chat_id = $queryBuilder->findExistingChat($current_user_id, $other_user_id);
        if (!$chat_id) {
            $chat_id = $queryBuilder->createNewChat($current_user_id, $other_user_id);
        }
        header("Location: /message?chat_id=$chat_id");
        exit();
    }

    // Get messages for chat if chat_id is set (use GET, not POST)
    $messages = [];
    if (isset($_GET['chat_id'])) {
        $chat_id = (int)$_GET['chat_id'];
        // Verify user is in chat
        $chats = $queryBuilder->getChatsForUser($current_user_id);
        $has_access = false;
        foreach ($chats as $chat) {
            if ($chat['chat_id'] == $chat_id) {
                $has_access = true;
                break;
            }
        }
        if (!$has_access) {
            die("Unauthorized access to this chat");
        }
        $messages = $queryBuilder->getMessagesForChat($chat_id);
        $chat_partner = $queryBuilder->getChatUser($chat_id, $current_user_id);
        $queryBuilder->markMessagesAsRead($chat_id, $current_user_id);
    }

    require_once 'view/message.view.php';
}
    public function sendMessage() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
            header('Location: /');
            exit();
        }
        
        $chat_id = (int)($_POST['chat_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $user_id = (int)$_SESSION['user_id'];
        
        if (empty($message) || $chat_id <= 0) {
            header("Location: /message?chat_id=$chat_id&error=empty_message");
            exit();
        }
        
        $queryBuilder = new queryBuilder();
        
        // Verify user has access to this chat
        $chats = $queryBuilder->getChatsForUser($user_id);
        $has_access = false;
        
        foreach ($chats as $chat) {
            if ($chat['chat_id'] == $chat_id) {
                $has_access = true;
                break;
            }
        }
        
        if (!$has_access) {
            header('Location: /message?error=access_denied');
            exit();
        }
        
        // Send the message
        $success = $queryBuilder->sendMessage($chat_id, $user_id, $message);
        
        if ($success) {
            header("Location: /message?chat_id=$chat_id");
        } else {
            header("Location: /message?chat_id=$chat_id&error=send_failed");
        }
        exit();
    }

    public function friendRequest() {
        header('Content-Type: application/json');

        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $action = $data['action'] ?? '';
            $user_id = intval($data['user_id'] ?? 0);
            $allowed_actions = ['send', 'accept', 'decline', 'cancel', 'unfriend'];

            if (!isset($_SERVER['HTTP_X_CSRF_TOKEN']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_SERVER['HTTP_X_CSRF_TOKEN'])) {
                throw new Exception('Invalid CSRF token.');
            }

            if (!in_array($action, $allowed_actions) || $user_id <= 0 || $user_id === $_SESSION['user_id']) {
                throw new Exception('Invalid request parameters.');
            }

            $current_user_id = $_SESSION['user_id'];
            $queryBuilder = new queryBuilder();

            switch ($action) {
                case 'send':
                    $result = $queryBuilder->sendFriendRequest($current_user_id, $user_id);
                    break;
                case 'accept':
                    $result = $queryBuilder->acceptFriendRequest($user_id, $current_user_id);
                    break;
                case 'decline':
                    $result = $queryBuilder->declineFriendRequest($user_id, $current_user_id);
                    break;
                case 'cancel':
                    $result = $queryBuilder->cancelFriendRequest($current_user_id, $user_id);
                    break;
                case 'unfriend':
                    $result = $queryBuilder->unfriend($current_user_id, $user_id);
                    break;
                default:
                    throw new Exception('Invalid action.');
            }

            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => $result['message']]);
            } else {
                throw new Exception($result['message']);
            }
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
?>