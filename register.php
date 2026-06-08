<?php
$timeout = 14400; 
session_start([
    'cookie_lifetime' => $timeout,
    'gc_maxlifetime' => $timeout,
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax'
]);

include 'config.php';
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
$error = "";
$success = "";
$username = "";
$full_name = "";

if (isset($_POST['register_btn'])) {
    $username = $_POST['username'];
    $full_name = $_POST['full_name'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $access_code = $_POST['access_code'];

    $stmt_code = $conn->prepare("SELECT code_value FROM company_codes LIMIT 1");
    $stmt_code->execute();
    $res_code = $stmt_code->get_result();
    $db_code = $res_code->fetch_assoc()['code_value'] ?? '123';
    $stmt_code->close();

    if ($access_code !== $db_code) {
        $error = "Invalid Access Code! Only authorized employees can register.";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long!";
    } elseif (!preg_match("/[0-9]/", $password)) {
        $error = "Password must contain at least one digit!";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $stmt_check = $conn->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt_check->bind_param("s", $username);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows > 0) {
            $error = "Username is taken!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $res_role = $conn->query("SELECT user_id FROM users LIMIT 1");
            $role = ($res_role->num_rows == 0) ? 'admin' : 'user';

            $stmt_insert = $conn->prepare("INSERT INTO users (username, full_name, password, role) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("ssss", $username, $full_name, $hashed_password, $role);
            
            if ($stmt_insert->execute()) {
                $success = "The registration is successful!";
                $username = "";
                $full_name = "";
            } else {
                $error = "Database Error!";
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LinkHub - Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="auth-page">
    <div class="auth-box">
        <img src="images/logo.png" alt="LinkHub Logo" class="auth-logo">
        <h2>Registration</h2>

        <form id="register-form" action="register.php" method="POST">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label>Full name</label>
                <input type="text" name="full_name" value="<?php echo htmlspecialchars($full_name); ?>" required>
            </div>

            <div class="form-group">
                <div class="label-row">
                    <label>Company Code</label>
                    <div class="tooltip-container">
                        <i class="fa-solid fa-circle-question info-icon"></i>
                        <span class="tooltip-box">
                            This is a mandatory access code. Only employees of the company that has a corporate subscription can register. 
                            For inquiries, contact us: 124343@students.ue-varna.bg
                        </span>
                    </div>
                </div>
                <input type="text" name="access_code" placeholder="" required>
            </div>

            <div class="form-group">
                <label>Password (minimum 8 characters and a number)</label>
                <input type="password" id="register_password" name="password" required>
            </div>
            <div class="form-group">
                <label>Confirm password</label>
                <input type="password" id="register_confirm" name="confirm_password" required>
            </div>
            <button type="submit" name="register_btn" class="btn-primary">Register</button>
        </form>
        <div class="auth-footer">
            <p>You have a profile? <a href="login.php">Enter here</a></p>
        </div>
    </div>

<script>
$(document).ready(function() {
    <?php if(!empty($error)): ?>
        Swal.fire({
            icon: 'error',
            title: 'Registration Failed',
            text: '<?php echo htmlspecialchars($error); ?>',
            confirmButtonColor: '#6c5ce7'
        });
    <?php endif; ?>

    <?php if(!empty($success)): ?>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo htmlspecialchars($success); ?>',
            confirmButtonColor: '#6c5ce7'
        }).then(() => {
            window.location.href = 'login.php';
        });
    <?php endif; ?>

    $('#register-form').on('submit', function(e) {
        let pass = $('#register_password').val();
        let confirm = $('#register_confirm').val();

        if(pass.length < 8 || !/\d/.test(pass)) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long and contain at least one digit!',
                confirmButtonColor: '#6c5ce7'
            });
            return false;
        }

        if(pass !== confirm) {
            e.preventDefault();
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Passwords do not match!',
                confirmButtonColor: '#6c5ce7'
            });
            return false;
        }
    });
});
</script>
</body>
</html>