<?php
include_once "connect.php";

$users_count = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$flights_count = $conn->query("SELECT COUNT(*) FROM flights")->fetch_row()[0];
$bookings_count = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
$revenue = $conn->query("SELECT SUM(total_price) FROM bookings WHERE status = 'confirmed'")->fetch_row()[0] ?? 0;
?>
<h1>Обзор системы</h1>
<div class="stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-top: 20px;">
    <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Пользователи</h3>
        <p style="font-size: 24px; font-weight: bold;"><?php echo $users_count; ?></p>
    </div>
    <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Рейсы</h3>
        <p style="font-size: 24px; font-weight: bold;"><?php echo $flights_count; ?></p>
    </div>
    <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Бронирования</h3>
        <p style="font-size: 24px; font-weight: bold;"><?php echo $bookings_count; ?></p>
    </div>
    <div class="stat-card" style="background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <h3>Доход</h3>
        <p style="font-size: 24px; font-weight: bold; color: #27ae60;"><?php echo number_format($revenue, 0, '.', ' '); ?> ₽</p>
    </div>
</div>

<style>
.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.quick-action {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s ease;
}

.quick-action:hover {
    background: #3498db;
    color: white;
    transform: translateY(-5px);
}

.quick-action i {
    font-size: 2rem;
    margin-bottom: 10px;
}

.user-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.user-item {
    display: flex;
    align-items: center;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #3498db;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    font-weight: bold;
}

.user-details {
    flex: 1;
}

.user-details small {
    display: block;
    color: #666;
    font-size: 0.9rem;
}

.user-date {
    font-size: 0.8rem;
    color: #999;
}

.user-role {
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: bold;
}

.role-admin {
    background: #e74c3c;
    color: white;
}

.role-user {
    background: #3498db;
    color: white;
}

.system-info-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
}

.info-item i {
    font-size: 1.5rem;
    color: #3498db;
}

.info-item div {
    flex: 1;
}

.info-item strong {
    display: block;
    color: #333;
}

.info-item span {
    color: #666;
    font-size: 0.9rem;
}
</style>
