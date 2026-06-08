<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
include 'includes/header.php';
?>

<div class="container">
    <h1 class="chat-title">Linkhub AI Assistent</h1>
    <p style="text-align:center; color:var(--text-muted); margin-bottom:20px; font-weight:700;">
        ⚡ Powered by Groq
    </p>

    <div class="chat-container">
        <div class="chat-messages" id="ai-chat-box">
            <div class="message-wrapper msg-received">
                <div class="message-bubble ai-bubble">
                    <span class="sender-name">LinkHub AI</span>
                    Hello, <?php echo $_SESSION['username']; ?>! How can I help you today?
                </div>
            </div>
        </div>

        <div class="chat-input-bar">
            <form id="ai-form" class="chat-form">
                <textarea id="ai-text" placeholder="Ask a question..." required></textarea>
                <button type="submit" class="send-btn">
                    <i class="fa-solid fa-microchip"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#ai-form').on('submit', function(e) {
        e.preventDefault();
        let msg = $('#ai-text').val().trim();
        if(msg == "") return;

        $('#ai-chat-box').append(`
            <div class="message-wrapper msg-sent">
                <div class="message-bubble">
                    <span class="sender-name">You</span>
                    ${msg}
                </div>
            </div>
        `);
        
        $('#ai-text').val("");
        let loaderId = "load_" + Date.now();
        $('#ai-chat-box').append(`
            <div class="message-wrapper msg-received" id="${loaderId}">
                <div class="message-bubble ai-bubble" style="opacity: 0.6;">LinkHub AI generates answer...</div>
            </div>
        `);
        $('#ai-chat-box').scrollTop($('#ai-chat-box')[0].scrollHeight);

        $.post('ajax/ai_query.php', { message: msg }, function(response) {
            $('#' + loaderId).remove();
            $('#ai-chat-box').append(`
                <div class="message-wrapper msg-received">
                    <div class="message-bubble ai-bubble">
                        <span class="sender-name">LinkHub AI</span>
                        ${response}
                    </div>
                </div>
            `);
            $('#ai-chat-box').animate({ scrollTop: $('#ai-chat-box')[0].scrollHeight }, 500);
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>