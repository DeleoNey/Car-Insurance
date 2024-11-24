<?php
session_start(); // Ініціалізуємо сесію
ini_set('session.use_cookies', 1);



//echo '<pre>';
//print_r($_SESSION);
//echo '</pre>'; // перевірка, чи є дані в сесії



// Діагностика: Перевіряємо наявність цін у сесії
if (!isset($_SESSION['prices'])) {
    echo "Ціни не знайдені у сесії.";
    exit();
}

$basePrice = $_SESSION['prices']['base'] ?? 0;

// Перевірка наявності даних автомобіля
if (!isset($_SESSION['carData'])) {
    echo "Інформація про автомобіль не знайдена.";
    exit();
}

$carData = $_SESSION['carData'];

// Змінні для зручності
$carNumber = htmlspecialchars($carData['carNumber']);
$brand = htmlspecialchars($carData['brand']);
$model = htmlspecialchars($carData['model']);
$year = htmlspecialchars($carData['year']);
$vin = htmlspecialchars($carData['vin']);

// Підключення до бази даних
$conn = new mysqli('localhost', 'root', 'root', 'insurancecar'); // Змініть дані підключення

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Отримання інформації про клієнта
if (empty($_SESSION['client_id'])) {
    echo "ID клієнта не знайдено в сесії.";
    exit();
}

$client_id = $_SESSION['client_id'];

// Запит для отримання даних про клієнта
$sql = "SELECT * FROM clients WHERE client_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $client_id);

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$clientResult = $stmt->get_result();

if ($clientResult->num_rows > 0) {
    $clientData = $clientResult->fetch_assoc();
    $name = htmlspecialchars($clientData['first_name']);
    $surname = htmlspecialchars($clientData['last_name']);
    $patronymic = htmlspecialchars($clientData['middle_name']);
    $phone = htmlspecialchars($clientData['phone_number']);
} else {
    echo "Клієнта не знайдено.";
    exit();
}

// Запит для отримання даних про поліс
$sql = "SELECT * FROM policies WHERE client_id = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $client_id);

if (!$stmt->execute()) {
    die("Error executing statement: " . $stmt->error);
}

$policyResult = $stmt->get_result();

if ($policyResult->num_rows > 0) {
    $policyData = $policyResult->fetch_assoc();
    $policyNumber = htmlspecialchars($policyData['policy_number']);
    $startDate = htmlspecialchars($policyData['start_date']);
    $endDate = htmlspecialchars($policyData['end_date']);
} else {
    echo "Поліс не знайдено.";
    exit();
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>АвтоЩит - Електронний сертифікат про страхування</title>
</head>
<body>
<div class="container">
    <header class="header">
        <img src="./Logo.png" alt="АвтоЩит" class="logo">
        <div class="nav-container">
            <nav class="nav">
                <a href="#" class="nav-link green">Страхування онлайн</a>
                <a href="#" class="nav-link">Оплатити поліс</a>
                <a href="#" class="nav-link">Страховий випадок</a>
            </nav>
            <button type="button" class="header-button">Увійти</button>
        </div>
    </header>

    <div class="certificate-container">
        <div class="vehicle-info">
            <h2><?php echo $brand . ' ' . $model; ?></h2>
            <p><?php echo $vin; ?></p>
            <p>Рік випуску: <?php echo $year; ?></p>
            <p>Держ. номер: <?php echo $carNumber; ?></p>
        </div>
        <div class="certificate-details">
            <h2>Електронний сертифікат про страхування</h2>
            <p><strong>ЕЛЕКТРОННИЙ ПОЛІС</strong></p>
            <p>обов'язкового страхування цивільно-правової відповідальності власників наземних транспортних засобів</p>
            <p>№ <?php echo $policyNumber; ?> від <?php echo date('d.m.Y', strtotime($startDate)); ?></p>
            <p><strong><?php echo $surname . ' ' . $name . ' ' . $patronymic; ?></strong></p>
        </div>
        <img src="./Qr-code.png" alt="QR Code" class="qr-code">
    </div>

    <div class="main-info">
        <img src="./Qr-code.png" alt="QR Code" class="qr-code">
        <div class="main-info-content">
            <h2>Основна інформація:</h2>
            <p>Страховик: ПрАТ "Авто Щит"</p>
            <p>Ліцензія: АВ 213/51529423 від 10.10.2024</p>
            <p>ЄДРПОУ: 24009056</p>
            <p>Страхувальник: <?php echo$surname . ' ' . $name . ' ' . $patronymic; ?></p>
            <p>ІПН: <?php echo $client_id?></p>
            <p>Адреса: Львів, Героїв України, 29Б</p>
            <p>Тел.: <?php echo $phone?></p>
            <p>Забезпечений транспортний засіб:</p>
            <p>Марка: <?php echo $brand; ?></p>
            <p>Модель: <?php echo $model; ?></p>
            <p>Держ. номер: <?php echo $carNumber; ?></p>
            <p>VIN: <?php echo $vin; ?></p>
            <p>Строк дії договору:</p>
            <p>з <?php echo date('d.m.Y H:i', strtotime($startDate)); ?></p>
            <p>до <?php echo date('d.m.Y H:i', strtotime($endDate)); ?></p>
            <p>Страховий платіж: <?php echo number_format($basePrice, 2); ?> грн</p>
            <p>Франшиза <?php echo number_format($basePrice / 2, 2); ?> грн.</p>
            <p>Договір укладено в електронній формі з використанням Інформаційно-телекомунікаційної системи страховика.</p>
            <p>Достовірність цього полісу можна перевірити на сайті АТСА: https://car-shield.atca.ua</p>
        </div>
    </div>
</div>

<footer class="footer">
    <div class="container footer-content">
        <div class="footer-column">
            <h3>Гаряча лінія</h3>
            <p>Телефон: 0800-321-123</p>
            <p>Email: info@autoshield.ua</p>
        </div>
        <div class="footer-column">
            <h3>Адреса</h3>
            <p>Адреса: м. Київ, вул. Страхова, 1</p>
        </div>
        <div class="footer-column">
            <h3>Контакти</h3>
            <p>Телефон: 0800-321-123</p>
            <p>Для дзвінків з-за кордону: +380 080 022 22 23</p>
        </div>
    </div>
    <div class="container">
        <p>АвтоЩит - ваш надійний партнер у страхуванні з 2014 року</p>
    </div>
</footer>
</body>
</html>
