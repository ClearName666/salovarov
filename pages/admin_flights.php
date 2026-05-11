<?php
include_once "connect.php";

// --- ОБРАБОТЧИК ДОБАВЛЕНИЯ РЕЙСА ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_flight'])) {
    
    $fn = trim($_POST['flight_number']);
    $airline = trim($_POST['airline']);
    $dep_city = trim($_POST['departure_city']);
    $arr_city = trim($_POST['arrival_city']);
    $dep_date = $_POST['departure_date'];
    $dep_time = $_POST['departure_time'];
    $price = floatval($_POST['price']);
    $seats = intval($_POST['available_seats']);
    
    // Значения по умолчанию для обязательных полей, которых нет в форме
    $dep_airport = $dep_city . ' Airport';
    $arr_airport = $arr_city . ' Airport';
    $arr_time = '12:00:00';
    $duration = '2ч 00м';
    
    // Устанавливаем правильное значение для ENUM поля class
    // Значение должно быть одним из: 'economy', 'business', 'first'
    // Если поле имеет значение по умолчанию, можно вообще его не указывать
    // или указать NULL, чтобы использовать DEFAULT
    
    // Вариант 1: Не указываем поле class вообще (используется DEFAULT)
    // Вариант 2: Явно указываем 'economy' как строку без дополнительных символов
    
    $class = 'economy'; // Простая строка без лишних символов
    
    // Форматируем время
    if (!strpos($dep_time, ':')) {
        $dep_time .= ':00';
    }
    
    // Для отладки: выводим значения
    error_log("Class value: " . $class);
    error_log("Class length: " . strlen($class));
    
    // Создаем SQL запрос БЕЗ поля class - пусть используется DEFAULT
    $sql = "INSERT INTO flights (
                airline, 
                flight_number, 
                departure_city, 
                departure_airport,
                arrival_city, 
                arrival_airport,
                departure_time, 
                arrival_time,
                duration, 
                price, 
                available_seats,
                departure_date
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        // Обратите внимание: теперь на один параметр меньше (без class)
        $stmt->bind_param(
            "sssssssssdss", 
            $airline,        // s - airline
            $fn,             // s - flight_number
            $dep_city,       // s - departure_city
            $dep_airport,    // s - departure_airport
            $arr_city,       // s - arrival_city
            $arr_airport,    // s - arrival_airport
            $dep_time,       // s - departure_time
            $arr_time,       // s - arrival_time
            $duration,       // s - duration
            $price,          // d - price (decimal)
            $seats,          // i - available_seats
            $dep_date        // s - departure_date
        );
        
        if ($stmt->execute()) {
            echo '<script>
                alert("Рейс успешно добавлен!");
                window.location.href = "admin.php?page=flights";
            </script>';
            exit();
        } else {
            $error_msg = addslashes($stmt->error);
            echo "<script>alert('Ошибка при добавлении рейса: $error_msg');</script>";
            echo "<!-- DEBUG: SQL Error: " . $stmt->error . " -->";
        }
        $stmt->close();
    } else {
        $error_msg = addslashes($conn->error);
        echo "<script>alert('Ошибка подготовки запроса: $error_msg');</script>";
        echo "<!-- DEBUG: Prepare Error: " . $conn->error . " -->";
    }
}

// Удаление рейса
if (isset($_GET['del'])) {
    $id = intval($_GET['del']);
    $conn->query("DELETE FROM flights WHERE id = $id");
    header("Location: admin.php?page=flights");
    exit();
}

$flights = $conn->query("SELECT * FROM flights ORDER BY id DESC");
?>

<div class="header-flex">
    <h1>Управление рейсами</h1>
    <button class="btn btn-primary" onclick="document.getElementById('flightModal').style.display='flex'">
        <i class="fas fa-plus"></i> Добавить новый рейс
    </button>
</div>

