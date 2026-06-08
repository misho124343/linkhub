<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Access denied: You don't have the required permission");
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    exit("Security error: Invalid token!");
}

if (isset($_POST['poll_id'])) {
    $id = intval($_POST['poll_id']);
    
    $stmt1 = $conn->prepare("DELETE FROM poll_votes WHERE poll_id = ?");
    $stmt1->bind_param("i", $id);
    $stmt1->execute();
    $stmt1->close();

    $stmt2 = $conn->prepare("DELETE FROM poll_options WHERE poll_id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $stmt2->close();

    $stmt3 = $conn->prepare("DELETE FROM polls WHERE poll_id = ?");
    $stmt3->bind_param("i", $id);
    
    if ($stmt3->execute()) {
        echo "success";
    } else {
        echo "Error deleting the poll.";
    }
    $stmt3->close();
}
?>
