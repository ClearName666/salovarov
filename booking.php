<?php
// booking.php

// Подключение к базе данных
require_once 'connect.php';

// Проверка авторизации пользователя
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Обработка формы бронирования
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получение и валидация данных из формы
    $flight_id = isset($_POST['flight_id']) ? intval($_POST['flight_id']) : 0;
    $passengers_count = isset($_POST['passengers']) ? intval($_POST['passengers']) : 1;
    $passenger_name = isset($_POST['passenger_name']) ? trim($_POST['passenger_name']) : '';
    $passenger_email = isset($_POST['passenger_email']) ? trim($_POST['passenger_email']) : '';
    $passenger_phone = isset($_POST['passenger_phone']) ? trim($_POST['passenger_phone']) : '';
    
    // Дополнительная информация о пассажирах (если нужно)
    $passengers_details = [];
    for ($i = 1; $i <= $passengers_count; $i++) {
        if (isset($_POST["passenger_{$i}_name"])) {
            $passengers_details[] = [
                'name' => $_POST["passenger_{$i}_name"],
                'birth_date' => $_POST["passenger_{$i}_birth_date"] ?? null,
                'document' => $_POST["passenger_{$i}_document"] ?? null
            ];
        }
    }
    
    // Валидация данных
    if ($flight_id <= 0) {
        $error = 'Неверный ID рейса';
    } elseif ($passengers_count <= 0 || $passengers_count > 10) {
        $error = 'Неверное количество пассажиров (1-10)';
    } elseif (empty($passenger_name) || empty($passenger_email)) {
        $error = 'Заполните обязательные поля';
    } elseif (!filter_var($passenger_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Неверный формат email';
    } else {
        // Начало транзакции
        $conn->begin_transaction();
        
        try {
            // 1. Получение информации о рейсе
            $stmt_flight = $conn->prepare("SELECT * FROM flights WHERE id = ? AND available_seats >= ?");
            $stmt_flight->bind_param("ii", $flight_id, $passengers_count);
            $stmt_flight->execute();
            $result_flight = $stmt_flight->get_result();
            $flight = $result_flight->fetch_assoc();
            
            if (!$flight) {
                throw new Exception('Рейс не найден или недостаточно свободных мест');
            }
            
            // 2. Расчет общей стоимости
            $price_per_passenger = $flight['price'];
            $total_price = $price_per_passenger * $passengers_count;
            
            // 3. Генерация уникального номера бронирования
            $booking_ref = 'BK' . date('YmdHis') . rand(1000, 9999);
            
            // 4. Вставка данных в таблицу bookings
            // Сначала проверяем структуру таблицы bookings
            $check_table = $conn->query("SHOW TABLES LIKE 'bookings'");
            if ($check_table->num_rows == 0) {
                throw new Exception("Таблица bookings не существует");
            }
            
            // Проверяем существование столбцов
            $check_columns = $conn->query("DESCRIBE bookings");
            $columns = [];
            while ($col = $check_columns->fetch_assoc()) {
                $columns[] = $col['Field'];
            }
            
            // Проверяем наличие всех необходимых столбцов
            $required_columns = ['booking_reference', 'user_id', 'flight_id', 'passengers', 'total_price', 'status', 'payment_status', 'passenger_name', 'passenger_email', 'passenger_phone'];
            $missing_columns = array_diff($required_columns, $columns);
            
            if (!empty($missing_columns)) {
                throw new Exception("В таблице bookings отсутствуют столбцы: " . implode(', ', $missing_columns));
            }
            
            $sql_booking = "INSERT INTO bookings 
                (booking_reference, user_id, flight_id, passengers, total_price, 
                 status, payment_status, passenger_name, passenger_email, passenger_phone) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_booking = $conn->prepare($sql_booking);
            
            if ($stmt_booking === false) {
                throw new Exception("Ошибка подготовки запроса: " . $conn->error . ". SQL: " . $sql_booking);
            }
            
            $status = 'confirmed';
            $payment_status = 'unpaid';
            
            // Проверяем типы данных
            $stmt_booking->bind_param(
                "siiidsssss",
                $booking_ref,
                $user_id,
                $flight_id,
                $passengers_count,
                $total_price,
                $status,
                $payment_status,
                $passenger_name,
                $passenger_email,
                $passenger_phone
            );
            
            if (!$stmt_booking->execute()) {
                throw new Exception("Ошибка при создании бронирования: " . $stmt_booking->error);
            }
            
            $booking_id = $stmt_booking->insert_id;
            
            // 5. Добавление деталей пассажиров (если есть)
            if (!empty($passengers_details)) {
                // Проверяем существование таблицы passenger_details
                $check_passenger_table = $conn->query("SHOW TABLES LIKE 'passenger_details'");
                if ($check_passenger_table->num_rows > 0) {
                    foreach ($passengers_details as $passenger) {
                        $stmt_passenger = $conn->prepare("INSERT INTO passenger_details 
                            (booking_id, full_name, birth_date, document_number) 
                            VALUES (?, ?, ?, ?)");
                            
                        if ($stmt_passenger === false) {
                            throw new Exception("Ошибка подготовки запроса пассажира: " . $conn->error);
                        }
                            
                        $stmt_passenger->bind_param(
                            "isss",
                            $booking_id,
                            $passenger['name'],
                            $passenger['birth_date'],
                            $passenger['document']
                        );
                        
                        if (!$stmt_passenger->execute()) {
                            throw new Exception("Ошибка при добавлении данных пассажира: " . $stmt_passenger->error);
                        }
                        $stmt_passenger->close();
                    }
                }
            }
            
            // 6. Обновление количества свободных мест
            $new_available_seats = $flight['available_seats'] - $passengers_count;
            $stmt_update = $conn->prepare("UPDATE flights SET available_seats = ? WHERE id = ?");
            
            if ($stmt_update === false) {
                throw new Exception("Ошибка подготовки запроса обновления: " . $conn->error);
            }
            
            $stmt_update->bind_param("ii", $new_available_seats, $flight_id);
            
            if (!$stmt_update->execute()) {
                throw new Exception("Ошибка при обновлении количества мест: " . $stmt_update->error);
            }
            
            // Подтверждение транзакции
            $conn->commit();
            
            $success = "Бронирование успешно создано! Номер бронирования: {$booking_ref}";
            
            // Очистка формы
            $_POST = [];
            
        } catch (Exception $e) {
            // Откат транзакции при ошибке
            $conn->rollback();
            $error = 'Ошибка при создании бронирования: ' . $e->getMessage();
        } finally {
            // Закрываем все запросы только если они были успешно созданы
            if (isset($stmt_flight) && $stmt_flight !== false) {
                $stmt_flight->close();
            }
            if (isset($stmt_booking) && $stmt_booking !== false) {
                $stmt_booking->close();
            }
            if (isset($stmt_update) && $stmt_update !== false) {
                $stmt_update->close();
            }
        }
    }
}

// Получение информации о рейсе для отображения
$flight_info = [];
if (isset($_GET['flight_id'])) {
    $flight_id = intval($_GET['flight_id']);
    
    $query = "
        SELECT f.*, 
               a1.city as dep_city, a1.name as dep_airport_name,
               a2.city as arr_city, a2.name as arr_airport_name
        FROM flights f
        LEFT JOIN airports a1 ON f.departure_airport = a1.code
        LEFT JOIN airports a2 ON f.arrival_airport = a2.code
        WHERE f.id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $flight_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $flight_info = $result->fetch_assoc();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бронирование рейса</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .container { max-width: 800px; margin: 0 auto; padding: 20px; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
        .flight-info { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .btn { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #0056b3; }
        .debug-info { background: #e9ecef; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Бронирование рейса</h1>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php 
        // Для отладки: показать информацию о таблице bookings
        if (isset($_GET['debug'])): 
            $tables = $conn->query("SHOW TABLES");
            echo '<div class="debug-info">';
            echo '<h4>Отладка - Таблицы в базе данных:</h4>';
            while ($table = $tables->fetch_array()) {
                echo $table[0] . '<br>';
                
                // Показать структуру таблицы bookings если она существует
                if ($table[0] == 'bookings') {
                    echo '<br><strong>Структура таблицы bookings:</strong><br>';
                    $columns = $conn->query("DESCRIBE bookings");
                    while ($col = $columns->fetch_assoc()) {
                        echo $col['Field'] . ' - ' . $col['Type'] . '<br>';
                    }
                }
            }
            echo '</div>';
        endif; 
        ?>
        
        <?php if ($flight_info): ?>
            <div class="flight-info">
                <h3>Информация о рейсе</h3>
                <p><strong>Рейс:</strong> <?php echo htmlspecialchars($flight_info['airline'] . ' ' . $flight_info['flight_number']); ?></p>
                <p><strong>Маршрут:</strong> <?php echo htmlspecialchars($flight_info['dep_city'] . ' → ' . $flight_info['arr_city']); ?></p>
                <p><strong>Дата вылета:</strong> <?php echo htmlspecialchars($flight_info['departure_date']); ?></p>
                <p><strong>Время:</strong> <?php echo htmlspecialchars($flight_info['departure_time'] . ' - ' . $flight_info['arrival_time']); ?></p>
                <p><strong>Цена за пассажира:</strong> <?php echo number_format($flight_info['price'], 2, '.', ' '); ?> руб.</p>
                <p><strong>Свободных мест:</strong> <?php echo htmlspecialchars($flight_info['available_seats']); ?></p>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="hidden" name="flight_id" value="<?php echo isset($_GET['flight_id']) ? intval($_GET['flight_id']) : ''; ?>">
            
            <div class="form-group">
                <label for="passengers">Количество пассажиров:</label>
                <select name="passengers" id="passengers" required>
                    <?php for ($i = 1; $i <= 10; $i++): ?>
                        <option value="<?php echo $i; ?>" <?php echo (isset($_POST['passengers']) && $_POST['passengers'] == $i) ? 'selected' : ''; ?>></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <h3>Контактная информация</h3>
            
            <div class="form-group">
                <label for="passenger_name">ФИО контактного лица:*</label>
                <input type="text" id="passenger_name" name="passenger_name" 
                       value="<?php echo isset($_POST['passenger_name']) ? htmlspecialchars($_POST['passenger_name']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="passenger_email">Email:*</label>
                <input type="email" id="passenger_email" name="passenger_email" 
                       value="<?php echo isset($_POST['passenger_email']) ? htmlspecialchars($_POST['passenger_email']) : ''; ?>" 
                       required>
            </div>
            
            <div class="form-group">
                <label for="passenger_phone">Телефон:*</label>
                <input type="tel" id="passenger_phone" name="passenger_phone" 
                       value="<?php echo isset($_POST['passenger_phone']) ? htmlspecialchars($_POST['passenger_phone']) : ''; ?>" 
                       required>
            </div>
            
            <div id="additional-passengers" style="display: none;">
                <h3>Дополнительные пассажиры</h3>
                <!-- Динамически заполняется JavaScript -->
            </div>
            
            <button type="submit" class="btn">Забронировать</button>
            <a href="search.php" class="btn" style="background: #6c757d; margin-left: 10px;">Назад к рейсам</a>
            <a href="?flight_id=<?php echo isset($_GET['flight_id']) ? $_GET['flight_id'] : '' ?>&debug=1" class="btn" style="background: #17a2b8; margin-left: 10px;">Отладка</a>
        </form>
    </div>
    
    <script>
        // Динамическое добавление полей для дополнительных пассажиров
        document.getElementById('passengers').addEventListener('change', function() {
            const passengersCount = parseInt(this.value);
            const container = document.getElementById('additional-passengers');
            
            if (passengersCount > 1) {
                container.style.display = 'block';
                let html = '';
                
                for (let i = 2; i <= passengersCount; i++) {
                    html += `
                        <div class="form-group">
                            <h4>Пассажир ${i}</h4>
                            <label for="passenger_${i}_name">ФИО:</label>
                            <input type="text" id="passenger_${i}_name" name="passenger_${i}_name" required>
                            
                            <label for="passenger_${i}_birth_date">Дата рождения:</label>
                            <input type="date" id="passenger_${i}_birth_date" name="passenger_${i}_birth_date">
                            
                            <label for="passenger_${i}_document">Номер документа:</label>
                            <input type="text" id="passenger_${i}_document" name="passenger_${i}_document">
                        </div>
                    `;
                }
                
                container.innerHTML = html;
            } else {
                container.style.display = 'none';
                container.innerHTML = '';
            }
        });
        
        // Триггер события при загрузке страницы
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('passengers').dispatchEvent(new Event('change'));
        });
    </script>
</body>
</html>