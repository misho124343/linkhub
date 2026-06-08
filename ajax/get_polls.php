<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); 
    exit();
}

$u_id = $_SESSION['user_id'];
$is_admin = (isset($_SESSION['role']) && $_SESSION['role'] == 'admin');

$polls = $conn->query("SELECT * FROM polls ORDER BY created_at DESC");

while ($p = $polls->fetch_assoc()) {
    $p_id = $p['poll_id'];
    
    $stmt_vote = $conn->prepare("SELECT option_id FROM poll_votes WHERE poll_id = ? AND user_id = ?");
    $stmt_vote->bind_param("ii", $p_id, $u_id);
    $stmt_vote->execute();
    $check_vote = $stmt_vote->get_result();
    $my_vote = ($check_vote->num_rows > 0) ? $check_vote->fetch_assoc()['option_id'] : null;
    $stmt_vote->close();

    $stmt_total = $conn->prepare("SELECT COUNT(*) as total FROM poll_votes WHERE poll_id = ?");
    $stmt_total->bind_param("i", $p_id);
    $stmt_total->execute();
    $total_res = $stmt_total->get_result();
    $total_votes = $total_res->fetch_assoc()['total'];
    $stmt_total->close();

    echo '<div class="poll-card">';
    if ($is_admin) {
        echo '<button class="delete-poll-btn" data-id="'.(int)$p_id.'"><i class="fa-solid fa-trash-can"></i></button>';
    }

    echo '<h4 class="poll-question">'.htmlspecialchars($p['question']).'</h4>';

    $stmt_options = $conn->prepare("SELECT * FROM poll_options WHERE poll_id = ?");
    $stmt_options->bind_param("i", $p_id);
    $stmt_options->execute();
    $options = $stmt_options->get_result();

    while ($opt = $options->fetch_assoc()) {
        $opt_id = $opt['option_id'];
        
        $stmt_cnt = $conn->prepare("SELECT COUNT(*) as cnt FROM poll_votes WHERE option_id = ?");
        $stmt_cnt->bind_param("i", $opt_id);
        $stmt_cnt->execute();
        $opt_votes_res = $stmt_cnt->get_result();
        $opt_votes = $opt_votes_res->fetch_assoc()['cnt'];
        $stmt_cnt->close();
        
        $percent = ($total_votes > 0) ? round(($opt_votes / $total_votes) * 100) : 0;
        $is_selected = ($my_vote == $opt_id) ? '<span class="voted-mark">✔ Chosen</span>' : '';

        echo '<div class="poll-option-row" data-poll="'.(int)$p_id.'" data-option="'.(int)$opt_id.'">';
        echo '<div class="option-info">
                <span>'.htmlspecialchars($opt['option_text']).' '.$is_selected.'</span>
                <span>'.$percent.'%</span>
              </div>';
        echo '<div class="progress-container"><div class="progress-fill" style="width:'.$percent.'%"></div></div>';
        echo '</div>';
    }
    $stmt_options->close();

    $date_formatted = date('d.m.Y H:i', strtotime($p['created_at']));
    echo '<span class="poll-date">Published: '.$date_formatted.' • Total votes: '.(int)$total_votes.'</span>';
    echo '</div>';
}
?>
