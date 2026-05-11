<?php
include_once "connect.php";

// Смена статуса в один клик
if (isset($_GET['approve'])) {
    $id = intval($_GET['approve']);
    $conn->query("UPDATE bookings SET status = 'confirmed' WHERE id = $id");
}

// Сначала попробуем получить данные без сортировки по created_at
$sql = "SELECT b.*, f.flight_number, f.departure_city, f.arrival_city, u.fullname, u.phone 
        FROM bookings b 
        JOIN flights f ON b.flight_id = f.id 
        JOIN users u ON b.user_id = u.id";

// Проверяем существование столбца created_at
$check_column = $conn->query("SHOW COLUMNS FROM bookings LIKE 'created_at'");
if ($check_column && $check_column->num_rows > 0) {
    // Столбец существует, добавляем сортировку
    $sql .= " ORDER BY b.created_at DESC";
} else {
    // Ищем альтернативный столбец для сортировки
    $check_id = $conn->query("SHOW COLUMNS FROM bookings LIKE 'id'");
    if ($check_id && $check_id->num_rows > 0) {
        $sql .= " ORDER BY b.id DESC";
    }
}

$res = $conn->query($sql);

// Проверяем, выполнился ли запрос успешно
if ($res === false) {
    die("Ошибка выполнения запроса: " . $conn->error);
}
?>
<div class="header-flex">
    <h1>Журнал бронирований</h1>
    <a href="export_bookings.php" class="btn" style="background: white; border: 1px solid #ddd;"><i class="fas fa-file-excel"></i> Выгрузить CSV</a>
</div>

<div class="card">
    <div class="table-res">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Клиент / Контакты</th>
                    <th>Рейс</th>
                    <th>Стоимость</th>
                    <th>Статус</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($res->num_rows > 0): ?>
                    <?php while($row = $res->fetch_assoc()): ?>
                    <tr>
                        <td>#<?= htmlspecialchars($row['id']) ?></td>
                        <td>
                            <div style="font-weight: 600;"><?= htmlspecialchars($row['fullname']) ?></div>
                            <small><?= htmlspecialchars($row['phone']) ?></small>
                        </td>
                        <td>
                            <b><?= htmlspecialchars($row['flight_number']) ?></b><br>
                            <small><?= htmlspecialchars($row['departure_city']) ?> → <?= htmlspecialchars($row['arrival_city']) ?></small>
                        </td>
                        <td><span style="color: #059669; font-weight: 700;"><?= number_format($row['total_price'], 0, '.', ' ') ?> ₽</span></td>
                        <td>
                            <?php 
                            $st = $row['status'];
                            $class = ($st == 'confirmed') ? 'b-confirmed' : (($st == 'pending') ? 'b-pending' : 'b-cancelled');
                            $text = ($st == 'confirmed') ? 'Подтвержден' : (($st == 'pending') ? 'Ожидание' : 'Отменен');
                            ?>
                            <span class="badge <?= $class ?>"><?= $text ?></span>
                        </td>
                        <td>
                            <?php if($st == 'pending'): ?>
                                <a href="admin.php?page=bookings&approve=<?= $row['id'] ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">Одобрить</a>
                            <?php else: ?>
                                <i class="fas fa-check-double" style="color: var(--success);"></i>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">Нет данных о бронированиях</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>