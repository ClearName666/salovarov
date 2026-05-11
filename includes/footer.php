<footer class="footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Авиабилеты</h3>
                <p>Ваш надежный партнер в поиске и бронировании авиабилетов по всему миру.</p>
            </div>
            
            <div class="footer-section">
                <h3>Навигация</h3>
                <ul class="footer-links">
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="search.php">Поиск билетов</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Аккаунт</h3>
                <ul class="footer-links">
                    <?php if (isset($_SESSION["user_id"])): ?>
                        <li><a href="logout.php">Выйти</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Войти</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Контакты</h3>
                <ul class="footer-links">
                    <li>📞 +7 (999) 123-45-67</li>
                    <li>✉️ info@aviabilety.ru</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Авиабилеты. Все права защищены.</p>
        </div>
    </div>
</footer>
<?php ob_end_flush(); ?>