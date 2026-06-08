<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php"); 
    exit();
}

if (isset($_POST['id'])) {
    $news_id = (int)$_POST['id'];

    $stmt = $conn->prepare("DELETE FROM news WHERE id = ?");
    $stmt->bind_param("i", $news_id);
    
    if ($stmt->execute()) {

        echo "success";
    } else {
        echo "Error: " . $conn->error;
    }
    
    $stmt->close();
} else {
    echo "Error: Missing news identifier.";
}
?>