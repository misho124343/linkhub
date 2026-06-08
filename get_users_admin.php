<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$my_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM users ORDER BY user_id ASC");
$stmt->execute();
$res = $stmt->get_result();

while ($row = $res->fetch_assoc()) {
    $u_id = $row['user_id'];
    $is_me = ($u_id == $my_id);
    $role_badge = ($row['role'] == 'admin') ? '<span class="role-badge role-admin">Admin</span>' : '<span class="role-badge role-user">User</span>';

    echo '<tr>';
    echo '<td style="font-weight:800; color:var(--text-muted);">' . (int)$u_id . '</td>';
    echo '<td><div class="user-info-cell"><b>' . htmlspecialchars($row['full_name']) . '</b> ' . ($is_me ? '<small style="color:var(--link-blue)">(Аз)</small>' : '') . '</div></td>';
    echo '<td>' . $role_badge . '</td>';
    echo '<td>';

    if (!$is_me) {
        if ($row['role'] == 'admin') {
            echo '<button class="btn-action btn-revoke-admin btn-role-toggle" data-id="' . (int)$u_id . '" data-current="admin"><i class="fa-solid fa-user-shield"></i> Take Admin</button>';
        } else {
            echo '<button class="btn-action btn-give-admin btn-role-toggle" data-id="' . (int)$u_id . '" data-current="user"><i class="fa-solid fa-unlock"></i> Promote to Admin</button>';
        }
        echo '<button class="btn-action btn-delete-user" data-id="' . (int)$u_id . '"><i class="fa-solid fa-trash-can"></i> Delete</button>';
    } else {
        echo '<span style="font-size:12px; color:var(--text-muted); font-style:italic;">Secured permission</span>';
    }

    echo '</td>';
    echo '</tr>';
}

$stmt->close();
?>