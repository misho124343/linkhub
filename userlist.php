<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <h1 class="chat-title">User management</h1>

    <div class="admin-table-box">
        <table class="user-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="admin-users-list">
                </tbody>
        </table>
    </div>

    <div class="create-user-panel">
        <h2 style="text-align:center; font-weight:900; margin-bottom:35px; color:var(--text-main);">👤 Add user</h2>
        <form id="admin-create-user-form">
            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Full name:</label>
                <input type="text" name="full_name" placeholder="Enter full name..." required style="width:100%; padding:15px; border-radius:15px; border:1.5px solid var(--border-color);">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Username:</label>
                <input type="text" name="username" placeholder="Enter username..." required style="width:100%; padding:15px; border-radius:15px; border:1.5px solid var(--border-color);">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Password:</label>
                <input type="password" name="password" placeholder="Enter password..." required style="width:100%; padding:15px; border-radius:15px; border:1.5px solid var(--border-color);">
            </div>

            <div class="form-group" style="margin-bottom:20px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Repeat password:</label>
                <input type="password" name="confirm_password" placeholder="Repeat password..." required style="width:100%; padding:15px; border-radius:15px; border:1.5px solid var(--border-color);">
            </div>

            <div class="form-group" style="margin-bottom:25px;">
                <label style="display:block; margin-bottom:8px; font-weight:700;">Status:</label>
                <select name="role" style="width:100%; padding:15px; border-radius:15px; border:1.5px solid var(--border-color); font-family:inherit; cursor:pointer;">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <button type="submit" class="btn-primary" style="width:100%; padding:18px; border-radius:15px; font-weight:900; cursor:pointer;">CREATE PROFILE</button>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    function fetchAdminUsers() {
        $.get('ajax/get_users_admin.php', function(res) {
            $('#admin-users-list').html(res);
        });
    }

    fetchAdminUsers();
    $(document).on('click', '.btn-role-toggle', function() {
        let u_id = $(this).data('id');
        let current_role = $(this).data('current');
        let new_role = (current_role === 'admin') ? 'user' : 'admin';

        $.post('ajax/change_user_role.php', { user_id: u_id, role: new_role }, function(res) {
            if(res === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Role updated successfully!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                fetchAdminUsers();
            } else {
                Swal.fire('Error!', res, 'error');
            }
        });
    });

    $(document).on('click', '.btn-delete-user', function() {
        let u_id = $(this).data('id');

        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to permanently remove this user!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757', 
            cancelButtonColor: '#6c5ce7',
            confirmButtonText: 'Yes, remove user!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/delete_user.php', { user_id: u_id }, function(res) {
                    if(res === "success") {
                        Swal.fire(
                            'Removed!',
                            'The profile has been deleted.',
                            'success'
                        );
                        fetchAdminUsers();
                    } else {
                        Swal.fire('Error!', 'Could not complete operation.', 'error');
                    }
                });
            }
        });
    });

    $('#admin-create-user-form').on('submit', function(e) {
        e.preventDefault();
        
        let p1 = $("input[name='password']").val();
        let p2 = $("input[name='confirm_password']").val();
        
        if(p1.length < 8 || !/\d/.test(p1)) {
            Swal.fire({
                icon: 'error',
                title: 'Weak Password',
                text: 'Password must be at least 8 characters long and contain at least one digit!',
                confirmButtonColor: '#6c5ce7'
            });
            return;
        }
        
        if(p1 !== p2) {
            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                text: 'Passwords do not match!',
                confirmButtonColor: '#6c5ce7'
            });
            return;
        }

        $.post('ajax/add_user_admin.php', $(this).serialize(), function(res) {
            if(res === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'User added successfully to the organization!',
                    confirmButtonColor: '#6c5ce7'
                });
                $('#admin-create-user-form')[0].reset();
                fetchAdminUsers();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Database Error',
                    text: res,
                    confirmButtonColor: '#6c5ce7'
                });
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>