<?php
include 'config.php';
session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <h1 class="chat-title">Chat</h1>
    <div class="chat-container">
        <div class="chat-messages" id="chat-window"></div>

        <div class="chat-input-bar">
            <form id="chat-form" enctype="multipart/form-data" class="chat-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                
                <label style="cursor: pointer; font-size: 28px; color: var(--text-muted);">
                    <i class="fa-solid fa-paperclip"></i>
                    <input type="file" name="chat_file" id="chat_file" hidden accept="image/*,.pdf,.doc,.docx,.txt">
                </label>
                <textarea name="message" id="message-text" placeholder="Write a message..." rows="1"></textarea>
                <button type="submit" class="send-btn"><i class="fa-solid fa-paper-plane"></i></button>
            </form>
            <div id="file-preview" style="font-size: 14px; color: var(--link-blue); margin-top: 12px; font-weight: 800; padding-left: 20px;"></div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    const chatWindow = $('#chat-window');
    const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token']; ?>";
    let currentContent = ""; 
    let shouldScroll = true;

    function fetchMessages(forceScroll = false) {
        $.ajax({
            url: 'ajax/get_messages.php',
            type: 'GET',
            cache: false,
            success: function(response) {
                if (response.trim() !== currentContent.trim()) {
                    const isAtBottom = chatWindow[0].scrollHeight - chatWindow.scrollTop() <= chatWindow.outerHeight() + 100;
                    
                    currentContent = response;
                    chatWindow.html(response);
                    if (forceScroll || isAtBottom) {
                        chatWindow.scrollTop(chatWindow[0].scrollHeight);
                    }
                }
            }
        });
    }

    fetchMessages(true);
    setInterval(() => fetchMessages(false), 3000);

    $(document).on('click', '.like-btn', function(e) {
        e.preventDefault();
        let btn = $(this);
        let msgId = btn.data('id');

        btn.toggleClass('liked');

        $.ajax({
            url: 'ajax/like_message.php',
            type: 'POST',
            data: { 
                message_id: msgId,
                csrf_token: CSRF_TOKEN 
            },
            success: function(res) {
                if(res.trim() === "success") {
                    currentContent = ""; 
                    fetchMessages(false); 
                } else {
                    btn.toggleClass('liked');
                }
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        let msgId = $(this).data('id');
        
        Swal.fire({
            title: 'Delete message?',
            text: "Are you sure you want to remove this message?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c5ce7',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/delete_message.php', { 
                    message_id: msgId,
                    csrf_token: CSRF_TOKEN 
                }, function() {
                    currentContent = "";
                    fetchMessages(false);
                });
            }
        });
    });

    $('#chat-form').on('submit', function(e) {
        e.preventDefault();
        if($('#message-text').val().trim() === "" && !$('#chat_file')[0].files[0]) return;

        let formData = new FormData(this);
        formData.append('csrf_token', CSRF_TOKEN);

        $.ajax({
            url: 'ajax/send_message.php',
            type: 'POST',
            data: formData, 
            contentType: false, 
            processData: false,
            success: function(res) {
                if(res.trim() === "success" || res.trim() === "") {
                    $('#chat-form')[0].reset();
                    $('#file-preview').text("");
                    currentContent = "";
                    fetchMessages(true);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: res,
                        confirmButtonColor: '#6c5ce7'
                    });
                }
            }
        });
    });

    $('#chat_file').change(function() { 
        if(this.files[0]) {
            $('#file-preview').text("📎 " + this.files[0].name); 
        }
    });
});
</script>

<?php include 'includes/footer.php'; ?>