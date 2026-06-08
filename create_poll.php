<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['question'])) {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        exit("Security error: Invalid token!");
    }

    $question = $_POST['question'];
    
    $stmt_poll = $conn->prepare("INSERT INTO polls (question) VALUES (?)");
    $stmt_poll->bind_param("s", $question);
    
    if ($stmt_poll->execute()) {
        $p_id = $conn->insert_id;
        $stmt_poll->close();

        if (isset($_POST['options']) && is_array($_POST['options'])) {
            $stmt_opt = $conn->prepare("INSERT INTO poll_options (poll_id, option_text) VALUES (?, ?)");
            
            foreach ($_POST['options'] as $opt_text) {
                $opt_text = trim($opt_text);
                if (!empty($opt_text)) {
                    $stmt_opt->bind_param("is", $p_id, $opt_text);
                    $stmt_opt->execute();
                }
            }
            $stmt_opt->close();
        }
        echo "success";
    } else {
        echo "Error creating poll.";
    }
}
?>