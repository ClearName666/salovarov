<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Подключаем базу данных
include_once "connect.php";

// Обработка поиска рейсов
$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['from']) && isset($_GET['to'])) {
    $search_performed = true;
    
    $from = trim($_GET['from']);
    $to = trim($_GET['to']);
    $departure_date = isset($_GET['departure']) ? $_GET['departure'] : date('Y-m-d');
    $return_date = isset($_GET['return']) ? $_GET['return'] : '';
    $passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
    $class = isset($_GET['class']) ? $_GET['class'] : 'economy';
    
    // Поиск рейсов туда
    $sql = "SELECT * FROM flights 
            WHERE departure_city LIKE ? 
            AND arrival_city LIKE ? 
            AND departure_date = ? 
            AND available_seats >= ? 
            ORDER BY price ASC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $from_like = "%$from%";
        $to_like = "%$to%";
        $stmt->bind_param("sssi", $from_like, $to_like, $departure_date, $passengers);
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            while ($flight = $result->fetch_assoc()) {
                $search_results[] = $flight;
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авиабилеты | Поиск и бронирование билетов</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="main.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero">
        <div class="container">
            <h1>Найдите лучшие авиабилеты</h1>
            <p>Сравните цены от сотен авиакомпаний и турагентств, чтобы найти идеальное путешествие по лучшей цене</p>
        </div>
    </section>
    
    <div class="container">
        <div class="search-container">
            <div class="search-title">
                <h2>Поиск авиабилетов</h2>
                <p>Найдите самые выгодные предложения на рейсы по всему миру</p>
            </div>
            
            <form class="search-form" action="" method="GET">
                <div class="form-group">
                    <label for="from" class="form-label">Откуда</label>
                    <input type="text" class="form-control" id="from" name="from" required 
                           placeholder="Город вылета" 
                           value="<?= isset($_GET['from']) ? htmlspecialchars($_GET['from']) : '' ?>"
                           list="cities-from">
                    <datalist id="cities-from">
                        <?php
                        // Получаем уникальные города вылета из базы данных
                        $cities_query = $conn->query("SELECT DISTINCT departure_city FROM flights ORDER BY departure_city");
                        while($city = $cities_query->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($city['departure_city']) . '">';
                        }
                        ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="to" class="form-label">Куда</label>
                    <input type="text" class="form-control" id="to" name="to" required 
                           placeholder="Город прилета"
                           value="<?= isset($_GET['to']) ? htmlspecialchars($_GET['to']) : '' ?>"
                           list="cities-to">
                    <datalist id="cities-to">
                        <?php
                        // Получаем уникальные города прибытия из базы данных
                        $cities_query = $conn->query("SELECT DISTINCT arrival_city FROM flights ORDER BY arrival_city");
                        while($city = $cities_query->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($city['arrival_city']) . '">';
                        }
                        ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="departure" class="form-label">Туда</label>
                    <input type="date" class="form-control" id="departure" name="departure" required
                           value="<?= isset($_GET['departure']) ? $_GET['departure'] : date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="return" class="form-label">Обратно (опционально)</label>
                    <input type="date" class="form-control" id="return" name="return"
                           value="<?= isset($_GET['return']) ? $_GET['return'] : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="passengers" class="form-label">Пассажиры</label>
                    <select class="form-control" id="passengers" name="passengers">
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['passengers']) && $_GET['passengers'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?> пассажир<?= $i > 1 ? 'а' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="class" class="form-label">Класс</label>
                    <select class="form-control" id="class" name="class">
                        <option value="economy" <?= (isset($_GET['class']) && $_GET['class'] == 'economy') ? 'selected' : '' ?>>Эконом класс</option>
                        <option value="business" <?= (isset($_GET['class']) && $_GET['class'] == 'business') ? 'selected' : '' ?>>Бизнес класс</option>
                        <option value="first" <?= (isset($_GET['class']) && $_GET['class'] == 'first') ? 'selected' : '' ?>>Первый класс</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-search">Найти билеты</button>
            </form>
        </div>
        
        <?php if ($search_performed): ?>
            <div class="search-results">
                <div class="search-info">
                    <h3>Результаты поиска:</h3>
                    <p>Из <?= htmlspecialchars($from) ?> в <?= htmlspecialchars($to) ?> на <?= date('d.m.Y', strtotime($departure_date)) ?></p>
                </div>
                
                <?php if (empty($search_results)): ?>
                    <div class="no-flights">
                        <h3>✈️ Рейсы не найдены</h3>
                        <p>Попробуйте изменить параметры поиска или выберите другую дату.</p>
                    </div>
                <?php else: ?>
                    <div class="flights-list">
                        <?php foreach ($search_results as $flight): ?>
                            <div class="flight-card">
                                <div class="flight-header">
                                    <div class="flight-airline">
                                        🛫 <?= htmlspecialchars($flight['airline']) ?> - <?= htmlspecialchars($flight['flight_number']) ?>
                                    </div>
                                    <div class="flight-price">
                                        <?= number_format($flight['price'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                                
                                <div class="flight-route">
                                    <div class="flight-departure">
                                        <div class="flight-city"><?= htmlspecialchars($flight['departure_city']) ?></div>
                                        <div class="flight-time">
                                            <?= date('H:i', strtotime($flight['departure_time'])) ?>
                                            <small style="display: block; font-size: 12px; color: #666;"><?= date('d.m.Y', strtotime($flight['departure_date'])) ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-duration">
                                        ➡️
                                        <div><?= $flight['duration'] ?></div>
                                    </div>
                                    
                                    <div class="flight-arrival">
                                        <div class="flight-city"><?= htmlspecialchars($flight['arrival_city']) ?></div>
                                        <div class="flight-time"><?= $flight['arrival_time'] ?></div>
                                    </div>
                                </div>
                                
                                <div class="flight-details">
                                    <div class="detail-item">
                                        🪑 <span>Свободных мест: <?= $flight['available_seats'] ?></span>
                                    </div>
                                    <div class="detail-item">
                                        💼 <span>Класс: 
                                            <?php 
                                            $classNames = [
                                                'economy' => 'Эконом',
                                                'business' => 'Бизнес',
                                                'first' => 'Первый'
                                            ];
                                            echo $classNames[$flight['class']] ?? $flight['class'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        ⏰ <span>Вылет: <?= $flight['departure_time'] ?></span>
                                    </div>
                                </div>
                                
                                <a href="booking.php?flight_id=<?= $flight['id'] ?>&passengers=<?= $passengers ?>" 
                                   class="book-flight-btn">
                                    🎫 Забронировать билет
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Остальной контент страницы -->
            <?php include 'pages/home_content.php'; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    
    <script>
    // Устанавливаем минимальную дату на завтра
    const today = new Date();
    const tomorrow = new Date(today);
    tomorrow.setDate(tomorrow.getDate() + 1);
    
    const departureDate = document.getElementById('departure');
    const returnDate = document.getElementById('return');
    
    const formatDate = (date) => {
        return date.toISOString().split('T')[0];
    };
    
    if (departureDate) {
        departureDate.min = formatDate(tomorrow);
        if (!departureDate.value) {
            departureDate.value = formatDate(tomorrow);
        }
        
        if (returnDate) {
            returnDate.min = formatDate(tomorrow);
            
            departureDate.addEventListener('change', function() {
                const selectedDate = new Date(this.value);
                const nextDay = new Date(selectedDate);
                nextDay.setDate(nextDay.getDate() + 1);
                returnDate.min = formatDate(nextDay);
                
                if (returnDate.value && new Date(returnDate.value) < nextDay) {
                    returnDate.value = formatDate(nextDay);
                }
            });
        }
    }
    </script>
    
    <?php ob_end_flush(); ?>
</body>
</html>