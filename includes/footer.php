<footer class="main-footer">
    <div class="container footer-layout">
        <div class="footer-zone">
            <div class="cookie-info-btn" onclick="showCookieInfo()" title="Cookie Policy">
                <i class="fa-solid fa-cookie-bite"></i>
            </div>
        </div>

        <div class="footer-zone footer-center">
            <div class="footer-socials">
                <a href="https://www.google.com" target="_blank" title="Google"><i class="fa-brands fa-google"></i></a>
                <a href="https://www.facebook.com" target="_blank" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                <a href="https://www.youtube.com" target="_blank" title="YouTube"><i class="fa-brands fa-youtube"></i></a>
                <a href="ai_chat.php" title="AI assistant"><i class="fa-solid fa-robot"></i></a>
            </div>
        </div>

        <div class="footer-zone footer-right">
            <div class="footer-copyright">
                &copy; 2026 <strong>LinkHub</strong>. All rights reserved.
            </div>
        </div>
    </div>
</footer>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function showCookieInfo() {
    Swal.fire({
        title: 'Cookie Policy',
        html: '<strong>LinkHub</strong> uses strictly necessary session cookies to keep your account secure. These cookies expire after 4 hours and are essential for the service to function. No tracking or marketing cookies are used.',
        icon: 'info',
        confirmButtonColor: '#6c5ce7',
        confirmButtonText: 'I Understand',
        background: '#ffffff'
    });
}
</script>

</body>
</html>
