<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    exit("Access Denied");
}

if (isset($_POST['news_title'])) {
    $title = $_POST['news_title'];
    $content = $_POST['news_content'];
    $image_path = "";

    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] == 0) {
        $target_dir = "../uploads/news/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_name = $_FILES["news_image"]["name"];
        $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($ext, $allowed)) {
            $new_file_name = time() . "_" . bin2hex(random_bytes(5)) . "." . $ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($_FILES["news_image"]["tmp_name"], $target_file)) {
                $image_path = "uploads/news/" . $new_file_name;
            }
        }
    }

    $stmt = $conn->prepare("INSERT INTO news (title, content, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $content, $image_path);

    if ($stmt->execute()) {
        echo "success";
    }

    $stmt->close();
}
?>
