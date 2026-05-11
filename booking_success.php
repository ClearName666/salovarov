<?php
session_start();
include_once "connect.php";

$booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : 0;
$reference = isset($_GET['reference']) ? $_GET['reference'] : '';

// Получаем данные бронирования
if ($booking_id > 0) {
    $sql = "SELECT b.*, f.*, u.fullname as user_name 
            FROM bookings b
            JOIN flights f ON b.flight_id = f.id
            JOIN users u ON b.user_id = u.id
            WHERE b.id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    $conn->close();
} else {
    $booking = null;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование успешно | Авиабилеты</title>
    <link rel="stylesheet" href="assets/css/booking_success.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideIn 0.6s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .success-icon {
            font-size: 80px;
            color: #4CAF50;
            margin-bottom: 20px;
            animation: bounce 1s infinite alternate;
        }

        @keyframes bounce {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }

        h1 {
            color: #333;
            font-size: 2.5rem;
            margin-bottom: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .success-message {
            color: #666;
            font-size: 1.2rem;
            text-align: center;
            margin-bottom: 40px;
        }

        .booking-details {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            border-left: 5px solid #4CAF50;
        }

        .details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .detail-item {
            padding: 15px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .detail-label {
            font-weight: 600;
            color: #555;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-value {
            color: #333;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .reference-number {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 15px 30px;
            border-radius: 50px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
            margin: 20px 0;
            display: inline-block;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 40px;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }

        .btn-secondary {
            background: #f8f9fa;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
        }

        .btn-secondary:hover {
            background: #e9ecef;
        }

        .timer {
            text-align: center;
            color: #666;
            font-size: 0.9rem;
            margin-top: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .contact-info {
            margin-top: 30px;
            padding: 20px;
            background: #f0f7ff;
            border-radius: 15px;
            border-left: 5px solid #2196F3;
        }

        .contact-info h3 {
            color: #2196F3;
            margin-bottom: 15px;
        }

        .email {
            color: #2196F3;
            font-weight: bold;
        }

        /* Адаптивность */
        @media (max-width: 768px) {
            .success-container {
                padding: 25px;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .details-grid {
                grid-template-columns: 1fr;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .success-container {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .success-icon {
                font-size: 60px;
            }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Бронирование успешно завершено!</h1>
        
        <?php if ($booking): ?>
            <div class="booking-info">
                <p><strong>Номер бронирования:</strong></p>
                <div class="booking-id"><?php echo $booking['booking_reference']; ?></div>
                
                <p><strong>Информация о рейсе:</strong></p>
                <div class="flight-summary">
                    <div class="route">
                        <?php echo $booking['departure_city']; ?> → <?php echo $booking['arrival_city']; ?>
                    </div>
                    <div class="details">
                        <div class="detail">
                            <span class="label">Авиакомпания:</span>
                            <span class="value"><?php echo $booking['airline']; ?></span>
                        </div>
                        <div class="detail">
                            <span class="label">Рейс:</span>
                            <span class="value"><?php echo $booking['flight_number']; ?></span>
                        </div>
                        <div class="detail">
                            <span class="label">Дата:</span>
                            <span class="value"><?php echo date('d.m.Y', strtotime($booking['departure_date'])); ?></span>
                        </div>
                        <div class="detail">
                            <span class="label">Время:</span>
                            <span class="value"><?php echo date('H:i', strtotime($booking['departure_time'])); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="price-info">
                    <p><strong>Сумма:</strong> <?php echo number_format($booking['total_price'], 0, ',', ' '); ?> ₽</p>
                </div>
                
                <p class="email-notice">
                    Информация о бронировании отправлена на email: <?php echo $booking['passenger_email']; ?>
                </p>
            </div>
        <?php else: ?>
            <p>Бронирование создано успешно!</p>
            <?php if ($reference): ?>
                <p>Номер бронирования:</p>
                <div class="booking-id"><?php echo $reference; ?></div>
            <?php endif; ?>
            <p>Информация о бронировании отправлена на ваш email.</p>
        <?php endif; ?>
        
        <div class="action-buttons">
            <a href="index.php" class="btn btn-primary">Вернуться на главную</a>
            <a href="bookings.php" class="btn btn-secondary">Мои бронирования</a>
        </div>
    </div>
</body>
</html>