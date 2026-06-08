<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$siteKey = ''; 
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkHub - LOGIN</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css?v=1">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body class="auth-page">

    <meta name="description" content="LinkHub is a secure enterprise platform for team collaboration, featuring encrypted video meetings, live messaging, corporate news and an embedded AI assistant.">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="global-info-trigger" onclick="openInfoModal()" title="About LinkHub">
        <i class="fa-solid fa-circle-info"></i>
    </div>

    <div id="globalInfoModal" class="info-modal-overlay" onclick="closeInfoModalOutside(event)">
        <div class="info-modal-content">
            <span class="info-modal-close" onclick="closeInfoModal()">&times;</span>
            <h3><i class="fa-solid fa-cubes"></i> Welcome to LinkHub</h3>
            <p><strong>LinkHub</strong> is a unified enterprise platform engineered to optimize team collaboration through secure video conferencing, real-time live messaging, an integrated industry news stream, and an easily accessible, embedded AI assistant. If your organization is looking to transition beyond scattered mass chat groups and upgrade to a structured, intelligent digital workplace, LinkHub provides the ideal ecosystem. For registration inquiries and corporate access terms, please contact us at <strong>124343@students.ue-varna.bg</strong>.</p>
            
            <ul class="info-features-list">
                <li>
                    <i class="fa-solid fa-video"></i>
                    <div><strong>Meet (Video Conferences):</strong> Secure virtual meeting rooms. Fully supports instant high-definition screen sharing and encrypted session recording.</div>
                </li>
                <li>
                    <i class="fa-solid fa-comments"></i>
                    <div><strong>Internal Messaging:</strong> High-speed text chat infrastructure enabling instantaneous communication, alongside safe photo and document file transfers.</div>
                </li>
                <li>
                    <i class="fa-solid fa-square-poll-vertical"></i>
                    <div><strong>Interactive Polls:</strong> Dynamic corporate decision-making tools featuring analytical voting fields updated in real time.</div>
                </li>
                <li>
                    <i class="fa-solid fa-brain"></i>
                    <div><strong>AI Intelligence Core:</strong> Integrated Llama 3.3 server assistant equipped with session-based memory for guiding tasks and answering comprehensive inquiries.</div>
                </li>
            </ul>
            
            <div class="info-footer-note">
                <i class="fa-solid fa-shield-halved"></i> Access Restriction: Registration requires an official enterprise key. Unauthorised external registrations are automatically rejected by the security layer.
            </div>
        </div>
    </div>

    <div class="auth-box">
        <img src="images/logo.png" alt="LinkHub Logo" class="auth-logo">
        <h2 style="margin-bottom: 25px; font-weight: 900;">LOGIN</h2>
        
        <div id="login-error" class="alert alert-danger" style="display: none;"></div>

        <form id="login-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" placeholder="Enter username..." required>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="********" required>
            </div>
            
            <div class="captcha-container">
                <div class="g-recaptcha" data-sitekey="<?php echo $siteKey; ?>"></div>
            </div>

            <button type="submit" id="login-btn" class="btn-primary">LOGIN</button>
        </form>

        <div class="auth-footer" style="margin-top: 25px;">
            <p>Do not have a profile? <a href="register.php" style="color: var(--hub-purple); font-weight: 800; text-decoration: none;">Register</a></p>
        </div>
    </div>

    <script>
$(document).ready(function() {
    if (!localStorage.getItem('cookie_notified')) {
        Swal.fire({
            title: 'Cookie Policy',
            html: '<strong>LinkHub</strong> uses strictly necessary session cookies to keep your account secure. These cookies expire after 4 hours and are essential for the service to function. No tracking or marketing cookies are used.',
            icon: 'info',
            confirmButtonColor: '#6c5ce7',
            confirmButtonText: 'I Understand',
            background: '#ffffff',
            allowOutsideClick: false
        }).then((result) => {
            if (result.isConfirmed) {
                localStorage.setItem('cookie_notified', 'true');
            }
        });
    }

    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        
        const errorBox = $('#login-error');
        const loginBtn = $('#login-btn');

        errorBox.hide();
        loginBtn.prop('disabled', true).text('Please, wait...');

        $.post('ajax/process_login.php', $(this).serialize(), function(res) {
            if (res.success) {
                window.location.href = 'index.php';
            } else {
                errorBox.text(res.error).fadeIn();
                loginBtn.prop('disabled', false).text('Login');
                if(window.grecaptcha) {
                    grecaptcha.reset();
                }
            }
        }, 'json').fail(function() {
            errorBox.text("System error. Try again later.").fadeIn();
            loginBtn.prop('disabled', false).text('Login');
        });
    });
});

function openInfoModal() {
    document.getElementById('globalInfoModal').style.display = 'flex';
}

function closeInfoModal() {
    document.getElementById('globalInfoModal').style.display = 'none';
}

function closeInfoModalOutside(event) {
    const modal = document.getElementById('globalInfoModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
}
</script>
</body>
</html>