<!-- Модальное окно для добавления рейса -->
<div id="flightModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div class="card" style="width:500px; max-width:90%; position:relative;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Добавление нового рейса</h3>
            <button type="button" onclick="document.getElementById('flightModal').style.display='none'" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #666;">&times;</button>
        </div>
        <form method="POST" id="addFlightForm">
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <input type="text" name="flight_number" placeholder="№ Рейса (SU-123)" class="form-input" required>
                <input type="text" name="airline" placeholder="Авиакомпания" class="form-input" required>
                <input type="text" name="departure_city" placeholder="Откуда" class="form-input" required>
                <input type="text" name="arrival_city" placeholder="Куда" class="form-input" required>
                <input type="date" name="departure_date" class="form-input" required title="Дата вылета">
                <input type="time" name="departure_time" class="form-input" required title="Время вылета">
                <input type="number" name="price" placeholder="Цена (руб)" class="form-input" required min="0" step="100">
                <input type="number" name="available_seats" placeholder="Мест" class="form-input" required min="1">
            </div>
            <div style="margin-top:20px; display:flex; gap:10px;">
                <button type="submit" name="add_flight" class="btn btn-primary" style="flex:1">Сохранить рейс</button>
                <button type="button" class="btn" style="background:#ddd" onclick="document.getElementById('flightModal').style.display='none'">Отмена</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-res">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Рейс</th>
                    <th>Маршрут</th>
                    <th>Вылет</th>
                    <th>Прибытие</th>
                    <th>Длительность</th>
                    <th>Места</th>
                    <th>Цена</th>
                    <th>Класс</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($flights && $flights->num_rows > 0): ?>
                    <?php while($f = $flights->fetch_assoc()): ?>
                    <tr>
                        <td><?= $f['id'] ?></td>
                        <td>
                            <b><?= htmlspecialchars($f['flight_number']) ?></b><br>
                            <small><?= htmlspecialchars($f['airline']) ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($f['departure_city']) ?> → <?= htmlspecialchars($f['arrival_city']) ?><br>
                            <small style="font-size: 11px; color: #666;">
                                <?= htmlspecialchars($f['departure_airport']) ?> → <?= htmlspecialchars($f['arrival_airport']) ?>
                            </small>
                        </td>
                        <td>
                            <?= date('d.m.Y', strtotime($f['departure_date'])) ?><br>
                            <small><?= $f['departure_time'] ?></small>
                        </td>
                        <td><?= $f['arrival_time'] ?></td>
                        <td><?= $f['duration'] ?></td>
                        <td><?= $f['available_seats'] ?></td>
                        <td><?= number_format($f['price'], 0, '', ' ') ?> ₽</td>
                        <td>
                            <?php 
                            $classNames = [
                                'economy' => 'Эконом',
                                'business' => 'Бизнес',
                                'first' => 'Первый'
                            ];
                            echo $classNames[$f['class']] ?? $f['class'];
                            ?>
                        </td>
                        <td>
                            <a href="admin.php?page=flights&del=<?= $f['id'] ?>" class="btn" style="color:red" onclick="return confirm('Удалить рейс?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 40px;">
                            <p style="color: #666;">Нет доступных рейсов</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Простая валидация формы
document.getElementById('addFlightForm')?.addEventListener('submit', function(e) {
    const price = this.querySelector('input[name="price"]');
    const seats = this.querySelector('input[name="available_seats"]');
    const depDate = this.querySelector('input[name="departure_date"]');
    
    if (price && price.value <= 0) {
        e.preventDefault();
        alert('Цена должна быть больше 0');
        return false;
    }
    
    if (seats && seats.value <= 0) {
        e.preventDefault();
        alert('Количество мест должно быть больше 0');
        return false;
    }
    
    if (depDate) {
        const today = new Date().toISOString().split('T')[0];
        if (depDate.value < today) {
            e.preventDefault();
            alert('Дата вылета не может быть в прошлом');
            return false;
        }
    }
    
    return true;
});

// Устанавливаем минимальную дату (сегодня)
document.addEventListener('DOMContentLoaded', function() {
    const dateInput = document.querySelector('input[name="departure_date"]');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.min = today;
        
        // Если значение пустое, устанавливаем сегодняшнюю дату
        if (!dateInput.value) {
            dateInput.value = today;
        }
    }
});
</script>