<?php
session_start();
include '../config.php';

if ($_SESSION['role'] == 'admin' && isset($_POST['user_id'], $_POST['role'])) {
    $target_id = (int)$_POST['user_id'];
    $new_role = $_POST['role'];
    $my_id = $_SESSION['user_id'];

    if ($target_id == $my_id) {
        die("You cannot change your permission!");
    }

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_role, $target_id);
    
    if ($stmt->execute()) {
        echo "success";
    }
    
    $stmt->close();
}
?>
