<?php
session_start();
include '../config.php';
$secretKey = "";

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(['success' => false, 'error' => 'Invalid session!']);
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $captcha = $_POST['g-recaptcha-response'] ?? '';

    if (empty($captcha)) {
        echo json_encode(['success' => false, 'error' => 'Please confirm you are not a robot!']);
        exit;
    }

    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$secretKey}&response={$captcha}");
    $res = json_decode($verify);

    if (!$res || !$res->success) {
        echo json_encode(['success' => false, 'error' => 'Captcha unsuccessful.']);
        exit;
    }

    $stmt = $conn->prepare("SELECT user_id, username, full_name, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Wrong username or password!']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Wrong username or password!']);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Error.']);
}
?>