<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit();
}

if (isset($_POST['user_id'])) {
    $id_to_delete = (int)$_POST['user_id'];
    $admin_id = $_SESSION['user_id'];

    if ($id_to_delete === $admin_id) {
        exit("Error: You can't delete your profile!");
    }

    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $id_to_delete);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
}
?>
