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
$is_admin = ($_SESSION['role'] == 'admin');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container">
    <h1 class="chat-title">Polls</h1>

    <?php if ($is_admin): ?>
    <div class="poll-create-box">
        <h3 style="margin-bottom: 20px; font-weight: 900;">📊 Create new poll</h3>
        <form id="create-poll-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            
            <div class="form-group">
                <label>Question:</label>
                <input type="text" name="question" placeholder="Enter the question..." required>
            </div>
            
            <label style="display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600;">Options:</label>
            <div id="options-container">
                <div class="poll-option-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <input type="text" name="options[]" placeholder="Option 1" required>
                    </div>
                </div>

                <div class="poll-option-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <input type="text" name="options[]" placeholder="Option 2" required>
                    </div>
                </div>
            </div>

            <button type="button" class="btn-add-option" id="add-option-btn" style="margin-top: 10px;">
                <i class="fa-solid fa-plus"></i> Add new option
            </button>
            <button type="submit" class="btn-submit-poll" style="margin-top: 20px;">Publish</button>
        </form>
    </div>
    <?php endif; ?>

    <div id="polls-list">
        </div>
</div>

<script>
$(document).ready(function() {

    const CSRF_TOKEN = "<?php echo $_SESSION['csrf_token']; ?>";
    let optionCount = 2;

    $('#add-option-btn').click(function() {
        if (optionCount < 5) {
            optionCount++;
            $('#options-container').append(`
                <div class="poll-option-input" style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; animation: fadeInBubble 0.3s ease;">
                    <div class="form-group" style="flex: 1; margin-bottom: 0;">
                        <input type="text" name="options[]" placeholder="Option ${optionCount}" required>
                    </div>
                    <button type="button" class="remove-opt" style="background:none; border:none; color:#ff4757; cursor:pointer; font-size:20px; padding: 5px;">
                        <i class="fa-solid fa-circle-xmark"></i>
                    </button>
                </div>
            `);
        } else { 
            Swal.fire({
                icon: 'warning',
                title: 'Limit reached',
                text: 'Maximum 5 options allowed.',
                confirmButtonColor: '#6c5ce7'
            });
        }
    });

    $(document).on('click', '.remove-opt', function() { 
        $(this).closest('.poll-option-input').remove(); 
        optionCount--; 
    });

    function fetchPolls() {
        $.get('ajax/get_polls.php', function(res) {
            $('#polls-list').html(res);
        });
    }

    fetchPolls();

    $('#create-poll-form').on('submit', function(e) {
        e.preventDefault();
        $.post('ajax/create_poll.php', $(this).serialize(), function(res) {
            if(res.trim() === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Poll created successfully!',
                    confirmButtonColor: '#6c5ce7'
                });
                $('#create-poll-form')[0].reset();
                optionCount = 2;
                fetchPolls();
            } else {
                Swal.fire('Error!', res, 'error');
            }
        });
    });

    $(document).on('click', '.poll-option-row', function() {
        const pollId = $(this).data('poll');
        const optionId = $(this).data('option');

        $.post('ajax/vote_poll.php', { 
            poll_id: pollId, 
            option_id: optionId,
            csrf_token: CSRF_TOKEN 
        }, function(res) {
            if(res.trim() === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Vote registered!',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 2000
                });
                fetchPolls();
            } else {
                Swal.fire('Error!', res, 'error');
            }
        });
    });

    $(document).on('click', '.delete-poll-btn', function() {
        const pollId = $(this).data('id');
        
        Swal.fire({
            title: 'Delete this poll?',
            text: "Are you sure you want to permanently remove this poll?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c5ce7',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/delete_poll.php', { 
                    poll_id: pollId,
                    csrf_token: CSRF_TOKEN 
                }, function(res) {
                    if(res.trim() === "success") {
                        Swal.fire(
                            'Deleted!',
                            'The poll has been deleted.',
                            'success'
                        );
                        fetchPolls();
                    } else {
                        Swal.fire('Error!', res, 'error');
                    }
                });
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>