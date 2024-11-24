<?php
session_start(); // Ініціалізуємо сесію
ini_set('session.use_cookies', 1);


// Діагностика: Перевіряємо наявність цін у сесії
if (!isset($_SESSION['prices'])) {
    echo "Ціни не знайдені у сесії.";
    exit();
}

$basePrice = $_SESSION['prices']['base'] ?? 0;
$midPrice = $_SESSION['prices']['mid'] ?? 0;
$premiumPrice = $_SESSION['prices']['premium'] ?? 0;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./styles.css" />
    <title>АвтоЩит - Автострахування</title>
</head>
<body>
<div class="container">
    <header class="header">
        <img src="./Logo.png" alt="АвтоЩит" class="logo">
        <nav class="nav">
            <a href="#" class="nav-link green">Страхування онлайн</a>
            <a href="#" class="nav-link">Оплатити поліс</a>
            <a href="#" class="nav-link">Страховий випадок</a>
        </nav>
        <button class="header-button">Увійти</button>
    </header>

    <div class="breadcrumbs">
        АвтоЩит > ОСЦПВ > Пропозиції
    </div>

    <?php

    // Параметри підключення до бази даних
    $servername = "localhost";
    $username = "root";
    $password = "root";
    $dbname = "insurancecar";

    // Підключення до бази даних
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Перевірка підключення
    if ($conn->connect_error) {
        die("Помилка підключення: " . $conn->connect_error);
    }

    // SQL-запит для отримання даних з таблиці policy_types
    $sql = "SELECT * FROM policy_types";
    $result = $conn->query($sql);

    // Перевірка наявності результатів
    if ($result->num_rows > 0) {
        echo '<div class="insurance-options">';

        // Виведення даних для кожного запису
        while ($row = $result->fetch_assoc()) {
            echo '<div class="insurance-card">';
            echo '<h2>' . htmlspecialchars($row['name']) . '</h2>';

            // Отримання ціни з сесії
            $basePrice = $_SESSION['prices']['base'];
            $midPrice = $_SESSION['prices']['mid'];
            $premiumPrice = $_SESSION['prices']['premium'];

            // Виведення базової ціни
            if ($row['name'] == 'Базова автоцивілка') {
                echo '<h2>' . number_format($basePrice, 2) . ' ₴</h2>';
            } elseif ($row['name'] == 'Розширена автоцивілка') {
                echo '<h2>' . number_format($midPrice, 2) . ' ₴</h2>';
            } elseif ($row['name'] == 'Преміум автоцивілка') {
                echo '<h2>' . number_format($premiumPrice, 2) . ' ₴</h2>';
            }

            echo '<ul>';
            echo '<li>Ліміт відповідальності:<br>' . htmlspecialchars($row['liability_limit']) . '</li>';
            echo '<li>Термін дії: ' . htmlspecialchars($row['duration']) . '</li>';
            echo '<li>Підходить для: ' . htmlspecialchars($row['suitable_for']) . '</li>';
            echo '</ul>';
            echo '<button class="buy-button">Купити</button>';
            echo '</div>';
        }

        echo '</div>'; // Закриваємо div.insurance-options
    } else {
        echo "Не знайдено жодних полісів.";
    }

    // Закриття з'єднання
    $conn->close();
    ?>


</div>
<footer class="footer">
    <div class="container accent">
        <div class="footer-grid">
            <div>
                <h3>Гаряча лінія</h3>
                <p>Телефон: 0800-321-123</p>
                <p>Email: info@autoshield.ua</p>
            </div>
            <div>
                <h3>Адреса</h3>
                <p>м. Київ, вул. Страхова, 1</p>
            </div>
            <div>
                <h3>Контакти</h3>
                <p>Телефон: 0800-321-123</p>
                <p>Для дзвінків з-за кордону: +380 080 022 22 23</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>АвтоЩит - ваш надійний партнер у страхуванні з 2014 року</p>
        </div>
    </div>
</footer>
<script>
    // Отримуємо всі кнопки з класом 'buy-button'
    const buyButtons = document.querySelectorAll('.buy-button');

    // Додаємо обробник події для кожної кнопки
    buyButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Перенаправляємо користувача на сторінку 'purchace.php'
            window.location.href = 'Purchace/purchace.php';
        });
    });
</script>
</body>
</html>
