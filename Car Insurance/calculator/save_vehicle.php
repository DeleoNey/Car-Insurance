<?php
session_start(); // Ініціалізація сесії
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

// Отримання даних з форми
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vehicleType = $_POST['vehicle-type'];
    $vehicleBrand = $_POST['vehicle-brand'];
    $vehicleModel = $_POST['vehicle-model'];
    $vehicleYear = $_POST['vehicle-year'];
    $vehicleVin = $_POST['vehicle-vin'];
    $vehicleNumber = $_POST['vehicle-number'];
    $promo = isset($_POST['promo']) ? 'yes' : 'no';

    // Обчислення року
    $currentYear = date("Y");
    $age = $currentYear - intval($vehicleYear);

    // Формула обчислення ціни
    $basePrice = 3000 + ($age * 450) + pow($age, 1.2) * 20;
    $midPrice = 7000 + ($age * 700) + pow($age, 1.3) * 35;
    $premiumPrice = 24500 + ($age * 1400) + pow($age, 1.4) * 50;
    session_start(); // Ініціалізація сесії

    // Захист від SQL-ін'єкцій
    $vehicleNumber = $conn->real_escape_string($vehicleNumber);
    $vehicleBrand = $conn->real_escape_string($vehicleBrand);
    $vehicleModel = $conn->real_escape_string($vehicleModel);
    $vehicleVin = $conn->real_escape_string($vehicleVin);
    $vehicleYear = $conn->real_escape_string($vehicleYear);

    // Вставка даних у таблицю
    $sql = "INSERT INTO vehicles (vehicle_number, client_id, make, model, year, vin)
            VALUES ('$vehicleNumber', NULL, '$vehicleBrand', '$vehicleModel', '$vehicleYear', '$vehicleVin')";

    if ($conn->query($sql) === TRUE) {
        // Зберігаємо ціни в сесії
        $_SESSION['prices'] = [
            'base' => $basePrice,
            'mid' => $midPrice,
            'premium' => $premiumPrice,
        ];

        // Зберігаємо інформацію про автомобіль у сесії
        $_SESSION['carData'] = [
            'brand' => $vehicleBrand,
            'model' => $vehicleModel,
            'year' => $vehicleYear,
            'vin' => $vehicleVin,
            'carNumber' => $vehicleNumber,
        ];

        // Перенаправлення на сторінку types.html
        header("Location: /calculator/types/types.php");
        exit();
    } else {
        echo "Помилка: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>

