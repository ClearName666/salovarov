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
    <title>Авиабилеты | Футуристический поиск и бронирование</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Дополнительные анимации прямо здесь для надежности */
        @keyframes glowPulse {
            0%, 100% { text-shadow: 0 0 5px rgba(0, 255, 255, 0.5); }
            50% { text-shadow: 0 0 20px rgba(0, 255, 255, 0.8), 0 0 30px rgba(255, 0, 255, 0.5); }
        }
        
        @keyframes borderRotate {
            0% { border-image: linear-gradient(0deg, #00ffff, #ff00ff) 1; }
            100% { border-image: linear-gradient(360deg, #00ffff, #ff00ff) 1; }
        }
        
        .glow-text {
            animation: glowPulse 2s infinite;
        }
        
        .rotating-border {
            border: 2px solid;
            border-image: linear-gradient(45deg, #00ffff, #ff00ff) 1;
            animation: borderRotate 3s linear infinite;
        }
    </style>
    <script>
        // Все JavaScript анимации прямо здесь
        document.addEventListener('DOMContentLoaded', function() {
            // Анимация при загрузке страницы
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.8s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
            
            // Партиклы на фоне
            createParticles();
            
            // Анимация для всех элементов при скролле
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.flight-card, .feature-card, .destination-card');
                elements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    const isVisible = rect.top < window.innerHeight - 100;
                    if (isVisible && !el.classList.contains('animated')) {
                        el.classList.add('animated');
                        el.style.animation = 'slideInLeft 0.6s ease forwards';
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll();
            
            // Эффект слежения за мышью для кнопок
            const buttons = document.querySelectorAll('.btn, .book-flight-btn, .btn-search');
            buttons.forEach(btn => {
                btn.addEventListener('mousemove', (e) => {
                    const rect = btn.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    btn.style.setProperty('--mouse-x', `${x}px`);
                    btn.style.setProperty('--mouse-y', `${y}px`);
                });
            });
            
            // 3D эффект для карточек
            const cards = document.querySelectorAll('.flight-card, .feature-card, .destination-card');
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    const rotateX = (y - centerY) / 20;
                    const rotateY = (centerX - x) / 20;
                    card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-5px)`;
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
                });
            });
            
            // Анимация для полей ввода
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.style.transform = 'translateX(5px)';
                });
                input.addEventListener('blur', () => {
                    input.parentElement.style.transform = 'translateX(0)';
                });
            });
            
            // Мигающий курсор эффект
            setInterval(() => {
                const cursor = document.createElement('div');
                cursor.style.position = 'fixed';
                cursor.style.width = '2px';
                cursor.style.height = '20px';
                cursor.style.background = '#00ffff';
                cursor.style.boxShadow = '0 0 10px #00ffff';
                cursor.style.pointerEvents = 'none';
                cursor.style.zIndex = '9999';
                cursor.style.transition = 'all 0.1s';
                document.body.appendChild(cursor);
                
                document.addEventListener('mousemove', (e) => {
                    cursor.style.left = e.clientX + 'px';
                    cursor.style.top = e.clientY - 10 + 'px';
                });
                
                setTimeout(() => {
                    cursor.remove();
                }, 100);
            }, 5000);
            
            // Функция создания частиц
            function createParticles() {
                const particleCount = 50;
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.style.position = 'fixed';
                    particle.style.width = Math.random() * 3 + 1 + 'px';
                    particle.style.height = particle.style.width;
                    particle.style.background = `radial-gradient(circle, ${Math.random() > 0.5 ? '#00ffff' : '#ff00ff'}, transparent)`;
                    particle.style.borderRadius = '50%';
                    particle.style.pointerEvents = 'none';
                    particle.style.zIndex = '0';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animation = `floatParticle ${Math.random() * 10 + 5}s linear infinite`;
                    particle.style.opacity = Math.random() * 0.3;
                    document.body.appendChild(particle);
                }
            }
            
            // Добавляем стиль для анимации частиц
            const style = document.createElement('style');
            style.textContent = `
                @keyframes floatParticle {
                    0% {
                        transform: translateY(0) translateX(0);
                        opacity: 0;
                    }
                    50% {
                        opacity: 0.3;
                    }
                    100% {
                        transform: translateY(-100vh) translateX(${Math.random() * 100 - 50}px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Эффект волны на кнопках
            const allBtns = document.querySelectorAll('button, .btn, .book-flight-btn');
            allBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.backgroundColor = 'rgba(255, 255, 255, 0.6';
                    ripple.style.width = '100px';
                    ripple.style.height = '100px';
                    ripple.style.marginLeft = '-50px';
                    ripple.style.marginTop = '-50px';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'ripple 0.6s linear';
                    ripple.style.pointerEvents = 'none';
                    const rect = btn.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';
                    btn.style.position = 'relative';
                    btn.style.overflow = 'hidden';
                    btn.appendChild(ripple);
                    setTimeout(() => ripple.remove(), 600);
                });
            });
            
            const rippleStyle = document.createElement('style');
            rippleStyle.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(rippleStyle);
            
            // Обновление времени в реальном времени
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('ru-RU');
                const timeElements = document.querySelectorAll('.current-time');
                timeElements.forEach(el => {
                    if (el) el.textContent = timeString;
                });
            }
            setInterval(updateTime, 1000);
            
            // Плавное появление результатов поиска
            const results = document.querySelector('.search-results');
            if (results) {
                results.style.animation = 'fadeInUp 0.8s ease';
            }
        });
        
        // Функция для уведомлений
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <span>${message}</span>
                <button onclick="this.parentElement.remove()" style="background: none; border: none; color: inherit; cursor: pointer; margin-left: 15px;">✕</button>
            `;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 15px 25px;
                background: linear-gradient(135deg, rgba(0, 255, 255, 0.9), rgba(255, 0, 255, 0.9));
                color: #0a0e27;
                border-radius: 15px;
                font-weight: bold;
                z-index: 10000;
                animation: slideInRight 0.3s ease;
                backdrop-filter: blur(10px);
            `;
            document.body.appendChild(notification);
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Валидация формы
        function validateSearchForm() {
            const from = document.getElementById('from');
            const to = document.getElementById('to');
            const departure = document.getElementById('departure');
            
            if (!from.value) {
                showNotification('Пожалуйста, укажите город вылета', 'error');
                from.focus();
                return false;
            }
            if (!to.value) {
                showNotification('Пожалуйста, укажите город прилета', 'error');
                to.focus();
                return false;
            }
            if (from.value === to.value) {
                showNotification('Города вылета и прилета не могут совпадать', 'error');
                return false;
            }
            if (!departure.value) {
                showNotification('Пожалуйста, выберите дату вылета', 'error');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero">
        <div class="container">
            <h1 class="glow-text">✈️ Найдите лучшие авиабилеты</h1>
            <p>Сравните цены от сотен авиакомпаний и турагентств, чтобы найти идеальное путешествие по лучшей цене</p>
            <div class="current-time" style="font-size: 14px; opacity: 0.8; margin-top: 20px;"></div>
        </div>
    </section>
    
    <div class="container">
        <div class="search-container rotating-border">
            <div class="search-title">
                <h2>🔍 Поиск авиабилетов</h2>
                <p>Найдите самые выгодные предложения на рейсы по всему миру</p>
            </div>
            
            <form class="search-form" action="" method="GET" onsubmit="return validateSearchForm()">
                <div class="form-group">
                    <label for="from" class="form-label">🚀 Откуда</label>
                    <input type="text" class="form-control" id="from" name="from" required 
                           placeholder="Город вылета" 
                           value="<?= isset($_GET['from']) ? htmlspecialchars($_GET['from']) : '' ?>"
                           list="cities-from">
                    <datalist id="cities-from">
                        <?php
                        $cities_query = $conn->query("SELECT DISTINCT departure_city FROM flights ORDER BY departure_city");
                        while($city = $cities_query->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($city['departure_city']) . '">';
                        }
                        ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="to" class="form-label">🎯 Куда</label>
                    <input type="text" class="form-control" id="to" name="to" required 
                           placeholder="Город прилета"
                           value="<?= isset($_GET['to']) ? htmlspecialchars($_GET['to']) : '' ?>"
                           list="cities-to">
                    <datalist id="cities-to">
                        <?php
                        $cities_query = $conn->query("SELECT DISTINCT arrival_city FROM flights ORDER BY arrival_city");
                        while($city = $cities_query->fetch_assoc()) {
                            echo '<option value="' . htmlspecialchars($city['arrival_city']) . '">';
                        }
                        ?>
                    </datalist>
                </div>
                
                <div class="form-group">
                    <label for="departure" class="form-label">📅 Туда</label>
                    <input type="date" class="form-control" id="departure" name="departure" required
                           value="<?= isset($_GET['departure']) ? $_GET['departure'] : date('Y-m-d', strtotime('+1 day')) ?>">
                </div>
                
                <div class="form-group">
                    <label for="return" class="form-label">🔄 Обратно (опционально)</label>
                    <input type="date" class="form-control" id="return" name="return"
                           value="<?= isset($_GET['return']) ? $_GET['return'] : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="passengers" class="form-label">👥 Пассажиры</label>
                    <select class="form-control" id="passengers" name="passengers">
                        <?php for($i = 1; $i <= 8; $i++): ?>
                            <option value="<?= $i ?>" <?= (isset($_GET['passengers']) && $_GET['passengers'] == $i) ? 'selected' : '' ?>>
                                <?= $i ?> пассажир<?= $i > 1 ? 'а' : '' ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="class" class="form-label">💺 Класс</label>
                    <select class="form-control" id="class" name="class">
                        <option value="economy" <?= (isset($_GET['class']) && $_GET['class'] == 'economy') ? 'selected' : '' ?>>💚 Эконом класс</option>
                        <option value="business" <?= (isset($_GET['class']) && $_GET['class'] == 'business') ? 'selected' : '' ?>>💎 Бизнес класс</option>
                        <option value="first" <?= (isset($_GET['class']) && $_GET['class'] == 'first') ? 'selected' : '' ?>>👑 Первый класс</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-search">✨ Найти билеты ✨</button>
            </form>
        </div>
        
        <?php if ($search_performed): ?>
            <div class="search-results">
                <div class="search-info">
                    <h3>🎯 Результаты поиска:</h3>
                    <p>🚀 Из <?= htmlspecialchars($from) ?> → в <?= htmlspecialchars($to) ?> на 📅 <?= date('d.m.Y', strtotime($departure_date)) ?></p>
                </div>
                
                <?php if (empty($search_results)): ?>
                    <div class="no-flights">
                        <h3>😢 Рейсы не найдены</h3>
                        <p>Попробуйте изменить параметры поиска или выберите другую дату.</p>
                        <p>💡 Совет: Попробуйте поискать билеты на соседние даты</p>
                    </div>
                <?php else: ?>
                    <div class="flights-list">
                        <?php foreach ($search_results as $index => $flight): ?>
                            <div class="flight-card" style="animation-delay: <?= $index * 0.1 ?>s">
                                <div class="flight-header">
                                    <div class="flight-airline">
                                        ✈️ <?= htmlspecialchars($flight['airline']) ?> - <?= htmlspecialchars($flight['flight_number']) ?>
                                    </div>
                                    <div class="flight-price">
                                        💰 <?= number_format($flight['price'], 0, '', ' ') ?> ₽
                                    </div>
                                </div>
                                
                                <div class="flight-route">
                                    <div class="flight-departure">
                                        <div class="flight-city">🚀 <?= htmlspecialchars($flight['departure_city']) ?></div>
                                        <div class="flight-time">
                                            ⏰ <?= date('H:i', strtotime($flight['departure_time'])) ?>
                                            <small style="display: block; font-size: 12px;">📅 <?= date('d.m.Y', strtotime($flight['departure_date'])) ?></small>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-duration">
                                        ✈️ → ✈️
                                        <div><?= $flight['duration'] ?></div>
                                    </div>
                                    
                                    <div class="flight-arrival">
                                        <div class="flight-city">🎯 <?= htmlspecialchars($flight['arrival_city']) ?></div>
                                        <div class="flight-time">⏰ <?= $flight['arrival_time'] ?></div>
                                    </div>
                                </div>
                                
                                <div class="flight-details">
                                    <div class="detail-item">
                                        🪑 <span>Свободно мест: <?= $flight['available_seats'] ?></span>
                                    </div>
                                    <div class="detail-item">
                                        💼 <span>Класс: 
                                            <?php 
                                            $classNames = [
                                                'economy' => '💚 Эконом',
                                                'business' => '💎 Бизнес',
                                                'first' => '👑 Первый'
                                            ];
                                            echo $classNames[$flight['class']] ?? $flight['class'];
                                            ?>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        🎫 <span>Рейс: <?= $flight['flight_number'] ?></span>
                                    </div>
                                </div>
                                
                                <a href="booking.php?flight_id=<?= $flight['id'] ?>&passengers=<?= $passengers ?>" 
                                   class="book-flight-btn" 
                                   onclick="showNotification('✈️ Перенаправление на бронирование...', 'info')">
                                    🎫 Забронировать билет сейчас 🎫
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
    
    // Приветственное уведомление при загрузке
    setTimeout(() => {
        showNotification('✨ Добро пожаловать в футуристический поиск авиабилетов! ✨', 'info');
    }, 1000);
    </script>
    
    <?php ob_end_flush(); ?>
</body>
</html>