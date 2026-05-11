
<header class="admin-header">
    <div>
        <h1><i class="fas fa-cog"></i> Административная панель</h1>
        <p>Управление системой бронирования авиабилетов</p>
    </div>
    <div class="admin-user">
        <div class="user-info">
            <i class="fas fa-user-circle"></i>
            <span><?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
            <div class="user-dropdown">
                <a href="index.php"><i class="fas fa-home"></i> На главную</a>
                <a href="profile.php"><i class="fas fa-user"></i> Мой профиль</a>
                <a href="admin.php?page=settings"><i class="fas fa-cog"></i> Настройки</a>
                <div class="divider"></div>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Выйти</a>
            </div>
        </div>
    </div>
</header>

