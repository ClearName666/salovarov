<?php
ob_start();
session_start();
include_once "connect.php";

$from = isset($_GET['from']) ? trim($_GET['from']) : '';
$to = isset($_GET['to']) ? trim($_GET['to']) : '';
$departure = isset($_GET['departure']) ? $_GET['departure'] : '';
$passengers = isset($_GET['passengers']) ? intval($_GET['passengers']) : 1;
$class = isset($_GET['class']) ? $_GET['class'] : '';
$min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
$max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 1000000;
$airlines = isset($_GET['airline']) ? $_GET['airline'] : [];

$sql = "SELECT * FROM flights WHERE 1=1";
$params = [];
$types = "";

if (!empty($from)) {
    $sql .= " AND departure_city LIKE ?";
    $params[] = "%$from%";
    $types .= "s";
}

if (!empty($to)) {
    $sql .= " AND arrival_city LIKE ?";
    $params[] = "%$to%";
    $types .= "s";
}

if (!empty($departure)) {
    $sql .= " AND DATE(departure_time) = ?";
    $params[] = $departure;
    $types .= "s";
}

if (!empty($class) && $class != 'all') {
    $sql .= " AND class = ?";
    $params[] = $class;
    $types .= "s";
}

if (!empty($min_price) && $min_price > 0) {
    $sql .= " AND price >= ?";
    $params[] = $min_price;
    $types .= "d";
}

if (!empty($max_price) && $max_price > 0) {
    $sql .= " AND price <= ?";
    $params[] = $max_price;
    $types .= "d";
}

if (!empty($airlines) && is_array($airlines)) {
    $placeholders = implode(',', array_fill(0, count($airlines), '?'));
    $sql .= " AND airline IN ($placeholders)";
    foreach ($airlines as $airline) {
        $params[] = $airline;
        $types .= "s";
    }
}

$sql .= " ORDER BY departure_time ASC";

$filtered_flights = [];
$stmt = $conn->prepare($sql);

if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $row['total_price'] = $row['price'] * $passengers;
        $row['passengers'] = $passengers;
        $filtered_flights[] = $row;
    }
    
    $stmt->close();
}

$airlines_list = [];
$airline_result = $conn->query("SELECT DISTINCT airline FROM flights ORDER BY airline");
while ($row = $airline_result->fetch_assoc()) {
    $airlines_list[] = $row['airline'];
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Результаты поиска | Авиабилеты</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/search.css">
    <script src="assets/js/main.js" defer></script>
    <script src="assets/js/search.js" defer></script>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <div class="search-results">
            <!-- Поисковая форма и результаты -->
            <?php include 'pages/searc_content.php'; ?>
        </div>
    </div>
    
    <?php include 'includes/footer.php'; ?>
    <?php ob_end_flush(); ?>
</body>
</html>