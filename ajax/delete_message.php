<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    exit("You are not logged.");
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    exit("Security error: Invalid token!");
}

if (isset($_POST['message_id'])) {
    $m_id = intval($_POST['message_id']);
    $u_id = $_SESSION['user_id'];
    $role = $_SESSION['role'];
    $stmt_check = $conn->prepare("SELECT sender_id FROM messages WHERE message_id = ?");
    $stmt_check->bind_param("i", $m_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    $row = $result->fetch_assoc();

    if ($row) {
        if ($role == 'admin' || $row['sender_id'] == $u_id) {
            $stmt_likes = $conn->prepare("DELETE FROM message_likes WHERE message_id = ?");
            $stmt_likes->bind_param("i", $m_id);
            $stmt_likes->execute();
            $stmt_likes->close();
            $stmt_del = $conn->prepare("DELETE FROM messages WHERE message_id = ?");
            $stmt_del->bind_param("i", $m_id);
            
            if ($stmt_del->execute()) {
                echo "success";
            } else {
                echo "Error deleting message.";
            }
            $stmt_del->close();
        } else {
            echo "You do not have the required permission.";
        }
    }
    $stmt_check->close();
}
?>
