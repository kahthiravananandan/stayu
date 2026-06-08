    <footer class="site-footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <span class="footer-logo">StayU</span>
                    <p>Sistem Carian Penginapan Luar Kampus Berpengesahan untuk Pelajar UKM.</p>
                    <p class="footer-sub">Diuruskan oleh Pusat Perumahan Pelajar (PPP), HEP UKM.</p>
                </div>
                <div class="footer-links">
                    <h4>Pautan Pantas</h4>
                    <ul>
                        <li><a href="<?= BASE_URL ?>/student/search">Cari Penginapan</a></li>
                        <li><a href="<?= BASE_URL ?>/student/map">Peta</a></li>
                        <?php if (!isLoggedIn()): ?>
                        <li><a href="<?= BASE_URL ?>/auth/login">Log Masuk</a></li>
                        <li><a href="<?= BASE_URL ?>/auth/register">Daftar</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="footer-contact">
                    <h4>Hubungi Kami</h4>
                    <p>PPP UKM, Bangi, Selangor</p>
                    <p>ppp@ukm.edu.my</p>
                    <p>+603-8921 5000</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> StayU — HEP UKM. Hak Cipta Terpelihara.</p>
            </div>
        </div>
    </footer>
    <script src="<?= BASE_URL ?>/public/js/main.js"></script>
    <?php if (!empty($extraScripts)) echo $extraScripts; ?>
</body>
</html>
