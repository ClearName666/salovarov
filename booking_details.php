<?php
// booking_details.php
ob_start();
session_start();
include_once "connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 1) {
    header("Location: ../login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Получаем детали бронирования
$sql = "SELECT b.*, f.*, u.*,
               DATE_FORMAT(b.created_at, '%d.%m.%Y %H:%i') as booking_date_formatted
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    echo "Бронирование не найдено";
    exit();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Детали бронирования | Админ-панель</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
    <?php include 'includes/header_admin.php'; ?>
    
    <div class="container">
        <div class="booking-details">
            <h1>Детали бронирования № <?php echo $booking['booking_reference']; ?></h1>
            
            <div class="details-card">
                <div class="section">
                    <h3>Информация о бронировании</h3>
                    <div class="detail-row">
                        <div class="label">ID:</div>
                        <div class="value"><?php echo $booking['id']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Номер бронирования:</div>
                        <div class="value"><?php echo $booking['booking_reference']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Дата создания:</div>
                        <div class="value"><?php echo $booking['booking_date_formatted']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Статус:</div>
                        <div class="value">
                            <?php 
                            $status_text = [
                                'confirmed' => 'Подтверждено',
                                'cancelled' => 'Отменено',
                                'pending' => 'Ожидание'
                            ];
                            echo $status_text[$booking['status']] ?? $booking['status'];
                            ?>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>Информация о пользователе</h3>
                    <div class="detail-row">
                        <div class="label">Имя:</div>
                        <div class="value"><?php echo htmlspecialchars($booking['fullname']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Email:</div>
                        <div class="value"><?php echo htmlspecialchars($booking['email']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Телефон:</div>
                        <div class="value"><?php echo htmlspecialchars($booking['phone']); ?></div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>Информация о рейсе</h3>
                    <div class="detail-row">
                        <div class="label">Авиакомпания:</div>
                        <div class="value"><?php echo htmlspecialchars($booking['airline']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Рейс:</div>
                        <div class="value"><?php echo htmlspecialchars($booking['flight_number']); ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Маршрут:</div>
                        <div class="value">
                            <?php echo htmlspecialchars($booking['departure_city']); ?> → 
                            <?php echo htmlspecialchars($booking['arrival_city']); ?>
                        </div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Дата и время:</div>
                        <div class="value">
                            <?php echo date('d.m.Y', strtotime($booking['departure_date'])); ?> 
                            в <?php echo date('H:i', strtotime($booking['departure_time'])); ?>
                        </div>
                    </div>
                </div>
                
                <div class="section">
                    <h3>Финансовая информация</h3>
                    <div class="detail-row">
                        <div class="label">Количество пассажиров:</div>
                        <div class="value"><?php echo $booking['passengers']; ?></div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Цена за билет:</div>
                        <div class="value"><?php echo number_format($booking['price'], 0, ',', ' '); ?> ₽</div>
                    </div>
                    <div class="detail-row">
                        <div class="label">Общая сумма:</div>
                        <div class="value"><?php echo number_format($booking['total_price'], 0, ',', ' '); ?> ₽</div>
                    </div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="adminPanel.php?tab=bookings" class="btn btn-primary">Назад к списку</a>
                <?php if ($booking['status'] == 'confirmed'): ?>
                    <a href="cancel_booking.php?id=<?php echo $booking['id']; ?>" 
                       class="btn btn-danger" 
                       onclick="return confirm('Вы уверены, что хотите отменить это бронирование?')">
                        Отменить бронирование
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'includes/footer_admin.php'; ?>
    
    <?php 
    $conn->close();
    ob_end_flush();
    ?>
</body>
</html>