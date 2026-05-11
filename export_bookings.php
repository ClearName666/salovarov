<?php
// export_bookings.php (для администраторов)
ob_start();
session_start();
include_once "connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

// Получаем данные для экспорта
$sql = "SELECT b.booking_reference, u.fullname, u.email, u.phone, 
               f.airline, f.flight_number, f.departure_city, f.arrival_city,
               DATE_FORMAT(f.departure_date, '%d.%m.%Y') as departure_date,
               DATE_FORMAT(f.departure_time, '%H:%i') as departure_time,
               b.passengers, b.total_price, b.status, b.created_at
        FROM bookings b
        JOIN flights f ON b.flight_id = f.id
        JOIN users u ON b.user_id = u.id
        ORDER BY b.created_at DESC";
$result = $conn->query($sql);

// Устанавливаем заголовки для скачивания CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');

// Создаем файл CSV
$output = fopen('php://output', 'w');

// Заголовки CSV
fputcsv($output, [
    'Номер бронирования',
    'ФИО клиента',
    'Email',
    'Телефон',
    'Авиакомпания',
    'Рейс',
    'Откуда',
    'Куда',
    'Дата вылета',
    'Время вылета',
    'Пассажиров',
    'Сумма (₽)',
    'Статус',
    'Дата создания'
], ';');

// Данные
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['booking_reference'],
        $row['fullname'],
        $row['email'],
        $row['phone'],
        $row['airline'],
        $row['flight_number'],
        $row['departure_city'],
        $row['arrival_city'],
        $row['departure_date'],
        $row['departure_time'],
        $row['passengers'],
        $row['total_price'],
        $row['status'],
        $row['created_at']
    ], ';');
}

fclose($output);
$conn->close();
exit();
?>