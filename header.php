<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once __DIR__ . '/../config.php';

$is_logged_in = isset($_SESSION['user_id']);
$is_admin = $is_logged_in && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkHub</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleSwitch = document.querySelector('.theme-switch input[type="checkbox"]');
        const currentTheme = localStorage.getItem('theme');

        if (currentTheme) {
            document.body.classList.add(currentTheme);

            if (toggleSwitch && currentTheme === 'dark-theme') {
                toggleSwitch.checked = true;
            }
        }

        if (toggleSwitch) {
            toggleSwitch.addEventListener('change', (e) => {
                if (e.target.checked) {
                    document.body.classList.add('dark-theme');
                    localStorage.setItem('theme', 'dark-theme');
                } else {
                    document.body.classList.remove('dark-theme');
                    localStorage.setItem('theme', 'light-theme');
                }
            });
        }
    });
    </script>
</head>
<body class="<?php echo !$is_logged_in ? 'auth-page-body' : ''; ?>">

<header class="main-header">
    <div class="container header-flex">
        
        <div class="header-logo">
            <a href="index.php">
                <img src="images/profiles/Copy.png" alt="LinkHub Logo">
            </a>
        </div>

        <?php if ($is_logged_in): ?>
        <nav class="main-nav">
            <ul class="nav-list">
                <li class="theme-switch-wrapper">
                    <label class="theme-switch" for="checkbox">
                        <input type="checkbox" id="checkbox" />
                        <div class="slider">
                            <i class="fa-solid fa-sun"></i>
                            <i class="fa-solid fa-moon"></i>
                        </div>
                    </label>
                </li>
                
                <li><a href="index.php"><i class="fa-solid fa-house"></i> News</a></li>

                <?php if ($is_admin): ?>
                    <li><a href="userlist.php"><i class="fa-solid fa-users"></i> Userlist</a></li>
                <?php endif; ?>

                <li><a href="messages.php"><i class="fa-solid fa-comments"></i> Chat</a></li>
                <li><a href="video.php"><i class="fa-solid fa-video"></i> Meet</a></li>
                <li><a href="polls.php"><i class="fa-solid fa-square-poll-vertical"></i> Polls</a></li>
                <li><a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Exit</a></li>
            </ul>
        </nav>
        <?php endif; ?>

    </div>
</header>

<main>