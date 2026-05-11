<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}
include_once "connect.php";

$page = $_GET['page'] ?? 'dashboard';
$pages = [
    'dashboard' => 'pages/admin_dashboard.php',
    'flights' => 'pages/admin_flights.php',
    'bookings' => 'pages/admin_bookings.php',
    'users' => 'pages/admin_users.php'
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>SkyAdmin Ultimate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { 
            --primary: #4f46e5; --sidebar: #111827; --bg: #f3f4f6; 
            --success: #10b981; --danger: #ef4444; --warning: #f59e0b;
        }
        body { font-family: 'Inter', sans-serif; margin: 0; background: var(--bg); display: flex; color: #1f2937; }
        
        /* Sidebar */
        .sidebar { width: 280px; background: var(--sidebar); height: 100vh; position: sticky; top: 0; padding: 25px; box-sizing: border-box; color: white; }
        .sidebar-brand { font-size: 24px; font-weight: 800; margin-bottom: 40px; color: #6366f1; display: flex; align-items: center; gap: 12px; }
        .nav-menu { list-style: none; padding: 0; }
        .nav-link { 
            display: flex; align-items: center; gap: 12px; padding: 14px 18px; 
            color: #9ca3af; text-decoration: none; border-radius: 12px; margin-bottom: 8px; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { background: #1f2937; color: white; }
        .nav-link.active { background: var(--primary); box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4); }

        /* Main Content */
        .main { flex: 1; padding: 40px; max-width: 1200px; margin: 0 auto; }
        .header-flex { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        /* Cards & Tables */
        .card { background: white; border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 24px; border: 1px solid #e5e7eb; }
        .table-res { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f9fafb; padding: 16px; text-align: left; font-size: 13px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #edf2f7; }
        td { padding: 16px; border-bottom: 1px solid #f3f4f6; }
        
        /* Status Badges */
        .badge { padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .b-confirmed { background: #ecfdf5; color: #059669; }
        .b-pending { background: #fffbeb; color: #d97706; }
        .b-cancelled { background: #fef2f2; color: #dc2626; }

        /* Forms & Buttons */
        .btn { padding: 10px 20px; border-radius: 10px; border: none; font-weight: 600; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }
        .form-input { padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; transition: 0.2s; }
        .form-input:focus { border-color: var(--primary); ring: 2px var(--primary); }

        /* Animations */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade { animation: fadeIn 0.4s ease-out; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand"><i class="fas fa-rocket"></i> SkyPanel Pro</div>
        <nav>
            <ul class="nav-menu">
                <li><a href="admin.php?page=dashboard" class="nav-link <?= $page=='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i> Дашборд</a></li>
                <li><a href="admin.php?page=flights" class="nav-link <?= $page=='flights'?'active':'' ?>"><i class="fas fa-plane-departure"></i> Рейсы</a></li>
                <li><a href="admin.php?page=bookings" class="nav-link <?= $page=='bookings'?'active':'' ?>"><i class="fas fa-clipboard-list"></i> Бронирования</a></li>
                <li><a href="admin.php?page=users" class="nav-link <?= $page=='users'?'active':'' ?>"><i class="fas fa-user-shield"></i> Персонал/Клиенты</a></li>
                <li>
    <a href="index.php" class="nav-link" style="margin-top: 20px; background: rgba(255,255,255,0.05);">
        <i class="fas fa-home"></i> На главную сайт
    </a>
</li>
<li>
    <a href="logout.php" class="nav-link" style="color: #f87171;">
        <i class="fas fa-sign-out-alt"></i> Выйти
    </a>
</li>
            </ul>
        </nav>
    </aside>

    <main class="main animate-fade">
        <?php include $pages[$page]; ?>
    </main>

    <script>
        // Функция для уведомлений (можно вызвать из PHP через echo)
        function notify(msg, type='success') {
            const toast = document.createElement('div');
            toast.style = `position: fixed; bottom: 20px; right: 20px; background: ${type=='success'?'#10b981':'#ef4444'}; color: white; padding: 15px 25px; border-radius: 10px; z-index: 1000; box-shadow: 0 10px 15px rgba(0,0,0,0.1); animation: fadeIn 0.3s;`;
            toast.innerText = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }
    </script>
</body>
<script>
        // Функция для уведомлений (можно вызвать из PHP через echo)
        function notify(msg, type='success') {
            const toast = document.createElement('div');
            toast.style = `position: fixed; bottom: 20px; right: 20px; background: ${type=='success'?'#10b981':'#ef4444'}; color: white; padding: 15px 25px; border-radius: 10px; z-index: 1000; box-shadow: 0 10px 15px rgba(0,0,0,0.1); animation: fadeIn 0.3s;`;
            toast.innerText = msg;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // Функции для работы с модальным окном добавления рейса
        function openFlightModal() {
            const modal = document.getElementById('flightModal');
            if (modal) {
                modal.style.display = 'flex';
                
                // Устанавливаем сегодняшнюю дату по умолчанию
                const today = new Date().toISOString().split('T')[0];
                const dateInput = modal.querySelector('input[name="departure_date"]');
                if (dateInput) {
                    dateInput.value = today;
                }
                
                // Устанавливаем время по умолчанию (через 2 часа от текущего)
                const now = new Date();
                now.setHours(now.getHours() + 2);
                const timeString = now.toTimeString().substring(0, 5);
                const timeInput = modal.querySelector('input[name="departure_time"]');
                if (timeInput) {
                    timeInput.value = timeString;
                }
            }
        }

        function closeFlightModal() {
            const modal = document.getElementById('flightModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }

        // Закрытие модального окна при клике вне его
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('flightModal');
            if (modal && modal.style.display === 'flex' && e.target === modal) {
                closeFlightModal();
            }
        });

        // Закрытие модального окна при нажатии Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFlightModal();
            }
        });

        // Добавляем обработчик на кнопку добавления рейса
        document.addEventListener('DOMContentLoaded', function() {
            const addBtn = document.getElementById('addFlightBtn');
            if (addBtn) {
                addBtn.addEventListener('click', openFlightModal);
            }
            
            // Валидация формы добавления рейса
            const flightForm = document.getElementById('addFlightForm');
            if (flightForm) {
                flightForm.addEventListener('submit', function(e) {
                    const price = this.querySelector('input[name="price"]');
                    const seats = this.querySelector('input[name="available_seats"]');
                    
                    if (price && price.value <= 0) {
                        e.preventDefault();
                        alert('Цена должна быть больше 0');
                        return false;
                    }
                    
                    if (seats && seats.value <= 0) {
                        e.preventDefault();
                        alert('Количество мест должно быть больше 0');
                        return false;
                    }
                    
                    return true;
                });
            }
        });
    </script>
</body>
</html>
</html>