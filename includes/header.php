<?php
ob_start();

?>
<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">
                <span>✈️</span> Авиабилеты
            </a>
            
            <div class="search-button-container">
                <a href="search.php" class="btn-search-simple">
                    <span>🔍</span> Поиск билетов
                </a>
            </div>
            
            <div class="nav-links">
                <?php if (isset($_SESSION["user_id"])): ?>
                    <div class="user-info">
                        <span class="user-name"><?php echo htmlspecialchars($_SESSION["user_name"]); ?></span>
                        <?php if ($_SESSION["role"] == 1): ?>
                            <a href="admin.php" class="btn btn-logout">Админ-панель</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-logout">Выйти</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-login">Войти</a>
                    <a href="register.php" class="btn btn-login">Регистрация</a>
                    <a href="contact.php" class="btn btn-login">Обратная связь</a>
                <?php endif; ?>
                
                <!-- В header.php в nav-links добавим: -->
<?php if (isset($_SESSION["user_id"])): ?>
    <a href="bookings.php" class="btn-login">Мои бронирования</a>
    <a href="contact.php" class="btn btn-login">Обратная связь</a>
<?php endif; ?>
            </div>
        </div>
    </div>
</header>
