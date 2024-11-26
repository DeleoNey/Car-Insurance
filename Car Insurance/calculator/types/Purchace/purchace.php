<?php
session_start(); // Ініціалізуємо сесію
ini_set('session.use_cookies', 1);

// Діагностика: Перевіряємо наявність цін у сесії
if (!isset($_SESSION['prices'])) {
    echo "Ціни не знайдені у сесії.";
    exit();
}

$basePrice = $_SESSION['prices']['base'] ?? 0;

if (isset($_SESSION['carData'])) {
    $carData = $_SESSION['carData'];

    // Змінні для зручності
    $carNumber = htmlspecialchars($carData['carNumber']);
    $brand = htmlspecialchars($carData['brand']);
    $model = htmlspecialchars($carData['model']);
    $year = htmlspecialchars($carData['year']);
    $vin = htmlspecialchars($carData['vin']);
} else {
    echo "Інформація про автомобіль не знайдена.";
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $client_id = htmlspecialchars($_POST['client_id']); // Отримуємо ID клієнта
    if (!empty($client_id)) {
        $_SESSION['client_id'] = $client_id; // Зберігаємо в сесії
    } else {
        echo "ID клієнта не може бути пустим.";
    }
    $name = htmlspecialchars($_POST['name']);
    $surname = htmlspecialchars($_POST['surname']);
    $patronymic = htmlspecialchars($_POST['patronymic']);
    $client_id = htmlspecialchars($_POST['client_id']); // РНОКПП
    $tel = htmlspecialchars($_POST['phone-number']);


    // Підключення до бази даних
    $conn = new mysqli('localhost', 'root', 'root', 'insurancecar');

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Додати запис у таблицю clients
    $insertClient = "INSERT INTO clients (client_id, first_name, last_name, middle_name, phone_number) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE client_id = client_id";
    $stmt = $conn->prepare($insertClient);

    if ($stmt) {
        $stmt->bind_param("sssss", $client_id, $name, $surname, $patronymic, $tel);
        $stmt->execute();
        $stmt->close();
    } else {
        echo "Помилка підготовки запиту clients: " . $conn->error;
    }

    // Перевірити, чи існує автомобіль з таким номером
    $checkVehicle = "SELECT * FROM vehicles WHERE vehicle_number = ?";
    $stmt = $conn->prepare($checkVehicle);
    $stmt->bind_param("s", $carNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Автомобіль існує, оновлюємо поле client_id
        $updateVehicle = "UPDATE vehicles SET client_id = ? WHERE vehicle_number = ?";
        $stmt = $conn->prepare($updateVehicle);

        if ($stmt) {
            $stmt->bind_param("ss", $client_id, $carNumber);
            if ($stmt->execute()) {
                // echo "Запис в vehicles оновлено: client_id для автомобіля $carNumber встановлено.";
            } else {
                echo "Помилка виконання запиту vehicles: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Помилка підготовки запиту vehicles: " . $conn->error;
        }
    } else {
        // Автомобіль не існує, додаємо новий запис у таблицю vehicles
        $insertVehicle = "INSERT INTO vehicles (client_id, vehicle_number, make, model, year, vin) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insertVehicle);

        if ($stmt) {
            // Додаємо автомобіль без client_id спочатку
            $stmt->bind_param("ssssis", null, $carNumber, $brand, $model, $year, $vin);
            if ($stmt->execute()) {
                // Отримуємо ID нового автомобіля
                $vehicle_id = $stmt->insert_id;

                // Тепер оновлюємо client_id
                $updateVehicle = "UPDATE vehicles SET client_id = ? WHERE vehicle_id = ?";
                $stmt = $conn->prepare($updateVehicle);
                if ($stmt) {
                    $stmt->bind_param("si", $client_id, $vehicle_id);
                    if ($stmt->execute()) {
                        // echo "Запис успішно додано до vehicles і client_id встановлено.";
                    } else {
                        echo "Помилка виконання запиту для оновлення client_id: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    echo "Помилка підготовки запиту для оновлення client_id: " . $conn->error;
                }
            } else {
                echo "Помилка виконання запиту vehicles: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Помилка підготовки запиту vehicles: " . $conn->error;
        }
    }

    // Додати запис у таблицю policies
    $start_date = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime('+1 year'));
    $policy_type_id = 1; // Ваше значення policy_type_id

    $insertPolicy = "INSERT INTO policies (client_id, vehicle_number, policy_type_id, start_date, end_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insertPolicy);

    if ($stmt) {
        $stmt->bind_param("ssiss", $client_id, $carNumber, $policy_type_id, $start_date, $end_date);
        $stmt->execute();
        $policy_id = $stmt->insert_id; // Отримання ID нової політики
        $stmt->close();
    } else {
        echo "Помилка підготовки запиту policies: " . $conn->error;
    }

    // Додати запис у таблицю transactions
    $amount = $basePrice * 0.9;
    $transaction_date = date('Y-m-d H:i:s');

    $insertTransaction = "INSERT INTO transactions (policy_id, client_id, amount, transaction_date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insertTransaction);

    if ($stmt) {
        $stmt->bind_param("isss", $policy_id, $client_id, $amount, $transaction_date);
        if ($stmt->execute()) {
            // echo "Транзакцію успішно виконано.";
        } else {
            echo "Помилка виконання запиту transactions: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Помилка підготовки запиту transactions: " . $conn->error;
    }

    // Закриваємо підключення
    $conn->close();

    // Перенаправлення на нову сторінку
    header('Location: certificate/certificate.php');
    exit(); // Вихід після перенаправлення
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="styles.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АвтоЩит - Оформлення полісу</title>
</head>
<body>
<div class="container">
    <header class="header">
        <img src="./Logo.png" alt="АвтоЩит" class="logo">
        <nav class="nav-container">
            <div>
                <a href="#" class="nav-link green">Страхування онлайн</a>
                <a href="#" class="nav-link">Оплатити поліс</a>
                <a href="#" class="nav-link">Страховий випадок</a>
            </div>
            <button class="header-button">Увійти</button>
        </nav>
    </header>

    <div class="breadcrumb">
        АвтоЩит > ОСЦПВ > Пропозиції > Оформлення
    </div>

    <main class="main-content">
        <section class="form-section">
            <h2>Контактні дані</h2>
            <form id="insuranceForm" method="POST" action="purchace.php">
                <div class="form-group">
                    <label for="name">Ім'я</label>
                    <input type="text" id="name" name="name" placeholder="Іван" required>
                </div>
                <div class="form-group">
                    <label for="surname">Прізвище</label>
                    <input type="text" id="surname" name="surname" placeholder="Степаненко" required>
                </div>
                <div class="form-group">
                    <label for="patronymic">По батькові</label>
                    <input type="text" id="patronymic" name="patronymic" placeholder="Андрійович" required>
                </div>
                <div class="form-group">
                    <label for="phone-number">Номер телефону</label>
                    <input type="text" id="phone-number" name="phone-number" placeholder="+380 XX XXX XX XX" required>
                </div>
                <div class="form-group">
                    <label for="client_id">РНОКПП</label>
                    <input type="text" id="client_id" name="client_id" placeholder="0084568392" required>
                </div>
                <div class="form-group">
                    <label for="car-number">Держ. номер авто</label>
                    <input type="text" placeholder="<?php echo $carNumber; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="car-brand">Марка</label>
                    <input type="text" placeholder="<?php echo $brand; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="car-model">Модель</label>
                    <input type="text" placeholder="<?php echo $model; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="car-year">Рік випуску</label>
                    <input type="text" placeholder="<?php echo $year; ?>" readonly>
                </div>
                <div class="form-group">
                    <label for="vin">VIN-Номер</label>
                    <input type="text" placeholder="<?php echo $vin; ?>" readonly>
                </div>
                <button class="confirm-btn" type="submit" name="confirm">Підтвердити</button>
            </form>
        </section>

        <section class="policy-section">
            <h2>Електронний поліс ОСЦПВ</h2>
            <div class="policy-details">
                <div class="policy-item">
                    <span>VUSOB</span>
                </div>
                <div class="policy-item">
                    <span>Франшиза</span>
                    <span> <?php echo number_format($basePrice / 2, 2); ?> грн. </span>
                </div>
                <div class="policy-item">
                    <span>Реєстрація</span>
                    <span>Львів</span>
                </div>
                <div class="policy-item">
                    <span>Пільги</span>
                    <span>Відсутні</span>
                </div>
                <div class="policy-item">
                    <span>Термін дії</span>
                    <span>12 місяців</span>
                </div>
                <div class="policy-item">
                    <span>Тип транспортного засобу</span>
                    <span>Легковий автомобіль</span>
                </div>
                <div class="policy-item">
                    <span>ОСЦПВ</span>
                    <span><s><?php echo number_format($basePrice, 2); ?> грн.</s> <?php echo number_format($basePrice * 0.9, 2); ?> грн.</span>
                </div>
                <div class="total-price">
                    <span>До сплати:</span>
                    <span><?php echo number_format($basePrice * 0.9, 2); ?> грн.</span>
                </div>
            </div>
        </section>
    </main>

</body>
</html>
