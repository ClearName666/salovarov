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
        /* Дополнительные стили для новых блоков */
        .stats-counter {
            font-size: 36px;
            font-weight: bold;
            background: linear-gradient(135deg, #00ffff, #ff00ff);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        
        .floating-card {
            animation: floatCard 4s ease-in-out infinite;
        }
        
        @keyframes floatCard {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-15px); }
        }
        
        .shake-animation {
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        .success-pop {
            animation: pop 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        
        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .rotating-icon {
            animation: rotateIcon 2s linear infinite;
        }
        
        @keyframes rotateIcon {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>
    <script>
        // Полный JavaScript с кучей анимаций и интерактива
        document.addEventListener('DOMContentLoaded', function() {
            // Плавное появление страницы
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 1s ease';
            setTimeout(() => {
                document.body.style.opacity = '1';
            }, 100);
            
            // ========== ГЛОБАЛЬНЫЕ АНИМАЦИИ ==========
            
            // Создание множества частиц
            createAdvancedParticles();
            
            // Анимация при скролле
            const animateOnScroll = () => {
                const elements = document.querySelectorAll('.flight-card, .feature-card, .destination-card, .countdown-timer, .clicker-game, .weather-widget');
                elements.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    const isVisible = rect.top < window.innerHeight - 100;
                    if (isVisible && !el.classList.contains('animated')) {
                        el.classList.add('animated');
                        el.style.opacity = '0';
                        el.style.transform = 'translateY(50px)';
                        setTimeout(() => {
                            el.style.transition = 'all 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1)';
                            el.style.opacity = '1';
                            el.style.transform = 'translateY(0)';
                        }, 100);
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll();
            
            // 3D эффект слежения за мышью для карточек
            const cards = document.querySelectorAll('.flight-card, .feature-card, .destination-card');
            cards.forEach(card => {
                card.addEventListener('mousemove', (e) => {
                    const rect = card.getBoundingClientRect();
                    const x = ((e.clientX - rect.left) / rect.width - 0.5) * 20;
                    const y = ((e.clientY - rect.top) / rect.height - 0.5) * 20;
                    card.style.transform = `perspective(1000px) rotateY(${x}deg) rotateX(${-y}deg) translateY(-10px)`;
                });
                
                card.addEventListener('mouseleave', () => {
                    card.style.transform = 'perspective(1000px) rotateY(0deg) rotateX(0deg) translateY(0)';
                });
            });
            
            // Эффект волны на всех кнопках
            const allBtns = document.querySelectorAll('button, .btn, .book-flight-btn, .clicker-btn, .carousel-btn');
            allBtns.forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const ripple = document.createElement('span');
                    ripple.style.position = 'absolute';
                    ripple.style.borderRadius = '50%';
                    ripple.style.backgroundColor = 'rgba(0, 255, 255, 0.6)';
                    ripple.style.width = '100px';
                    ripple.style.height = '100px';
                    ripple.style.marginLeft = '-50px';
                    ripple.style.marginTop = '-50px';
                    ripple.style.transform = 'scale(0)';
                    ripple.style.animation = 'rippleEffect 0.6s linear';
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
            
            // Стиль для ripple
            const rippleStyle = document.createElement('style');
            rippleStyle.textContent = `
                @keyframes rippleEffect {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(rippleStyle);
            
            // ========== СЧЕТЧИК ОБРАТНОГО ОТСЧЕТА ==========
            function startCountdown() {
                const targetDate = new Date();
                targetDate.setDate(targetDate.getDate() + 7);
                targetDate.setHours(0, 0, 0, 0);
                
                function updateCountdown() {
                    const now = new Date();
                    const diff = targetDate - now;
                    
                    if (diff <= 0) {
                        document.getElementById('days').innerHTML = '0';
                        document.getElementById('hours').innerHTML = '0';
                        document.getElementById('minutes').innerHTML = '0';
                        document.getElementById('seconds').innerHTML = '0';
                        return;
                    }
                    
                    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
                    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((diff % (1000 * 60)) / 1000);
                    
                    document.getElementById('days').innerHTML = days;
                    document.getElementById('hours').innerHTML = hours;
                    document.getElementById('minutes').innerHTML = minutes;
                    document.getElementById('seconds').innerHTML = seconds;
                }
                
                updateCountdown();
                setInterval(updateCountdown, 1000);
            }
            
            if (document.getElementById('days')) {
                startCountdown();
            }
            
            // ========== КЛИКЕР-ИГРА ==========
            let clickCount = 0;
            const clickerBtn = document.getElementById('clickerBtn');
            const clickerScore = document.getElementById('clickerScore');
            
            if (clickerBtn) {
                clickerBtn.addEventListener('click', function() {
                    clickCount++;
                    clickerScore.innerHTML = clickCount;
                    clickerScore.classList.add('success-pop');
                    setTimeout(() => {
                        clickerScore.classList.remove('success-pop');
                    }, 300);
                    
                    // Вибрация на мобильных
                    if (navigator.vibrate) {
                        navigator.vibrate(50);
                    }
                    
                    // Эффект частиц при клике
                    createClickParticles(this);
                    
                    // Сохраняем в localStorage
                    localStorage.setItem('clickerScore', clickCount);
                    
                    // Проверка на достижение
                    if (clickCount === 10) {
                        showNotification('🎉 Вы набрали 10 кликов! Получите скидку 5% на первый билет!', 'success');
                    } else if (clickCount === 50) {
                        showNotification('🏆 Невероятно! 50 кликов! Скидка 15% на любой рейс!', 'success');
                    } else if (clickCount === 100) {
                        showNotification('👑 ЛЕГЕНДА! 100 кликов! Бесплатное бронирование на 1 год!', 'success');
                    }
                });
                
                // Восстанавливаем сохраненный счет
                const savedScore = localStorage.getItem('clickerScore');
                if (savedScore) {
                    clickCount = parseInt(savedScore);
                    clickerScore.innerHTML = clickCount;
                }
            }
            
            function createClickParticles(element) {
                const rect = element.getBoundingClientRect();
                for (let i = 0; i < 10; i++) {
                    const particle = document.createElement('div');
                    particle.style.position = 'fixed';
                    particle.style.width = Math.random() * 10 + 5 + 'px';
                    particle.style.height = particle.style.width;
                    particle.style.background = `radial-gradient(circle, ${Math.random() > 0.5 ? '#00ffff' : '#ff00ff'}, transparent)`;
                    particle.style.borderRadius = '50%';
                    particle.style.pointerEvents = 'none';
                    particle.style.zIndex = '9999';
                    particle.style.left = rect.left + rect.width / 2 + 'px';
                    particle.style.top = rect.top + rect.height / 2 + 'px';
                    particle.style.animation = `particleExplode ${Math.random() * 0.5 + 0.5}s ease-out forwards`;
                    document.body.appendChild(particle);
                    setTimeout(() => particle.remove(), 500);
                }
            }
            
            // Стиль для частиц клика
            const particleStyle = document.createElement('style');
            particleStyle.textContent = `
                @keyframes particleExplode {
                    0% {
                        transform: translate(0, 0);
                        opacity: 1;
                    }
                    100% {
                        transform: translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(particleStyle);
            
            // ========== КАРУСЕЛЬ ОТЗЫВОВ ==========
            let currentTestimonial = 0;
            const testimonials = [
                { text: "Невероятный сервис! Нашел билеты дешевле, чем где-либо еще. Рекомендую всем!", author: "Анна С." },
                { text: "Лучший сайт для поиска авиабилетов. Интерфейс просто космос!", author: "Дмитрий К." },
                { text: "Быстро, удобно, стильно. Теперь только здесь бронирую перелеты.", author: "Елена М." },
                { text: "Акции и скидки радуют. Сэкономил 30% на билетах в Тайланд!", author: "Максим Р." },
                { text: "Поддержка работает отлично. Помогли с обменом билета за 5 минут.", author: "Ольга В." }
            ];
            
            function updateTestimonial() {
                const testimonialText = document.getElementById('testimonialText');
                const testimonialAuthor = document.getElementById('testimonialAuthor');
                
                if (testimonialText) {
                    testimonialText.style.opacity = '0';
                    testimonialText.style.transform = 'translateX(50px)';
                    setTimeout(() => {
                        testimonialText.innerHTML = testimonials[currentTestimonial].text;
                        testimonialAuthor.innerHTML = `— ${testimonials[currentTestimonial].author}`;
                        testimonialText.style.transition = 'all 0.5s ease';
                        testimonialText.style.opacity = '1';
                        testimonialText.style.transform = 'translateX(0)';
                    }, 300);
                }
            }
            
            window.prevTestimonial = function() {
                currentTestimonial = (currentTestimonial - 1 + testimonials.length) % testimonials.length;
                updateTestimonial();
            };
            
            window.nextTestimonial = function() {
                currentTestimonial = (currentTestimonial + 1) % testimonials.length;
                updateTestimonial();
            };
            
            // Автоматическая смена отзывов
            setInterval(() => {
                if (document.getElementById('testimonialText')) {
                    nextTestimonial();
                }
            }, 5000);
            
            // ========== ПОГОДНЫЙ ВИДЖЕТ ==========
            function updateWeather() {
                const cities = ['Москва', 'Санкт-Петербург', 'Сочи', 'Новосибирск', 'Владивосток'];
                const temps = [Math.floor(Math.random() * 30) - 10, Math.floor(Math.random() * 25) - 5, Math.floor(Math.random() * 35) + 5, Math.floor(Math.random() * 25) - 15, Math.floor(Math.random() * 20) - 10];
                const weathers = ['☀️', '⛅', '🌧️', '❄️', '🌪️', '🌈'];
                
                const randomCity = cities[Math.floor(Math.random() * cities.length)];
                const randomTemp = temps[Math.floor(Math.random() * temps.length)];
                const randomWeather = weathers[Math.floor(Math.random() * weathers.length)];
                
                const weatherWidget = document.getElementById('weatherWidget');
                if (weatherWidget) {
                    weatherWidget.innerHTML = `
                        <div style="font-size: 48px; margin-bottom: 20px;">${randomWeather}</div>
                        <div class="weather-temp">${randomTemp}°C</div>
                        <div style="font-size: 24px; margin-top: 15px;">${randomCity}</div>
                        <div style="font-size: 14px; margin-top: 10px; opacity: 0.8;">Обновлено сейчас</div>
                    `;
                }
            }
            
            if (document.getElementById('weatherWidget')) {
                updateWeather();
                setInterval(updateWeather, 30000);
            }
            
            // ========== СТАТИСТИКА ==========
            let flightCount = 0;
            let userCount = 0;
            
            function updateStats() {
                flightCount += Math.floor(Math.random() * 5);
                userCount += Math.floor(Math.random() * 10);
                
                const flightsStat = document.getElementById('flightsStat');
                const usersStat = document.getElementById('usersStat');
                
                if (flightsStat) flightsStat.innerHTML = flightCount.toLocaleString();
                if (usersStat) usersStat.innerHTML = userCount.toLocaleString();
            }
            
            if (document.getElementById('flightsStat')) {
                setInterval(updateStats, 2000);
            }
            
            // ========== СОЗДАНИЕ ПРОДВИНУТЫХ ЧАСТИЦ ==========
            function createAdvancedParticles() {
                const particleCount = 100;
                for (let i = 0; i < particleCount; i++) {
                    const particle = document.createElement('div');
                    particle.style.position = 'fixed';
                    particle.style.width = Math.random() * 4 + 1 + 'px';
                    particle.style.height = particle.style.width;
                    particle.style.background = `radial-gradient(circle, ${Math.random() > 0.7 ? '#00ffff' : '#ff00ff'}, transparent)`;
                    particle.style.borderRadius = '50%';
                    particle.style.pointerEvents = 'none';
                    particle.style.zIndex = '0';
                    particle.style.left = Math.random() * 100 + '%';
                    particle.style.top = Math.random() * 100 + '%';
                    particle.style.animation = `floatParticle ${Math.random() * 15 + 8}s linear infinite`;
                    particle.style.opacity = Math.random() * 0.4;
                    particle.style.filter = `blur(${Math.random() * 2}px)`;
                    document.body.appendChild(particle);
                }
            }
            
            const floatParticleStyle = document.createElement('style');
            floatParticleStyle.textContent = `
                @keyframes floatParticle {
                    0% {
                        transform: translateY(0) translateX(0);
                        opacity: 0;
                    }
                    20% {
                        opacity: 0.4;
                    }
                    80% {
                        opacity: 0.4;
                    }
                    100% {
                        transform: translateY(-100vh) translateX(${Math.random() * 200 - 100}px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(floatParticleStyle);
            
            // ========== АНИМАЦИЯ ДЛЯ ПОЛЕЙ ВВОДА ==========
            const inputs = document.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.style.transform = 'translateX(10px)';
                    input.style.boxShadow = '0 0 30px rgba(0, 255, 255, 0.5)';
                });
                input.addEventListener('blur', () => {
                    input.parentElement.style.transform = 'translateX(0)';
                    input.style.boxShadow = 'none';
                });
            });
            
            // ========== АНИМАЦИЯ ДЛЯ ССЫЛОК ==========
            const links = document.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('mouseenter', () => {
                    link.style.transition = 'all 0.3s';
                });
            });
            
            // ========== ОБНОВЛЕНИЕ ВРЕМЕНИ ==========
            function updateDateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('ru-RU');
                const dateString = now.toLocaleDateString('ru-RU', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                
                const timeElement = document.getElementById('currentTime');
                const dateElement = document.getElementById('currentDate');
                
                if (timeElement) timeElement.innerHTML = timeString;
                if (dateElement) dateElement.innerHTML = dateString;
            }
            
            setInterval(updateDateTime, 1000);
            updateDateTime();
        });
        
        // ========== ГЛОБАЛЬНЫЕ ФУНКЦИИ ==========
        
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : type === 'warning' ? '⚠️' : 'ℹ️';
            notification.innerHTML = `
                <div style="display: flex; align-items: center; gap: 15px;">
                    <span style="font-size: 24px;">${icon}</span>
                    <span style="flex: 1;">${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" 
                            style="background: none; border: none; color: inherit; cursor: pointer; font-size: 20px;">✕</button>
                </div>
            `;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 18px 25px;
                background: linear-gradient(135deg, rgba(0, 255, 255, 0.95), rgba(255, 0, 255, 0.95));
                color: #0a0e27;
                border-radius: 20px;
                font-weight: bold;
                z-index: 10000;
                animation: slideInRight 0.4s ease;
                backdrop-filter: blur(10px);
                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                min-width: 300px;
                max-width: 500px;
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }
        
        function validateSearchForm() {
            const from = document.getElementById('from');
            const to = document.getElementById('to');
            const departure = document.getElementById('departure');
            
            if (!from.value) {
                showNotification('🚀 Пожалуйста, укажите город вылета!', 'error');
                from.classList.add('shake-animation');
                setTimeout(() => from.classList.remove('shake-animation'), 500);
                from.focus();
                return false;
            }
            if (!to.value) {
                showNotification('🎯 Пожалуйста, укажите город прилета!', 'error');
                to.classList.add('shake-animation');
                setTimeout(() => to.classList.remove('shake-animation'), 500);
                to.focus();
                return false;
            }
            if (from.value === to.value) {
                showNotification('😅 Города вылета и прилета не могут совпадать!', 'error');
                return false;
            }
            if (!departure.value) {
                showNotification('📅 Пожалуйста, выберите дату вылета!', 'error');
                return false;
            }
            
            showNotification('🔍 Ищем лучшие предложения...', 'info');
            return true;
        }
    </script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <section class="hero">
        <div class="container">
            <h1 class="glow">✈️ Найдите лучшие авиабилеты</h1>
            <p>Сравните цены от сотен авиакомпаний и турагентств, чтобы найти идеальное путешествие по лучшей цене</p>
            <div id="currentDate" style="font-size: 14px; opacity: 0.8; margin-top: 20px;"></div>
            <div id="currentTime" style="font-size: 20px; font-weight: bold; margin-top: 10px;"></div>
        </div>
    </section>
    
    <div class="container">
        <div class="search-container">
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
                        if (isset($conn)) {
                            $cities_query = $conn->query("SELECT DISTINCT departure_city FROM flights ORDER BY departure_city");
                            if ($cities_query) {
                                while($city = $cities_query->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($city['departure_city']) . '">';
                                }
                            }
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
                        if (isset($conn)) {
                            $cities_query = $conn->query("SELECT DISTINCT arrival_city FROM flights ORDER BY arrival_city");
                            if ($cities_query) {
                                while($city = $cities_query->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($city['arrival_city']) . '">';
                                }
                            }
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
            <!-- ИНТЕРАКТИВНЫЕ БЛОКИ -->
            
            <!-- Счетчик обратного отсчета -->
            <div class="countdown-timer floating-card">
                <div class="countdown-title">🔥 Горячие предложения! До конца акции осталось:</div>
                <div class="countdown-numbers">
                    <div class="countdown-item">
                        <span class="countdown-value" id="days">0</span>
                        <span class="countdown-label">Дней</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="hours">0</span>
                        <span class="countdown-label">Часов</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="minutes">0</span>
                        <span class="countdown-label">Минут</span>
                    </div>
                    <div class="countdown-item">
                        <span class="countdown-value" id="seconds">0</span>
                        <span class="countdown-label">Секунд</span>
                    </div>
                </div>
            </div>
            
            <!-- Кликер-игра -->
            <div class="clicker-game">
                <h3>🎮 Кликер-игра: получай скидки! 🎮</h3>
                <div class="clicker-score" id="clickerScore">0</div>
                <button class="clicker-btn" id="clickerBtn">🔨 КЛИКНИ МЕНЯ! 🔨</button>
                <p style="margin-top: 15px; font-size: 14px; opacity: 0.8;">💡 Чем больше кликов - тем больше скидка!</p>
            </div>
            
            <!-- Статистика -->
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">✈️</div>
                    <h3>Выполнено рейсов</h3>
                    <div class="stats-counter" id="flightsStat">12,345</div>
                    <p>и это число растет каждую секунду!</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">👥</div>
                    <h3>Довольных пассажиров</h3>
                    <div class="stats-counter" id="usersStat">98,765</div>
                    <p>присоединяйтесь к счастливым путешественникам!</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌍</div>
                    <h3>Направлений</h3>
                    <div class="stats-counter">150+</div>
                    <p>стран и городов по всему миру</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">⭐</div>
                    <h3>Рейтинг</h3>
                    <div class="stats-counter">4.9</div>
                    <p>из 5 на основе 10,000+ отзывов</p>
                </div>
            </div>
            
            <!-- Погодный виджет -->
            <div class="weather-widget" id="weatherWidget">
                <div style="font-size: 48px; margin-bottom: 20px;">🌤️</div>
                <div class="weather-temp">22°C</div>
                <div style="font-size: 24px; margin-top: 15px;">Москва</div>
                <div style="font-size: 14px; margin-top: 10px;">Загрузка...</div>
            </div>
            
            <!-- Карусель отзывов -->
            <div class="testimonials-carousel">
                <h3 style="text-align: center; margin-bottom: 30px;">💬 Что говорят наши пассажиры</h3>
                <div class="testimonial-slide">
                    <div class="testimonial-text" id="testimonialText">Невероятный сервис! Нашел билеты дешевле, чем где-либо еще. Рекомендую всем!</div>
                    <div class="testimonial-author" id="testimonialAuthor">— Анна С.</div>
                </div>
                <div class="carousel-nav">
                    <button class="carousel-btn" onclick="prevTestimonial()">◀ Предыдущий</button>
                    <button class="carousel-btn" onclick="nextTestimonial()">Следующий ▶</button>
                </div>
            </div>
            
            <!-- Карта направлений (интерактивная) -->
            <div class="destinations-map">
                <h3>🗺️ Популярные направления</h3>
                <div class="map-placeholder">
                    <div class="map-pulse"></div>
                    <div style="position: relative; z-index: 1;">
                        <div style="font-size: 64px; margin-bottom: 20px;">🗺️</div>
                        <p>🌍 Москва → Санкт-Петербург | 3 990 ₽</p>
                        <p>🌍 Москва → Сочи | 5 490 ₽</p>
                        <p>🌍 Москва → Новосибирск | 8 990 ₽</p>
                        <p>🌍 Москва → Владивосток | 12 990 ₽</p>
                        <p>🌍 Москва → Калининград | 6 490 ₽</p>
                    </div>
                </div>
            </div>
            
            <!-- Преимущества -->
            <div class="features">
                <div class="section-title">
                    <h2>✨ Почему выбирают нас</h2>
                    <p>Мы делаем путешествия доступными и комфортными для каждого</p>
                </div>
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">💰</div>
                        <h3>Лучшие цены</h3>
                        <p>Гарантируем самые низкие цены на авиабилеты</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">⚡</div>
                        <h3>Мгновенное бронирование</h3>
                        <p>Бронируйте билеты за считанные секунды</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>Безопасность</h3>
                        <p>Ваши данные под надежной защитой</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🎁</div>
                        <h3>Бонусы и скидки</h3>
                        <p>Накопительная система скидок для постоянных клиентов</p>
                    </div>
                </div>
            </div>
            
            <!-- Популярные направления -->
            <div class="destinations">
                <div class="section-title">
                    <h2>🏝️ Популярные направления</h2>
                    <p>Самые востребованные маршруты у наших пассажиров</p>
                </div>
                <div class="destinations-grid">
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1533929736458-ca588d08c8be?w=400&h=300&fit=crop');"></div>
                        <div class="destination-content">
                            <h3>Стамбул, Турция</h3>
                            <p>Уникальное сочетание Европы и Азии</p>
                            <div class="destination-price">от 9 990 ₽</div>
                        </div>
                    </div>
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=300&fit=crop');"></div>
                        <div class="destination-content">
                            <h3>Пхукет, Таиланд</h3>
                            <p>Райский отдых на побережье Андаманского моря</p>
                            <div class="destination-price">от 24 990 ₽</div>
                        </div>
                    </div>
                    <div class="destination-card">
                        <div class="destination-img" style="background-image: url('https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop');"></div>
                        <div class="destination-content">
                            <h3>Интерлакен, Швейцария</h3>
                            <p>Сердце Швейцарских Альп</p>
                            <div class="destination-price">от 18 990 ₽</div>
                        </div>
                    </div>
                </div>
            </div>
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
    
    // Приветственное уведомление
    setTimeout(() => {
        showNotification('✨ Добро пожаловать в футуристический мир авиаперелетов! ✨', 'success');
    }, 1500);
    </script>
    
    <?php ob_end_flush(); ?>
</body>
</html>