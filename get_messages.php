<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$my_id = $_SESSION['user_id'];

$stmt_role = $conn->prepare("SELECT role FROM users WHERE user_id = ?");
$stmt_role->bind_param("i", $my_id);
$stmt_role->execute();
$role_res = $stmt_role->get_result();
$user_data = $role_res->fetch_assoc();
$my_role = $user_data['role'] ?? 'user';
$stmt_role->close();

$query = "SELECT m.*, u.full_name, u.profile_pic, 
          (SELECT COUNT(*) FROM message_likes WHERE message_id = m.message_id) as likes_count,
          (SELECT COUNT(*) FROM message_likes WHERE message_id = m.message_id AND user_id = ?) as my_like
          FROM messages m 
          JOIN users u ON m.sender_id = u.user_id 
          ORDER BY m.created_at ASC";

$stmt_msg = $conn->prepare($query);
$stmt_msg->bind_param("i", $my_id);
$stmt_msg->execute();
$res = $stmt_msg->get_result();

while ($row = $res->fetch_assoc()) {
    $is_me = ($row['sender_id'] == $my_id) ? 'msg-sent' : 'msg-received';
    $avatar = !empty($row['profile_pic']) ? htmlspecialchars($row['profile_pic']) : 'images/profiles/default.png';
    $liked_class = ($row['my_like'] > 0) ? 'liked' : '';
    $datetime = date('d.m.Y H:i', strtotime($row['created_at']));

    echo '<div class="message-wrapper ' . $is_me . '">';
    
    if ($row['sender_id'] != $my_id) {
        echo '<img src="' . $avatar . '" class="chat-avatar" alt="Avatar">';
    }
    
    echo '<div class="message-bubble">';
    echo '<span class="sender-name">' . htmlspecialchars($row['full_name']) . '</span>';
    
    if($row['message']) {
        echo '<p>' . nl2br(htmlspecialchars($row['message'])) . '</p>';
    }
    
    if(!empty($row['file_path'])) {
        $file_url = htmlspecialchars($row['file_path']);
        $ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));
        if(in_array($ext, ['jpg','jpeg','png','gif'])) {
            echo '<img src="' . $file_url . '" class="message-img" onclick="window.open(this.src)">';
        }
        echo '<a href="' . $file_url . '" download class="btn-download"><i class="fa-solid fa-download"></i> Download</a>';
    }

    echo '<div class="msg-footer">';
    echo '<span class="msg-time">' . $datetime . '</span>';
    
    echo '<div style="display:flex; align-items:center; gap:12px;">';
    
    if ($my_role == 'admin' || $row['sender_id'] == $my_id) {
        echo '<button class="delete-btn" data-id="' . $row['message_id'] . '"><i class="fa-solid fa-trash-can"></i></button>';
    }
    
    echo '<button class="like-btn ' . $liked_class . '" data-id="' . $row['message_id'] . '">
            <i class="fa-solid fa-heart"></i> ' . ($row['likes_count'] > 0 ? htmlspecialchars($row['likes_count']) : '') . '
          </button>';
    echo '</div>';
    echo '</div>';
    
    echo '</div></div>';
}
$stmt_msg->close();
?>