<?php
// functions/booking_simple.php

/**
 * Упрощенная версия создания бронирования для текущей структуры БД
 */
function createBookingSimple($conn, $user_id, $flight_id, $passenger_data, $passengers_count) {
    error_log("createBookingSimple called: user_id=$user_id, flight_id=$flight_id, passengers=$passengers_count");
    
    // Получаем информацию о рейсе
    $flight_sql = "SELECT * FROM flights WHERE id = ?";
    $flight_stmt = $conn->prepare($flight_sql);
    
    if (!$flight_stmt) {
        error_log("Ошибка подготовки запроса рейса: " . $conn->error);
        return ['success' => false, 'error' => 'Ошибка подготовки запроса рейса'];
    }
    
    $flight_stmt->bind_param("i", $flight_id);
    $flight_stmt->execute();
    $flight_result = $flight_stmt->get_result();
    $flight = $flight_result->fetch_assoc();
    $flight_stmt->close();
    
    if (!$flight) {
        return ['success' => false, 'error' => 'Рейс не найден'];
    }
    
    // Проверяем доступность мест
    if ($flight['available_seats'] < $passengers_count) {
        return ['success' => false, 'error' => 'Недостаточно мест на рейсе. Доступно: ' . $flight['available_seats']];
    }
    
    // Генерируем номер бронирования
    $booking_reference = 'BK' . date('Ymd') . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
    $total_price = $flight['price'] * $passengers_count;
    
    error_log("Создание бронирования: референс=$booking_reference, цена=$total_price");
    
    // Простая вставка без транзакции для отладки
    try {
        // Проверяем существующие столбцы
        $result = $conn->query("SHOW COLUMNS FROM bookings");
        $columns = [];
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
        
        error_log("Столбцы таблицы bookings: " . implode(', ', $columns));
        
        // Определяем SQL запрос в зависимости от структуры таблицы
        if (in_array('passenger_name', $columns) && 
            in_array('passenger_email', $columns) && 
            in_array('passenger_phone', $columns)) {
            
            $booking_sql = "INSERT INTO bookings (user_id, flight_id, passengers, total_price, booking_reference, 
                            passenger_name, passenger_email, passenger_phone, booking_date, status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'confirmed')";
            
            $booking_stmt = $conn->prepare($booking_sql);
            if (!$booking_stmt) {
                throw new Exception("Ошибка подготовки запроса (с пассажирами): " . $conn->error);
            }
            
            $booking_stmt->bind_param(
                "iiidssss", 
                $user_id, 
                $flight_id, 
                $passengers_count, 
                $total_price, 
                $booking_reference,
                $passenger_data['fullname'],
                $passenger_data['email'],
                $passenger_data['phone']
            );
            
        } else {
            $booking_sql = "INSERT INTO bookings (user_id, flight_id, passengers, total_price, booking_reference, status) 
                            VALUES (?, ?, ?, ?, ?, 'confirmed')";
            
            error_log("SQL запрос (без пассажиров): " . $booking_sql);
            
            $booking_stmt = $conn->prepare($booking_sql);
            if (!$booking_stmt) {
                throw new Exception("Ошибка подготовки запроса (без пассажиров): " . $conn->error);
            }
            
            $booking_stmt->bind_param(
                "iiids", 
                $user_id, 
                $flight_id, 
                $passengers_count, 
                $total_price, 
                $booking_reference
            );
        }
        
        if (!$booking_stmt->execute()) {
            throw new Exception("Ошибка выполнения запроса: " . $booking_stmt->error);
        }
        
        $booking_id = $conn->insert_id;
        $booking_stmt->close();
        
        error_log("Бронирование создано, ID: $booking_id");
        
        // Обновляем количество доступных мест
        $new_seats = $flight['available_seats'] - $passengers_count;
        $update_sql = "UPDATE flights SET available_seats = ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_sql);
        
        if ($update_stmt) {
            $update_stmt->bind_param("ii", $new_seats, $flight_id);
            $update_stmt->execute();
            $update_stmt->close();
        }
        
        return [
            'success' => true,
            'booking_id' => $booking_id,
            'booking_reference' => $booking_reference,
            'total_price' => $total_price
        ];
        
    } catch (Exception $e) {
        error_log("Исключение при создании бронирования: " . $e->getMessage());
        return ['success' => false, 'error' => $e->getMessage()];
    }
}

/**
 * Упрощенное получение бронирований пользователя
 */
function getUserBookingsSimple($conn, $user_id) {
    $sql = "SELECT b.*, f.*, b.booking_date,
                   TIME(f.departure_time) as dep_time,
                   TIME(f.arrival_time) as arr_time
            FROM bookings b
            JOIN flights f ON b.flight_id = f.id
            WHERE b.user_id = ?
            ORDER BY b.booking_date DESC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Ошибка подготовки запроса бронирований: " . $conn->error);
        return [];
    }
    
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
?>