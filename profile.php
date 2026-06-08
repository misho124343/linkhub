<?php
$timeout = 14400; 
session_start(['cookie_lifetime' => $timeout, 'use_strict_mode' => true]);
include 'config.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$u_id = (int)$_SESSION['user_id'];
$msg = "";
$msg_type = ""; 

if (isset($_POST['update_info'])) {
    $new_name = $_POST['full_name'];
    
    if (!empty($_FILES['profile_pic']['name']) && $_FILES['profile_pic']['error'] == 0) {
        $upload_dir = __DIR__ . "/uploads/profiles/";
        
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = time() . "_" . basename($_FILES['profile_pic']['name']);
        $target_path = $upload_dir . $filename;
        $db_save_path = "uploads/profiles/" . $filename;

        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_path)) {
            $stmt_pic = $conn->prepare("UPDATE users SET profile_pic = ? WHERE user_id = ?");
            $stmt_pic->bind_param("si", $db_save_path, $u_id);
            $stmt_pic->execute();
            $stmt_pic->close();

            $msg = "Success!";
            $msg_type = "success";
        } else {
            $msg = "Error saving the photo!";
            $msg_type = "error";
        }
    }
    
    $stmt_name = $conn->prepare("UPDATE users SET full_name = ? WHERE user_id = ?");
    $stmt_name->bind_param("si", $new_name, $u_id);
    
    if ($stmt_name->execute()) {
        if (empty($msg)) { 
            $msg = "Success!"; 
            $msg_type = "success";
        }
    }
    $stmt_name->close();
}

if (isset($_POST['change_pass'])) {
    $old_p = $_POST['old_pass'];
    $new_p = $_POST['new_pass'];

    $stmt_pass = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt_pass->bind_param("i", $u_id);
    $stmt_pass->execute();
    $res = $stmt_pass->get_result();
    $row = $res->fetch_assoc();
    
    if ($row && password_verify($old_p, $row['password'])) {
        $hashed = password_hash($new_p, PASSWORD_DEFAULT);
        
        $stmt_upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt_upd->bind_param("si", $hashed, $u_id);
        $stmt_upd->execute();
        $stmt_upd->close();

        $msg = "Password changed successfully!";
        $msg_type = "success";
    } else {
        $msg = "Wrong password!";
        $msg_type = "error";
    }
    $stmt_pass->close();
}

$stmt_user = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt_user->bind_param("i", $u_id);
$stmt_user->execute();
$user = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container" style="padding: 40px 0; padding-bottom: 120px;">
    <div style="margin-bottom: 20px;">
        <a href="index.php" style="text-decoration: none; color: var(--link-blue); font-weight: 700;">
            <i class="fa-solid fa-arrow-left"></i> Return
        </a>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; align-items: start;">
        
        <div class="content-box">
            <h2 style="margin-bottom: 20px;">Edit profile</h2>
            
            <form id="profile-info-form" action="profile.php" method="POST" enctype="multipart/form-data">
                <div class="profile-image-circle" style="width: 140px; height: 140px; margin-bottom: 10px;">
                    <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'uploads/profiles/default.png'; ?>" alt="Avatar">
                </div>
                
                <div style="margin-bottom: 20px; font-size: 11px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">
                    Profile made: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?>
                </div>

                <div class="form-group">
                    <label>Full name</label>
                    <input type="text" id="full_name_input" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Profile picture</label>
                    <input type="file" name="profile_pic" accept="image/*">
                </div>
                
                <input type="hidden" name="update_info" value="1">
                <button type="submit" class="btn-primary">Save changes</button>
            </form>
        </div>

        <div class="content-box">
            <h2 style="margin-bottom: 20px;">Change password</h2>
            <form id="profile-password-form" action="profile.php" method="POST" style="margin-top: 10px;">
                <div class="form-group">
                    <label>Current password</label>
                    <input type="password" id="old_pass_input" name="old_pass" placeholder="Enter old password" required>
                </div>
                <div class="form-group">
                    <label>New password</label>
                    <input type="password" id="new_pass_input" name="new_pass" placeholder="Enter new password" required>
                </div>
                <div style="margin-top: 25px;">
                    <input type="hidden" name="change_pass" value="1">
                    <button type="submit" class="btn-primary" style="background: var(--hub-purple);">
                        Change
                    </button>
                </div>
            </form>
        </div>
        
    </div>
</div>

<script>
$(document).ready(function() {
    <?php if (!empty($msg)): ?>
        Swal.fire({
            icon: '<?php echo $msg_type; ?>',
            title: '<?php echo ($msg_type === "success") ? "Success!" : "Notice"; ?>',
            text: '<?php echo $msg; ?>',
            confirmButtonColor: '#6c5ce7'
        });
    <?php endif; ?>

    $('#profile-info-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Save changes?',
            text: "Are you sure you want to update your profile information?",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#6c5ce7',
            cancelButtonColor: '#6e7881',
            confirmButtonText: 'Yes, save!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });

    $('#profile-password-form').on('submit', function(e) {
        e.preventDefault();
        const form = this;

        Swal.fire({
            title: 'Change password?',
            text: "Are you sure you want to modify your account password?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c5ce7',
            confirmButtonText: 'Yes, change it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>