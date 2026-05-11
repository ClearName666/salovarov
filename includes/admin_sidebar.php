
<aside class="admin-sidebar">
    <div class="admin-logo">
        <h2><i class="fas fa-plane"></i> Админ панель</h2>
        <p>Добро пожаловать, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</p>
    </div>
    
    <nav class="admin-nav">
        <ul>
            <li><a href="admin.php?page=dashboard" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'dashboard') ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Панель управления
            </a></li>
            
            <li><a href="admin.php?page=flights" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'flights') ? 'active' : ''; ?>">
                <i class="fas fa-plane-departure"></i> Управление рейсами
            </a></li>
            
            <li><a href="admin.php?page=bookings" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'bookings') ? 'active' : ''; ?>">
                <i class="fas fa-ticket-alt"></i> Бронирования
            </a></li>
            
            <li><a href="admin.php?page=users" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'users') ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Пользователи
            </a></li>
            
            <li><a href="admin.php?page=reports" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'reports') ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i> Отчеты
            </a></li>
            
            <li><a href="admin.php?page=settings" class="<?php echo (isset($_GET['page']) && $_GET['page'] == 'settings') ? 'active' : ''; ?>">
                <i class="fas fa-cogs"></i> Настройки
            </a></li>
        </ul>
    </nav>
    
    <div class="sidebar-footer">
        <div class="system-info">
            <p><i class="fas fa-user-shield"></i> Роль: Администратор</p>
            <p><i class="fas fa-calendar"></i> <?php echo date('d.m.Y'); ?></p>
        </div>
    </div>
</aside>

