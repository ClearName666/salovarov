<?php
// 1. Подключаем БД (выходим из папки functions в корень сайта)
require_once "../connect.php";

// 2. Проверяем, что форма была отправлена методом POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Получаем данные и экранируем их через $conn (как в connect.php)
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    // 4. Формируем SQL-запрос (убедитесь, что таблица contacts создана в БД salovarov)
    $sql = "INSERT INTO `contacts` (`name`, `email`, `subject`, `message`) 
            VALUES ('$name', '$email', '$subject', '$message')";

    // 5. Выполняем запрос
    if (mysqli_query($conn, $sql)) {
        // Если успешно — возвращаемся на страницу контактов с флагом успеха
        header("Location: ../contact.php?status=success");
    } else {
        // Если ошибка — выводим её для отладки
        die("Ошибка записи в БД: " . mysqli_error($conn));
    }
} else {
    // Если кто-то зашел на скрипт напрямую — отправляем обратно
    header("Location: ../contact.php");
}
?>