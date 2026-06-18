<?php
$timeout = 14400; 
session_start(['cookie_lifetime' => $timeout, 'use_strict_mode' => true]);
include 'config.php';

if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit(); 
}

$u_id = (int)$_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $u_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

include 'includes/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container" style="padding: 40px 0; padding-bottom: 120px;">
    
    <div class="dashboard-grid" style="display: grid; grid-template-columns: 350px 1fr; gap: 30px; margin-bottom: 60px; align-items: start;">
        
        <aside class="auth-box" style="width: 100%; max-width: none; margin: 0; border: 1.5px solid var(--link-blue); padding: 30px;">
            <h2 style="font-size: 22px; margin-bottom: 20px;">Profile</h2>

            <div class="profile-pic-wrapper" style="width: 130px; height: 130px; margin: 0 auto 15px; border-radius: 50%; border: 3px solid var(--link-blue); overflow: hidden; background: #eee;">
                <img src="<?php echo !empty($user_data['profile_pic']) ? htmlspecialchars($user_data['profile_pic']) : 'uploads/profiles/default.png'; ?>" 
                     alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
            </div>

            <h3 style="font-weight: 800; margin-bottom: 25px;">
                <?php echo htmlspecialchars($user_data['full_name']); ?>
            </h3>
            
            <div style="display: flex; flex-direction: column; gap: 12px;">
                <a href="profile.php" class="btn-primary" style="text-decoration: none; display: block; padding: 12px;">Profile</a>
                <a href="messages.php" class="btn-primary" style="text-decoration: none; display: block; padding: 12px; background: var(--hub-purple);">Messages</a>
            </div>
        </aside>

        <section class="auth-box" style="width: 100%; max-width: none; margin: 0; border: 1.5px solid var(--link-blue); padding: 30px;">
            <h2 style="font-size: 22px; margin-bottom: 25px;">News</h2>

            <form id="news-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Header</label>
                    <input type="text" name="news_title" required>
                </div>

                <div class="form-group">
                    <label>Text</label>
                    <textarea name="news_content" rows="4" style="resize: none;" required></textarea>
                </div>

                <div class="form-group">
                    <label>Apply photo</label>
                    <input type="file" name="news_image" accept="image/*">
                </div>

                <button type="submit" class="btn-primary">Publish</button>
            </form>
        </section>
    </div>

    <h2 style="font-weight: 900; margin-bottom: 30px; color: var(--hub-purple);">
        <i class="fa-solid fa-bolt"></i> LATEST NEWS
    </h2>

    <div class="news-container">
        <div class="news-track" id="news-track">
            <?php
            $news = $conn->query("SELECT * FROM news ORDER BY created_at DESC");

            while($n = $news->fetch_assoc()):
            ?>
                <div class="news-card" style="position: relative;">
                    
                    <?php if(isset($user_data['role']) && $user_data['role'] === 'admin'): ?>
                        <button class="delete-news-btn" 
                                data-id="<?php echo (int)$n['news_id']; ?>" 
                                style="position: absolute; top: 10px; right: 10px; background: rgba(255, 71, 87, 0.95); color: white; border: none; border-radius: 50%; width: 32px; height: 32px; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 8px rgba(0,0,0,0.2); transition: transform 0.2s, background 0.2s; z-index: 10;" 
                                title="Delete News">
                            <i class="fa-solid fa-trash" style="font-size: 13px; color: white !important;"></i>
                        </button>
                    <?php endif; ?>

                    <img src="<?php echo !empty($n['image_path']) ? htmlspecialchars($n['image_path']) : 'uploads/news/default.jpg'; ?>" alt="News">

                    <h4 class="dynamic-title">
                        <?php echo htmlspecialchars($n['title']); ?>
                    </h4>

                    <button class="btn-read open-news-btn" 
                            data-title="<?php echo htmlspecialchars($n['title']); ?>" 
                            data-content="<?php echo htmlspecialchars($n['content']); ?>">
                        READ
                    </button>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</div>

<div id="newsModal" class="modal" onclick="closeModalOutside(event)">
    <div class="modal-content">
        <h2 id="m-title" style="color: var(--hub-purple); margin-bottom: 20px; font-size: 26px;"></h2>
        <div id="m-body" style="font-size: 17px; line-height: 1.8; color: #444; max-height: 400px; overflow-y: auto; white-space: pre-wrap;"></div>
        <button class="btn-primary" onclick="closeModal()" style="width: auto; margin-top: 30px; padding: 10px 30px;">CLOSE</button>
    </div>
</div>

<script>
$(document).ready(function() {
    function adjustFontSize() {
        $('.dynamic-title').each(function() {
            let el = $(this);
            let fontSize = parseInt(el.css('font-size'));

            while (this.scrollHeight > this.clientHeight && fontSize > 11) {
                fontSize--;
                el.css('font-size', fontSize + 'px');
            }
        });
    }

    adjustFontSize();

    let step = 0;

    setInterval(function() {
        const track = $('#news-track');
        const cards = $('.news-card');

        if(cards.length > 4) {
            step++;

            if(step > cards.length - 4) {
                step = 0;
            }

            track.css('transform', `translateX(-${step * 305}px)`);
        }
    }, 10000);

    $('.open-news-btn').on('click', function() {
        $('#m-title').text($(this).data('title'));
        $('#m-body').text($(this).data('content'));
        $('#newsModal').fadeIn(300);
    });

    window.closeModal = function() { 
        $('#newsModal').fadeOut(300); 
    }

    window.closeModalOutside = function(event) {
        const modal = document.getElementById('newsModal');

        if (event.target === modal) {
            $('#newsModal').fadeOut(300);
        }
    }

    $('#news-form').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'ajax/create_news.php',
            type: 'POST',
            data: new FormData(this),
            contentType: false, 
            processData: false,
            success: function() { 
                location.reload(); 
            }
        });
    });

    $(document).on('mouseenter', '.delete-news-btn', function() {
        $(this).css('transform', 'scale(1.15)').css('background', '#ff4757');
    }).on('mouseleave', '.delete-news-btn', function() {
        $(this).css('transform', 'scale(1)').css('background', 'rgba(255, 71, 87, 0.95)');
    });

    $(document).on('click', '.delete-news-btn', function(e) {
        e.preventDefault();

        const newsId = $(this).data('id');
        const card = $(this).closest('.news-card');

        Swal.fire({
            title: 'Delete News?',
            text: "Are you sure you want to remove this news item?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ff4757',
            cancelButtonColor: '#6c5ce7',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('ajax/delete_news.php', { id: newsId }, function(res) {
                    if (res === "success") {
                        card.fadeOut(300, function() {
                            location.reload();
                        });
                    } else {
                        Swal.fire('Error!', res, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error!', 'Server error. Please try again later.', 'error');
                });
            }
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
