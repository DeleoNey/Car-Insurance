<?php
session_start(); // Ініціалізує сесію для зберігання даних автомобіля та цін

// Параметри підключення до бази даних
$servername = "localhost"; // Ім'я сервера бази даних
$username = "root";        // Ім'я користувача для доступу до бази даних
$password = "root";        // Пароль для доступу до бази даних
$dbname = "insurancecar";  // Назва бази даних

// Підключення до бази даних
$conn = new mysqli($servername, $username, $password, $dbname);

// Перевірка на успішне підключення
if ($conn->connect_error) {
    die(json_encode(['found' => false, 'message' => 'Помилка підключення до бази даних'])); // Завершення скрипта, якщо не вдалося підключитись
}

// Перевірка, чи метод запиту є POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Отримання та очищення введеного номера автомобіля
    $licensePlate = $conn->real_escape_string($_POST['license-plate']);

    // Пошук автомобіля за номером у таблиці vehicles
    $sql = "SELECT * FROM vehicles WHERE vehicle_number = '$licensePlate'";
    $result = $conn->query($sql);

    // Перевірка, чи знайдено автомобіль
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc(); // Отримання даних про автомобіль
        $currentYear = date("Y");      // Отримання поточного року
        $age = $currentYear - intval($row['year']); // Обчислення віку автомобіля

        // Збереження даних автомобіля в сесію
        $_SESSION['carData'] = [
            'brand' => $row['make'],
            'model' => $row['model'],
            'year' => $row['year'],
            'vin' => $row['vin'],
            'carNumber' => $row['vehicle_number']
        ];


        // Підрахунок вартості страхових пакетів
        $basePrice = 3000 + ($age * 450) + pow($age, 1.2) * 20;
        $midPrice = 7000 + ($age * 700) + pow($age, 1.3) * 35;
        $premiumPrice = 24500 + ($age * 1400) + pow($age, 1.4) * 50;

        // Збереження розрахованих цін у сесію
        $_SESSION['prices'] = [
            'base' => $basePrice,
            'mid' => $midPrice,
            'premium' => $premiumPrice
        ];

        // Відправлення відповіді, що автомобіль знайдено
        echo json_encode(['found' => true]);
    } else {
        // Відправлення відповіді, що автомобіль не знайдено
        echo json_encode(['found' => false, 'message' => 'Автомобіль не знайдено, введіть будь ласка дані вручну']);
    }
} else {
    // Відправлення відповіді, якщо метод запиту не є POST
    echo json_encode(['found' => false, 'message' => 'Неправильний метод запиту']);
}

// Закриття підключення до бази даних
$conn->close();
?>
