<div class="search-header">
    <h1>Результаты поиска авиабилетов</h1>
    
    <?php if (!empty($from) || !empty($to) || !empty($departure)) { ?>
        <div class="search-params">
            <?php if (!empty($from)) { ?>
                <div class="param-item">
                    <span class="param-label">Откуда:</span>
                    <span class="param-value"><?php echo htmlspecialchars($from); ?></span>
                </div>
            <?php } ?>
            
            <?php if (!empty($to)) { ?>
                <div class="param-item">
                    <span class="param-label">Куда:</span>
                    <span class="param-value"><?php echo htmlspecialchars($to); ?></span>
                </div>
            <?php } ?>
            
            <?php if (!empty($departure)) { ?>
                <div class="param-item">
                    <span class="param-label">Дата вылета:</span>
                    <span class="param-value"><?php echo date('d.m.Y', strtotime($departure)); ?></span>
                </div>
            <?php } ?>
            
            <div class="param-item">
                <span class="param-label">Пассажиры:</span>
                <span class="param-value"><?php echo $passengers; ?></span>
            </div>
            
            <div class="param-item">
                <span class="param-label">Класс:</span>
                <span class="param-value">
                    <?php 
                    $class_names = [
                        'economy' => 'Эконом',
                        'business' => 'Бизнес',
                        'first' => 'Первый'
                    ];
                    echo isset($class_names[$class]) ? $class_names[$class] : 'Все классы';
                    ?>
                </span>
            </div>
        </div>
    <?php } ?>
    
    <div class="results-count">
        Найдено рейсов: <?php echo count($filtered_flights); ?>
    </div>
</div>

