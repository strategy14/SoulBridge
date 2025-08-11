<?php

    class queryBuilder {
        private $pdo;

        public function __construct() {
            $this->pdo = dbConnection::connect();
        }

        

        public function login($email, $password) {
            $sql = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($user && password_verify($password, $user['password'])) {
                return $user;
            }
            return false;
        }

        public function select($table, $columns = '*', $where = '', $params = []) {
            $sql = "SELECT $columns FROM $table";
            if ($where) {
                $sql .= " WHERE $where";
            }
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function insert($table, $data) {
            $columns = implode(", ", array_keys($data));
            $placeholders = ":" . implode(", :", array_keys($data));
            $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($data);
        }

        public function update($table, $data, $where, $params = []) {
            $set = "";
            foreach ($data as $key => $value) {
                $set .= "$key = :$key, ";
            }
            $set = rtrim($set, ", ");
            $sql = "UPDATE $table SET $set WHERE $where";
            $stmt = $this->pdo->prepare($sql);
            $allParams = array_merge($data, $params);
            return $stmt->execute($allParams);
        }

        public function delete($table, $where, $params = []) {
            $sql = "DELETE FROM $table WHERE $where";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute($params);
        }

        public function Where($table, $column, $value) {
            $sql = "SELECT * FROM $table WHERE $column = :value";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['value' => $value]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    // Specific queries for SoulBridge application
    public function getUserData($userId) {
        $sql = "SELECT 
                    u.firstName, 
                    u.lastName, 
                    p.avatar, 
                    p.bio,
                    p.location
                FROM users u
                LEFT JOIN profiles p ON u.id = p.id 
                WHERE u.id = :userId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function searchUsers($searchTerm, $currentUserId) {
        $sql = "SELECT 
                    u.id,
                    u.firstName,
                    u.lastName,
                    p.avatar
                FROM users u
                LEFT JOIN profiles p ON u.id = p.id
                WHERE REPLACE(CONCAT(u.firstName, u.lastName), ' ', '') LIKE :searchTerm
                    OR u.firstName LIKE :searchTerm
                    OR u.lastName LIKE :searchTerm
                AND u.id != :currentUserId
                ORDER BY u.firstName ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['searchTerm' => "%$searchTerm%", 'currentUserId' => $currentUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getPosts($userId) {
        $sql = "SELECT 
                    p.userId,
                    p.id AS post_id,
                    p.caption AS content,
                    p.created_at,
                    p.photo AS post_photo,
                    p.isPublic AS post_public,
                    CONCAT(u.firstName, ' ', u.lastName) AS username,
                    pr.avatar AS profile_pic
                FROM posts p
                JOIN users u ON p.userId = u.id
                LEFT JOIN profiles pr ON u.id = pr.id
                LEFT JOIN friends f ON 
                    (f.userId = :userId1 AND f.friendId = p.userId AND f.status = 'accepted')
                    OR 
                    (f.friendId = :userId2 AND f.userId = p.userId AND f.status = 'accepted')
                WHERE 
                    p.isPublic = 1 
                    OR f.userId IS NOT NULL 
                    OR p.userId = :userId3
                ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId1' => $userId, 'userId2' => $userId, 'userId3' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFriendRequests($userId) {
        $sql = "SELECT 
                    u.id AS user_id,
                    u.firstName,
                    u.lastName,
                    p.avatar
                FROM friends fr
                JOIN users u ON fr.userId = u.id
                LEFT JOIN profiles p ON u.id = p.id
                WHERE fr.friendId = :userId AND fr.status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMutualFriendsCount($currentUserId, $targetUserId) {
        $sql = "SELECT COUNT(*) AS mutual_count
                FROM (
                    SELECT CASE WHEN userId = :currentUserId1 THEN friendId ELSE userId END AS friend
                    FROM friends 
                    WHERE (userId = :currentUserId2 OR friendId = :currentUserId3) 
                        AND status = 'accepted'
                    GROUP BY friend
                ) AS current_friends
                JOIN (
                    SELECT CASE WHEN userId = :targetUserId1 THEN friendId ELSE userId END AS friend
                    FROM friends 
                    WHERE (userId = :targetUserId2 OR friendId = :targetUserId3) 
                        AND status = 'accepted'
                    GROUP BY friend
                ) AS target_friends
                ON current_friends.friend = target_friends.friend";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'currentUserId1' => $currentUserId,
            'currentUserId2' => $currentUserId,
            'currentUserId3' => $currentUserId,
            'targetUserId1' => $targetUserId,
            'targetUserId2' => $targetUserId,
            'targetUserId3' => $targetUserId
        ]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['mutual_count'] : 0;
    }

    public function getUnreadNotificationsCount($userId) {
        $sql = "SELECT COUNT(*) AS noti_count FROM notifications WHERE toUserId = :userId AND status = 'unread'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['noti_count'] : 0;
    }

    public function getUnreadMessagesCount($userId) {
        $sql = "SELECT COUNT(*) AS unread_count FROM messages m
                JOIN chat_participants cp ON m.chatId = cp.chatId
                WHERE cp.userId = :userId1 AND m.senderId != :userId2 AND m.is_read = 0"; 
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId1' => $userId, 'userId2' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['unread_count'] : 0;
    }

    public function getLikesCountForPost($postId) {
        $sql = "SELECT COUNT(*) AS like_count FROM likes WHERE postId = :postId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['postId' => $postId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['like_count'] : 0;
    }

    public function hasUserLikedPost($userId, $postId) {
        $sql = "SELECT 1 FROM likes WHERE userId = :userId AND postId = :postId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId, 'postId' => $postId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    public function getCommentsCountForPost($postId) {
        $sql = "SELECT COUNT(*) AS comment_count FROM comments WHERE postId = :postId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['postId' => $postId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['comment_count'] : 0;
    }

    public function getChatsForUser($userId) {
        $sql = "
            SELECT c.id AS chat_id, 
                   u.id AS user_id,
                   u.firstName,
                   u.lastName,
                   p.avatar,
                   m.content AS last_message,
                   MAX(m.created_at) AS last_message_time,
                   SUM(CASE WHEN mr.read_at IS NULL AND m.senderId != :userId1 THEN 1 ELSE 0 END) AS unread
            FROM chat_participants cp
            JOIN chats c ON cp.chatId = c.id
            JOIN chat_participants cp2 ON c.id = cp2.chatId AND cp2.userId != :userId2
            JOIN users u ON cp2.userId = u.id
            LEFT JOIN profiles p ON u.id = p.id
            LEFT JOIN messages m ON c.id = m.chatId
            LEFT JOIN message_reads mr ON m.id = mr.messageId AND mr.userId = :userId3
            WHERE cp.userId = :userId4
            GROUP BY c.id
            ORDER BY last_message_time DESC
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'userId1' => $userId,
            'userId2' => $userId,
            'userId3' => $userId,
            'userId4' => $userId
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUnreadChatCounts($userId) {
        $sql = "
            SELECT m.chatId, COUNT(*) AS unread 
            FROM messages m
            LEFT JOIN message_reads mr ON m.id = mr.messageId AND mr.userId = :userId1
            WHERE m.chatId IN (
                SELECT chatId FROM chat_participants WHERE userId = :userId2
            ) 
            AND m.senderId != :userId3 
            AND mr.read_at IS NULL
            GROUP BY m.chatId
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'userId1' => $userId,
            'userId2' => $userId,
            'userId3' => $userId
        ]);
        $unread_counts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $unread_counts[$row['chatId']] = $row['unread'];
        }
        return $unread_counts;
    }

    // Friend Request Actions
    public function sendFriendRequest($fromUserId, $toUserId) {
        // Check if already friends or pending
        $sql = "SELECT * FROM friends WHERE (userId = :from AND friendId = :to) OR (userId = :to AND friendId = :from)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            return ['success' => false, 'message' => 'Friend request already exists.'];
        }
        // Insert friend request
        $sql = "INSERT INTO friends (userId, friendId, actionUserId, status) VALUES (:from, :to, :from, 'pending')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        // Add notification
        $this->addNotification($fromUserId, $toUserId, "sent you a friend request.");
        return ['success' => true, 'message' => 'Friend request sent.'];
    }

    public function acceptFriendRequest($fromUserId, $toUserId) {
        $sql = "UPDATE friends SET status = 'accepted', actionUserId = :to WHERE userId = :from AND friendId = :to AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'No pending request to accept.'];
        }
        $this->addNotification($toUserId, $fromUserId, "accepted your friend request.");
        return ['success' => true, 'message' => 'Friend request accepted.'];
    }

    public function declineFriendRequest($fromUserId, $toUserId) {
        $sql = "DELETE FROM friends WHERE userId = :from AND friendId = :to AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        return ['success' => true, 'message' => 'Friend request declined.'];
    }

    public function cancelFriendRequest($fromUserId, $toUserId) {
        $sql = "DELETE FROM friends WHERE userId = :from AND friendId = :to AND status = 'pending'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        // Remove notification
        $sql = "DELETE FROM notifications WHERE fromUserId = :from AND toUserId = :to AND message = 'sent you a friend request.' AND status = 'unread'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['from' => $fromUserId, 'to' => $toUserId]);
        return ['success' => true, 'message' => 'Friend request canceled.'];
    }

    public function unfriend($userId1, $userId2) {
        $sql = "DELETE FROM friends WHERE ((userId = :u1 AND friendId = :u2) OR (userId = :u2 AND friendId = :u1)) AND status = 'accepted'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['u1' => $userId1, 'u2' => $userId2]);
        if ($stmt->rowCount() === 0) {
            return ['success' => false, 'message' => 'Friendship not found.'];
        }
        $this->addNotification($userId1, $userId2, "unfriended you");
        return ['success' => true, 'message' => 'Friend removed.'];
    }

    // Post related actions
    public function createPost($userId, $caption, $photo, $isPublic) {
        $sql = "INSERT INTO posts (userId, caption, photo, isPublic) VALUES (:userId, :caption, :photo, :isPublic)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'userId' => $userId,
            'caption' => $caption,
            'photo' => $photo,
            'isPublic' => $isPublic
        ]);
    }

    // Like/Unlike actions
    public function addLike($userId, $postId) {
        $sql = "INSERT INTO likes (userId, postId) VALUES (:userId, :postId)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['userId' => $userId, 'postId' => $postId]);
    }

    public function removeLike($userId, $postId) {
        $sql = "DELETE FROM likes WHERE userId = :userId AND postId = :postId";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['userId' => $userId, 'postId' => $postId]);
    }

    // Comment actions
    public function addComment($userId, $postId, $content) {
        $sql = "INSERT INTO comments (userId, postId, content) VALUES (:userId, :postId, :content)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['userId' => $userId, 'postId' => $postId, 'content' => $content]);
    }

    // Notification actions
    public function addNotification($fromUserId, $toUserId, $message, $postId = null, $status = 'unread') {
        $sql = "INSERT INTO notifications (fromUserId, toUserId, postId, message, status) VALUES (:fromUserId, :toUserId, :postId, :message, :status)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            'fromUserId' => $fromUserId,
            'toUserId' => $toUserId,
            'postId' => $postId,
            'message' => $message,
            'status' => $status
        ]);
    }

    public function addStory($userId, $media) {
        $sql = "INSERT INTO stories (userId, media) VALUES (:userId, :media)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute(['userId' => $userId, 'media' => $media]);
    }

    public function getStories() {
        $sql = "SELECT s.*, u.firstName, u.lastName, p.avatar FROM stories s
                JOIN users u ON s.userId = u.id
                LEFT JOIN profiles p ON u.id = p.id
                WHERE s.created_at >= NOW() - INTERVAL 24 HOUR
                ORDER BY s.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNotifications($userId) {
        $sql = "SELECT n.*, u.firstName, u.lastName, p.avatar
                FROM notifications n
                JOIN users u ON n.fromUserId = u.id
                LEFT JOIN profiles p ON u.id = p.id
                WHERE n.toUserId = :userId
                ORDER BY n.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markNotificationsRead($userId) {
        $sql = "UPDATE notifications SET status = 'read' WHERE toUserId = :userId AND status = 'unread'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId]);
    }
    public function likePost($userId, $postId) {
        $sql = "INSERT IGNORE INTO likes (user_id, post_id) VALUES (:userId, :postId)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId, 'postId' => $postId]);
    }

    public function unlikePost($userId, $postId) {
        $sql = "DELETE FROM likes WHERE user_id = :userId AND post_id = :postId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['userId' => $userId, 'postId' => $postId]);
    }

    public function getProfilePosts($profile_user_id) {
        $sql = "SELECT 
                    p.id AS post_id,
                    p.caption AS content,
                    p.created_at,
                    p.photo AS post_photo,
                    p.isPublic AS post_public,
                    CONCAT(u.firstName, ' ', u.lastName) AS username,
                    pr.avatar AS profile_pic
                FROM posts p
                JOIN users u ON p.userId = u.id
                LEFT JOIN profiles pr ON u.id = pr.id
                WHERE p.userId = :profile_user_id
                ORDER BY p.created_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['profile_user_id' => $profile_user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

public function findExistingChat($user1, $user2) {
    $sql = "SELECT cp.chatId AS chat_id 
            FROM chat_participants cp
            INNER JOIN chat_participants cp2 ON cp.chatId = cp2.chatId
            WHERE cp.userId = :user1 AND cp2.userId = :user2
            LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['user1' => $user1, 'user2' => $user2]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['chat_id'] : false;
}

public function createNewChat($user1, $user2) {
    $this->pdo->beginTransaction();
    try {
        // Create chat
        $stmt = $this->pdo->prepare("INSERT INTO chats (created_at) VALUES (NOW())");
        $stmt->execute();
        $chat_id = $this->pdo->lastInsertId();
        
        // Add participants
        $stmt = $this->pdo->prepare("INSERT INTO chat_participants (chatId, userId) VALUES (:chatId, :user1), (:chatId, :user2)");
        $stmt->execute([
            'chatId' => $chat_id,
            'user1' => $user1,
            'user2' => $user2
        ]);
        
        $this->pdo->commit();
        return $chat_id;
    } catch (PDOException $e) {
        $this->pdo->rollBack();
        error_log("Chat creation error: " . $e->getMessage());
        return false;
    }
}

public function markMessagesAsRead($chatId, $userId) {
    $sql = "UPDATE messages SET is_read = 1 
            WHERE chatId = :chatId 
            AND senderId != :userId 
            AND is_read = 0";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute(['chatId' => $chatId, 'userId' => $userId]);
}

public function getLastMessageWithUser($currentUserId, $otherUserId) {
    $sql = "SELECT m.content AS last_message, m.created_at AS last_message_time
            FROM messages m
            JOIN chat_participants cp ON m.chatId = cp.chatId
            WHERE cp.chatId IN (
                SELECT chatId FROM chat_participants WHERE userId = :currentUserId
            )
            AND cp.chatId IN (
                SELECT chatId FROM chat_participants WHERE userId = :otherUserId
            )
            ORDER BY m.created_at DESC
            LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['currentUserId' => $currentUserId, 'otherUserId' => $otherUserId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function getUnreadCountWithUser($currentUserId, $otherUserId) {
    $sql = "SELECT COUNT(*) AS unread_count
            FROM messages m
            JOIN chat_participants cp ON m.chatId = cp.chatId
            WHERE cp.chatId IN (
                SELECT chatId FROM chat_participants WHERE userId = :currentUserId
            )
            AND cp.chatId IN (
                SELECT chatId FROM chat_participants WHERE userId = :otherUserId
            )
            AND m.senderId = :otherUserId
            AND m.is_read = 0";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([
        'currentUserId' => $currentUserId,
        'otherUserId' => $otherUserId
    ]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['unread_count'] : 0;
}

public function getAllUsersWithAvatarExcept($excludeId) {
    $sql = "SELECT u.id, u.firstName, u.lastName, p.avatar
            FROM users u
            LEFT JOIN profiles p ON u.id = p.id
            WHERE u.id != :id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['id' => $excludeId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}














//  public function findExistingChat($user1, $user2) {
//         $sql = "SELECT cp.chatId 
//                 FROM chat_participants cp
//                 INNER JOIN chat_participants cp2 ON cp.chatId = cp2.chatId
//                 WHERE cp.userId = ? AND cp2.userId = ?";
//         $stmt = $this->pdo->prepare($sql);
//         $stmt->execute([$user1, $user2]);
//         $result = $stmt->fetch(PDO::FETCH_ASSOC);
//         return $result ? $result['chatId'] : false;
//     }

//     public function createNewChat($user1, $user2) {
//         $this->pdo->beginTransaction();
//         try {
//             // Create chat
//             $stmt = $this->pdo->prepare("INSERT INTO chats (chat_type) VALUES ('direct')");
//             $stmt->execute();
//             $chat_id = $this->pdo->lastInsertId();
            
//             // Add participants
//             $stmt = $this->pdo->prepare("INSERT INTO chat_participants (chat_id, user_id) VALUES (?, ?), (?, ?)");
//             $stmt->execute([$chat_id, $user1, $chat_id, $user2]);
            
//             $this->pdo->commit();
//             return $chat_id;
//         } catch (PDOException $e) {
//             $this->pdo->rollBack();
//             error_log("Chat creation error: " . $e->getMessage());
//             return false;
//         }
//     }

//     public function verifyChatAccess($chatId, $userId) {
//         $sql = "SELECT 1 FROM chat_participants WHERE chatId = ? AND userId = ?";
//         $stmt = $this->pdo->prepare($sql);
//         $stmt->execute([$chatId, $userId]);
//         return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
//     }

//     public function getMessagesForChat($chatId) {
//         $sql = "SELECT m.*, u.firstName, u.lastName, p.avatar
//                 FROM messages m
//                 JOIN users u ON m.senderId = u.id
//                 LEFT JOIN profiles p ON u.id = p.id
//                 WHERE m.chatId = ?
//                 ORDER BY m.sentAt ASC";
//         $stmt = $this->pdo->prepare($sql);
//         $stmt->execute([$chatId]);
//         return $stmt->fetchAll(PDO::FETCH_ASSOC);
//     }

//     public function getChatUser($chatId, $currentUserId) {
//         $sql = "SELECT u.id, u.firstName, u.lastName, p.avatar
//                 FROM chat_participants cp
//                 JOIN users u ON cp.userId = u.id
//                 LEFT JOIN profiles p ON u.id = p.id
//                 WHERE cp.chatId = ? AND cp.userId != ?";
//         $stmt = $this->pdo->prepare($sql);
//         $stmt->execute([$chatId, $currentUserId]);
//         return $stmt->fetch(PDO::FETCH_ASSOC);
//     }

//     public function markMessagesAsRead($chatId, $userId) {
//         $sql = "UPDATE messages SET is_read = 1 
//                 WHERE chatId = ? AND senderId != ? AND is_read = 0";
//         $stmt = $this->pdo->prepare($sql);
//         return $stmt->execute([$chatId, $userId]);
//     }

//     public function sendMessage($chatId, $senderId, $messageText) {
//         $sql = "INSERT INTO messages (chatId, senderId, content, created_at) 
//                 VALUES (?, ?, ?, NOW())";
//         $stmt = $this->pdo->prepare($sql);
//         return $stmt->execute([$chatId, $senderId, $messageText]);
//     }

public function getMessagesForChat($chat_id){
    $sql = "SELECT m.*, u.firstName, u.lastName, p.avatar
            FROM messages m
            JOIN users u ON m.senderId = u.id
            LEFT JOIN profiles p ON u.id = p.id
            WHERE m.chatId = :chat_id
            ORDER BY m.created_at ASC";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['chat_id' => $chat_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function getChatUser($chat_id, $current_user_id) {
    $sql = "SELECT u.id, u.firstName, u.lastName, p.avatar
            FROM chat_participants cp
            JOIN users u ON cp.userId = u.id
            LEFT JOIN profiles p ON u.id = p.id
            WHERE cp.chatId = :chat_id AND cp.userId != :current_user_id";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['chat_id' => $chat_id, 'current_user_id' => $current_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

public function sendMessage($chat_id, $sender_id, $content) {
    $sql = "INSERT INTO messages (chatId, senderId, content, created_at) 
            VALUES (:chat_id, :sender_id, :content, NOW())";
    $stmt = $this->pdo->prepare($sql);
    return $stmt->execute([
        'chat_id' => $chat_id,
        'sender_id' => $sender_id,
        'content' => $content
    ]);
}
public function getLastMessageForUser($current_user_id){
    $sql = "SELECT m.content AS last_message, m.created_at AS last_message_time
            FROM messages m
            JOIN chat_participants cp ON m.chatId = cp.chatId
            WHERE m.senderId = :current_user_id
            ORDER BY m.created_at DESC
            LIMIT 1";
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute(['current_user_id' => $current_user_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}




















}
?>
