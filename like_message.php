<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    exit("Security error: Invalid token!");
}

if (isset($_POST['message_id'])) {
    $m_id = intval($_POST['message_id']);
    $u_id = (int)$_SESSION['user_id'];

    $stmt_check = $conn->prepare("SELECT message_id FROM message_likes WHERE message_id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $m_id, $u_id);
    $stmt_check->execute();
    $res = $stmt_check->get_result();

    if ($res->num_rows > 0) {
        $stmt_action = $conn->prepare("DELETE FROM message_likes WHERE message_id = ? AND user_id = ?");
    } else {
        $stmt_action = $conn->prepare("INSERT INTO message_likes (message_id, user_id) VALUES (?, ?)");
    }

    $stmt_action->bind_param("ii", $m_id, $u_id);
    
    if ($stmt_action->execute()) {
        echo "success";
    } else {
        echo "Error.";
    }

    $stmt_check->close();
    $stmt_action->close();
}
?>