<div class="search-layout">
    <!-- Боковая панель фильтров -->
    <aside class="filters-sidebar">
        <form action="search.php" method="GET">
            <div class="filter-group">
                <label class="filter-label">Авиакомпании</label>
                <div class="filter-options">
                    <?php foreach ($airlines_list as $airline_name) { ?>
                        <div class="filter-option">
                            <input type="checkbox" 
                                   id="airline_<?php echo $airline_name; ?>" 
                                   name="airline[]" 
                                   value="<?php echo htmlspecialchars($airline_name); ?>"
                                   <?php echo in_array($airline_name, $airlines) ? 'checked' : ''; ?>>
                            <label for="airline_<?php echo $airline_name; ?>">
                                <?php echo htmlspecialchars($airline_name); ?>
                            </label>
                        </div>
                    <?php } ?>
                </div>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Класс</label>
                <div class="filter-options">
                    <div class="filter-option">
                        <input type="radio" id="class_all" name="class" value="all" 
                               <?php echo (empty($class) || $class == 'all') ? 'checked' : ''; ?>>
                        <label for="class_all">Все классы</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="class_economy" name="class" value="economy" 
                               <?php echo ($class == 'economy') ? 'checked' : ''; ?>>
                        <label for="class_economy">Эконом</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="class_business" name="class" value="business" 
                               <?php echo ($class == 'business') ? 'checked' : ''; ?>>
                        <label for="class_business">Бизнес</label>
                    </div>
                    <div class="filter-option">
                        <input type="radio" id="class_first" name="class" value="first" 
                               <?php echo ($class == 'first') ? 'checked' : ''; ?>>
                        <label for="class_first">Первый</label>
                    </div>
                </div>
            </div>
            
            <div class="filter-group">
                <label class="filter-label">Цена, ₽</label>
                <div class="price-range">
                    <div class="price-inputs">
                        <input type="number" class="price-input" placeholder="от 0" min="0" 
                               name="min_price" value="<?php echo $min_price > 0 ? $min_price : ''; ?>">
                        <input type="number" class="price-input" placeholder="до 100000" min="0" 
                               name="max_price" value="<?php echo $max_price > 0 ? $max_price : ''; ?>">
                    </div>
                </div>
            </div>
            
            <?php if (!empty($from)) { ?>
                <input type="hidden" name="from" value="<?php echo htmlspecialchars($from); ?>">
            <?php } ?>
            <?php if (!empty($to)) { ?>
                <input type="hidden" name="to" value="<?php echo htmlspecialchars($to); ?>">
            <?php } ?>
            <?php if (!empty($departure)) { ?>
                <input type="hidden" name="departure" value="<?php echo htmlspecialchars($departure); ?>">
            <?php } ?>
            <?php if (!empty($passengers)) { ?>
                <input type="hidden" name="passengers" value="<?php echo $passengers; ?>">
            <?php } ?>
            
            <button type="submit" class="btn-apply">Применить фильтры</button>
            <a href="search.php" class="btn btn-primary" style="margin-top: 10px; display: block; text-align: center; background-color: #ccc; color: #333;">
                Сбросить фильтры
            </a>
        </form>
    </aside>
    
    <!-- Основной контент с результатами -->
    <main class="results-content">
        <?php if (count($filtered_flights) > 0) { ?>
            <?php foreach ($filtered_flights as $flight) { ?>
                <div class="flight-card">
                    <div class="flight-header">
                        <div class="airline-info">
                            <div class="airline-logo"><?php echo substr($flight['airline'], 0, 2); ?></div>
                            <div>
                                <div class="airline-name"><?php echo $flight['airline']; ?></div>
                                <div class="flight-number"><?php echo $flight['flight_number']; ?></div>
                            </div>
                        </div>
                        <div class="flight-class">
                            <?php 
                            $class_names = [
                                'economy' => 'Эконом класс',
                                'business' => 'Бизнес класс',
                                'first' => 'Первый класс'
                            ];
                            echo isset($class_names[$flight['class']]) ? $class_names[$flight['class']] : 'Эконом класс';
                            ?>
                        </div>
                    </div>
                    <!-- В search_content.php в конце flight-card добавим: -->

                    <div class="flight-details">
                        <div class="route-info">
                            <div class="city-name"><?php echo $flight['departure_city']; ?></div>
                            <div class="airport-name"><?php echo $flight['departure_airport']; ?></div>
                            <div class="time"><?php echo date('H:i', strtotime($flight['departure_time'])); ?></div>
                            <div class="date"><?php echo date('d.m.Y', strtotime($flight['departure_time'])); ?></div>
                        </div>
                        
                        <div class="flight-duration">
                            <div class="duration-value"><?php echo $flight['duration']; ?></div>
                            <div>Прямой рейс</div>
                        </div>
                        
                        <div class="route-info">
                            <div class="city-name"><?php echo $flight['arrival_city']; ?></div>
                            <div class="airport-name"><?php echo $flight['arrival_airport']; ?></div>
                            <div class="time"><?php echo date('H:i', strtotime($flight['arrival_time'])); ?></div>
                            <div class="date"><?php echo date('d.m.Y', strtotime($flight['arrival_time'])); ?></div>
                        </div>
                    </div>
                    
                    <div class="flight-footer">
                        <div class="price-info">
                            <div class="price-label">Цена за <?php echo $flight['passengers']; ?> пассажир(а/ов)</div>
                            <div class="price"><?php echo number_format($flight['total_price'], 0, ',', ' '); ?> ₽</div>
                            <div class="per-passenger"><?php echo number_format($flight['price'], 0, ',', ' '); ?> ₽ за пассажира</div>
                        </div>
                        
                        <?php if (isset($_SESSION["user_id"])) { ?>
                            <button class="btn-book" onclick="bookFlight(<?php echo $flight['id']; ?>)">
                                Забронировать
                            </button>
                        <?php } else { ?>
                            <a href="login.php" class="btn-book">
                                Войдите для бронирования
                            </a>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        <?php } else { ?>
            <div class="no-flights">
                <h3>Рейсы не найдены</h3>
                <p>По вашему запросу не найдено подходящих рейсов. Попробуйте изменить параметры поиска.</p>
                <a href="index.php" class="btn-back">Вернуться к поиску</a>
            </div>
        <?php } ?>
    </main>
</div>