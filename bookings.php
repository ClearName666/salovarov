<?php
ob_start();
session_start();
include_once "connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

// Подключаем функцию для получения бронирований
function getUserBookings($conn, $user_id) {
    $sql = "
        SELECT b.*, 
               f.airline, f.flight_number, 
               f.departure_city, f.departure_airport, 
               f.arrival_city, f.arrival_airport,
               f.departure_time as dep_time, 
               f.arrival_time as arr_time,
               f.duration, f.class, f.departure_date
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while ($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    $stmt->close();
    return $bookings;
}

$bookings = getUserBookings($conn, $user_id);

// Обработка отмены бронирования через POST
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $booking_id = intval($_POST['cancel_booking']);
    
    // Проверяем, существует ли бронирование у текущего пользователя
    $check_sql = "SELECT b.*, f.available_seats FROM bookings b 
                  JOIN flights f ON b.flight_id = f.id 
                  WHERE b.id = ? AND b.user_id = ? AND b.status = 'confirmed'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $booking_id, $user_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows === 1) {
        $booking_data = $check_result->fetch_assoc();
        
        // Начинаем транзакцию
        $conn->begin_transaction();
        
        try {
            // 1. Меняем статус бронирования на 'cancelled'
            $update_booking = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
            $stmt1 = $conn->prepare($update_booking);
            $stmt1->bind_param("i", $booking_id);
            $stmt1->execute();
            
            // 2. Возвращаем места на рейс
            $return_seats = "UPDATE flights SET available_seats = available_seats + ? WHERE id = ?";
            $stmt2 = $conn->prepare($return_seats);
            $stmt2->bind_param("ii", $booking_data['passengers'], $booking_data['flight_id']);
            $stmt2->execute();
            
            // Подтверждаем транзакцию
            $conn->commit();
            
            $message = '<div class="alert success">Бронирование успешно отменено</div>';
            
            // Обновляем список бронирований
            $bookings = getUserBookings($conn, $user_id);
            
            // Закрываем запросы
            $stmt1->close();
            $stmt2->close();
            
        } catch (Exception $e) {
            // Откатываем транзакцию при ошибке
            $conn->rollback();
            $error = '<div class="alert error">Ошибка при отмене бронирования</div>';
        }
    } else {
        $error = '<div class="alert error">Бронирование не найдено или уже отменено</div>';
    }
    
    $check_stmt->close();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои бронирования | Авиабилеты</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .no-bookings { text-align: center; padding: 40px; background: #f8f9fa; border-radius: 8px; }
        .booking-card { 
            background: white; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            padding: 20px; 
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .booking-header { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 15px;
            margin-bottom: 15px;
        }
        .booking-status { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 4px; 
            font-size: 14px;
            font-weight: bold;
        }
        .booking-status.confirmed { background-color: #d4edda; color: #155724; }
        .booking-status.cancelled { background-color: #f8d7da; color: #721c24; }
        .booking-status.pending { background-color: #fff3cd; color: #856404; }
        .flight-route { 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            margin: 20px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .departure, .arrival { text-align: center; }
        .city { font-size: 18px; font-weight: bold; }
        .airport { color: #666; margin: 5px 0; }
        .time { font-size: 16px; font-weight: bold; }
        .flight-duration { 
            text-align: center; 
            border-left: 2px solid #ddd; 
            border-right: 2px solid #ddd;
            padding: 0 30px;
        }
        .duration { font-size: 14px; color: #666; }
        .booking-details { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); 
            gap: 15px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .btn { 
            padding: 8px 16px; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            text-decoration: none;
            display: inline-block;
        }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-danger:hover { background-color: #c82333; }
        .booking-actions form { display: inline; }
        .passenger-info { 
            margin-top: 15px; 
            padding: 15px; 
            background: #f8f9fa; 
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="bookings-container">
            <h1>Мои бронирования</h1>
            
            <?php 
            if ($message) echo $message;
            if ($error) echo $error;
            ?>
            
            <?php if (empty($bookings)): ?>
                <div class="no-bookings">
                    <h3>У вас нет бронирований</h3>
                    <p>Найдите и забронируйте рейсы в разделе поиска</p>
                    <a href="search.php" class="btn" style="background: #007bff; color: white;">Найти рейсы</a>
                </div>
            <?php else: ?>
                <div class="bookings-list">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-info">
                                    <div class="booking-ref">
                                        <strong>Бронирование №:</strong> <?php echo htmlspecialchars($booking['booking_reference']); ?>
                                    </div>
                                    <div class="booking-date">
                                        <strong>Дата бронирования:</strong> 
                                        <?php echo date('d.m.Y H:i', strtotime($booking['booking_date'])); ?>
                                    </div>
                                    <div class="booking-status <?php echo $booking['status']; ?>">
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
                                
                                <?php if ($booking['status'] == 'confirmed'): ?>
                                    <div class="booking-actions">
                                        <form method="POST" action="" onsubmit="return confirm('Вы уверены, что хотите отменить это бронирование?');">
                                            <input type="hidden" name="cancel_booking" value="<?php echo $booking['id']; ?>">
                                            <button type="submit" class="btn btn-danger">Отменить бронирование</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flight-info">
                                <div class="flight-route">
                                    <div class="departure">
                                        <div class="city"><?php echo htmlspecialchars($booking['departure_city']); ?></div>
                                        <div class="airport"><?php echo htmlspecialchars($booking['departure_airport']); ?></div>
                                        <div class="time"><?php echo date('H:i', strtotime($booking['dep_time'])); ?></div>
                                        <div class="date"><?php echo date('d.m.Y', strtotime($booking['departure_date'])); ?></div>
                                    </div>
                                    
                                    <div class="flight-duration">
                                        <div class="duration"><?php echo htmlspecialchars($booking['duration']); ?></div>
                                        <div class="airline"><?php echo htmlspecialchars($booking['airline']); ?></div>
                                        <div class="flight-number"><?php echo htmlspecialchars($booking['flight_number']); ?></div>
                                    </div>
                                    
                                    <div class="arrival">
                                        <div class="city"><?php echo htmlspecialchars($booking['arrival_city']); ?></div>
                                        <div class="airport"><?php echo htmlspecialchars($booking['arrival_airport']); ?></div>
                                        <div class="time"><?php echo date('H:i', strtotime($booking['arr_time'])); ?></div>
                                        <?php 
                                        $arrival_date = date('d.m.Y', strtotime($booking['departure_date'] . ' + ' . $booking['duration']));
                                        echo '<div class="date">' . $arrival_date . '</div>';
                                        ?>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    <div class="detail">
                                        <strong>Пассажиров:</strong> <?php echo $booking['passengers']; ?>
                                    </div>
                                    <div class="detail">
                                        <strong>Класс:</strong> 
                                        <?php 
                                        $class_names = [
                                            'economy' => 'Эконом',
                                            'business' => 'Бизнес',
                                            'first' => 'Первый'
                                        ];
                                        echo $class_names[$booking['class']] ?? $booking['class'];
                                        ?>
                                    </div>
                                    <div class="detail">
                                        <strong>Сумма:</strong> 
                                        <?php echo number_format($booking['total_price'], 0, ',', ' '); ?> ₽
                                    </div>
                                    <div class="detail">
                                        <strong>Статус оплаты:</strong> 
                                        <?php echo $booking['payment_status'] == 'paid' ? 'Оплачено' : 'Не оплачено'; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($booking['passenger_name'])): ?>
                            <div class="passenger-info">
                                <strong>Контактная информация:</strong><br>
                                <?php echo htmlspecialchars($booking['passenger_name']); ?><br>
                                <?php echo !empty($booking['passenger_email']) ? htmlspecialchars($booking['passenger_email']) : ''; ?><br>
                                <?php echo !empty($booking['passenger_phone']) ? htmlspecialchars($booking['passenger_phone']) : ''; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <?php 
    $conn->close();
    ob_end_flush();
    ?>
</body>
</html>