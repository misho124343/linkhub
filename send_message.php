<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit("Security error: Invalid token!");
    }

    $sender_id = $_SESSION['user_id'];
    $message = $_POST['message'] ?? '';
    $file_path = NULL;
    $file_type = NULL;

    if (!empty($_FILES['chat_file']['name']) && $_FILES['chat_file']['error'] == 0) {
        $upload_dir = "../uploads/chat/";
        if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = $_FILES['chat_file']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt'];
        
        if (in_array($ext, $allowed_exts)) {
            $new_filename = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;
            
            if (move_uploaded_file($_FILES['chat_file']['tmp_name'], $upload_dir . $new_filename)) {
                $file_path = "uploads/chat/" . $new_filename;
                $file_type = $ext;
            }
        } else {
            exit("Invalid file type!");
        }
    }

    if (!empty(trim($message)) || $file_path) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, message, file_path, file_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $sender_id, $message, $file_path, $file_type);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "Error.";
        }
        $stmt->close();
    }
}
?>