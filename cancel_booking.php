<?php
// cancel_booking.php
ob_start();
session_start();
include_once "connect.php";

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] != 1) {
    header("Location: login.php");
    exit();
}

$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($booking_id > 0) {
    // Отменяем бронирование
    $update_sql = "UPDATE bookings SET status = 'cancelled' WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $booking_id);
    
    if ($stmt->execute()) {
        // Возвращаем места
        $booking_sql = "SELECT flight_id, passengers FROM bookings WHERE id = ?";
        $booking_stmt = $conn->prepare($booking_sql);
        $booking_stmt->bind_param("i", $booking_id);
        $booking_stmt->execute();
        $booking_result = $booking_stmt->get_result();
        $booking = $booking_result->fetch_assoc();
        
        if ($booking) {
            $return_sql = "UPDATE flights SET available_seats = available_seats + ? WHERE id = ?";
            $return_stmt = $conn->prepare($return_sql);
            $return_stmt->bind_param("ii", $booking['passengers'], $booking['flight_id']);
            $return_stmt->execute();
        }
        
        header("Location: adminPanel.php?tab=bookings&message=Бронирование+отменено+успешно");
    } else {
        header("Location: adminPanel.php?tab=bookings&error=Ошибка+отмены+бронирования");
    }
    exit();
}

header("Location: adminPanel.php?tab=bookings");
?>