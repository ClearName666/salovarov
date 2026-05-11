[file name]: export.php
[file content begin]
<?php
session_start();
include_once "connect.php";

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] != 1) {
    header('Location: login.php');
    exit();
}

$type = $_GET['type'] ?? '';

switch($type) {
    case 'flights':
        exportFlights($conn);
        break;
    case 'bookings':
        exportBookings($conn);
        break;
    case 'users':
        exportUsers($conn);
        break;
    default:
        header('Location: admin.php');
        exit();
}

function exportFlights($conn) {
    $sql = "SELECT * FROM flights ORDER BY departure_time DESC";
    $result = $conn->query($sql);
    
    $filename = "flights_export_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Заголовки
    fputcsv($output, [
        'ID', 'Номер рейса', 'Авиакомпания', 'Город вылета', 'Город прилета',
        'Время вылета', 'Время прилета', 'Цена', 'Доступные места', 'Класс', 'Статус'
    ]);
    
    // Данные
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['flight_number'],
            $row['airline'],
            $row['departure_city'],
            $row['arrival_city'],
            $row['departure_time'],
            $row['arrival_time'],
            $row['price'],
            $row['available_seats'],
            $row['class'],
            $row['status']
        ]);
    }
    
    fclose($output);
}

function exportBookings($conn) {
    $sql = "SELECT b.*, u.fullname, u.email, f.flight_number 
           FROM bookings b 
           JOIN users u ON b.user_id = u.id 
           JOIN flights f ON b.flight_id = f.id 
           ORDER BY b.created_at DESC";
    $result = $conn->query($sql);
    
    $filename = "bookings_export_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Заголовки
    fputcsv($output, [
        'ID', 'Номер бронирования', 'Пассажир', 'Email', 'Номер рейса',
        'Количество пассажиров', 'Общая сумма', 'Статус', 'Дата создания'
    ]);
    
    // Данные
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['booking_number'],
            $row['fullname'],
            $row['email'],
            $row['flight_number'],
            $row['passengers_count'],
            $row['total_amount'],
            $row['status'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
}

function exportUsers($conn) {
    $sql = "SELECT * FROM users ORDER BY created_at DESC";
    $result = $conn->query($sql);
    
    $filename = "users_export_" . date('Y-m-d') . ".csv";
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=' . $filename);
    
    $output = fopen('php://output', 'w');
    
    // Заголовки
    fputcsv($output, [
        'ID', 'ФИО', 'Email', 'Телефон', 'Роль', 'Статус', 'Дата регистрации', 'Последний вход'
    ]);
    
    // Данные
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['fullname'],
            $row['email'],
            $row['phone'] ?? '',
            $row['role'] == 1 ? 'Админ' : 'Пользователь',
            $row['status'] == 1 ? 'Активен' : 'Заблокирован',
            $row['created_at'],
            $row['last_login']
        ]);
    }
    
    fclose($output);
}

$conn->close();
[file content end]