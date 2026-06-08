<?php
session_start();
include '../config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); 
    exit();
}

if (isset($_POST['full_name'], $_POST['username'], $_POST['password'], $_POST['confirm_password'], $_POST['role'])) {
    
    $full_name = $_POST['full_name'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $role = $_POST['role'];

    if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
        die("Error: Password must be at least 8 characters long and contain at least one digit!");
    }

    if ($password !== $confirm) {
        die("Error: The passwords doesn't match!");
    }

    $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
    $stmt_check->bind_param("s", $username);
    $stmt_check->execute();
    $check_res = $stmt_check->get_result();
    
    if ($check_res->num_rows > 0) {
        die("Error: The username is taken!");
    }
    $stmt_check->close();

    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt_insert = $conn->prepare("INSERT INTO users (full_name, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt_insert->bind_param("ssss", $full_name, $username, $hashed_pass, $role);

    if ($stmt_insert->execute()) {
        echo "success";
    } else {
        echo "Database error: " . $conn->error;
    }
    $stmt_insert->close();
} else {
    echo "Error: Please fill all the lines!";
}
?>
