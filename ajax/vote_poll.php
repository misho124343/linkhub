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

if (isset($_POST['poll_id'], $_POST['option_id'])) {
    $p_id = intval($_POST['poll_id']);
    $o_id = intval($_POST['option_id']);
    $u_id = $_SESSION['user_id'];

    $stmt_check = $conn->prepare("SELECT vote_id FROM poll_votes WHERE poll_id = ? AND user_id = ?");
    $stmt_check->bind_param("ii", $p_id, $u_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows == 0) {
        $stmt_insert = $conn->prepare("INSERT INTO poll_votes (poll_id, option_id, user_id) VALUES (?, ?, ?)");
        $stmt_insert->bind_param("iii", $p_id, $o_id, $u_id);
        $stmt_insert->execute();
        $stmt_insert->close();
    } else {
        $stmt_update = $conn->prepare("UPDATE poll_votes SET option_id = ? WHERE poll_id = ? AND user_id = ?");
        $stmt_update->bind_param("iii", $o_id, $p_id, $u_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
    
    $stmt_check->close();
    echo "success";
}
?>
