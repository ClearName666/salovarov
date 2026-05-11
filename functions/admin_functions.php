
<?php
// Функции для админ-панели

/**
 * Проверяет, является ли пользователь администратором
 */
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] == 1;
}

/**
 * Редирект если пользователь не администратор
 */
function requireAdmin() {
    if (!isAdmin()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Получает статистику для дашборда
 */
function getDashboardStats($conn) {
    $stats = [];
    
    // Пользователи
    $result = $conn->query("SELECT COUNT(*) as count FROM users");
    $stats['users'] = $result->fetch_assoc()['count'];
    
    // Рейсы
    $result = $conn->query("SELECT COUNT(*) as count FROM flights");
    $stats['flights'] = $result->fetch_assoc()['count'];
    
    // Бронирования
    $result = $conn->query("SELECT COUNT(*) as count FROM bookings");
    $stats['bookings'] = $result->fetch_assoc()['count'];
    
    // Доход
    $result = $conn->query("SELECT SUM(amount) as total FROM payments WHERE status = 'completed'");
    $data = $result->fetch_assoc();
    $stats['revenue'] = $data['total'] ?: 0;
    
    return $stats;
}

/**
 * Получает последние бронирования
 */
function getRecentBookings($conn, $limit = 5) {
    $sql = "SELECT b.*, u.fullname, f.flight_number, f.departure_city, f.arrival_city 
           FROM bookings b 
           JOIN users u ON b.user_id = u.id 
           JOIN flights f ON b.flight_id = f.id 
           ORDER BY b.created_at DESC LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $bookings = [];
    while($row = $result->fetch_assoc()) {
        $bookings[] = $row;
    }
    
    $stmt->close();
    return $bookings;
}

/**
 * Получает последних пользователей
 */
function getRecentUsers($conn, $limit = 5) {
    $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $users = [];
    while($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
    
    $stmt->close();
    return $users;
}

/**
 * Форматирует статус бронирования
 */
function formatBookingStatus($status) {
    $statuses = [
        'pending' => ['text' => 'В ожидании', 'class' => 'status-pending'],
        'confirmed' => ['text' => 'Подтверждено', 'class' => 'status-confirmed'],
        'cancelled' => ['text' => 'Отменено', 'class' => 'status-cancelled'],
        'completed' => ['text' => 'Завершено', 'class' => 'status-completed']
    ];
    
    return isset($statuses[$status]) ? $statuses[$status] : ['text' => $status, 'class' => 'status-pending'];
}

/**
 * Генерирует CSV файл
 */
function generateCSV($data, $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Заголовки
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        
        // Данные
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
}

/**
 * Валидация данных формы
 */
function validateFormData($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';
        
        // Проверка на обязательность
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = $rule['message'] ?? "Поле обязательно для заполнения";
        }
        
        // Проверка на email
        if (isset($rule['email']) && $rule['email'] && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $errors[$field] = "Некорректный email адрес";
        }
        
        // Проверка на минимальную длину
        if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
            $errors[$field] = "Минимальная длина: " . $rule['min_length'] . " символов";
        }
        
        // Проверка на максимальную длину
        if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
            $errors[$field] = "Максимальная длина: " . $rule['max_length'] . " символов";
        }
    }
    
    return $errors;
}
