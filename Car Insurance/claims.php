<?php
session_start(); // Почати сесію

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Підключення до бази даних
    $servername = "localhost"; // або відповідний сервер
    $username = "root"; // ім'я користувача
    $password = "root"; // пароль користувача
    $dbname = "insurancecar"; // ім'я бази даних

    // Створення з'єднання
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Перевірка з'єднання
    if ($conn->connect_error) {
        die("Помилка підключення: " . $conn->connect_error);
    }

    // Отримання даних з форми
    $name = $_POST['name'];
    $surname = $_POST['surname'];
    $patronymic = $_POST['patronymic'];
    $phone_number = $_POST['phone_number'];
    $client_id = $_POST['client_id'];
    $policy_id = $_POST['claim_id'];
    $description = $_POST['description'];

    // Підготовка SQL-запиту для вставки даних
    $stmt = $conn->prepare("INSERT INTO claims (policy_id, claim_date, description, phone_number, client_id) VALUES (?, NOW(), ?, ?, ?)");

    // Перевірка на помилку підготовки
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => "Помилка підготовки запиту."]);
        exit;
    }

    // Використання правильних типів даних для bind_param
    $stmt->bind_param("ssss", $policy_id, $description, $phone_number, $client_id);

    // Виконання запиту та перевірка результату
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Заявку успішно відправлено!"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Помилка при відправленні: " . $stmt->error]);
    }

    // Закриття підготовленого виразу та з'єднання
    $stmt->close();
    $conn->close();
    exit;
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="claims.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>АвтоЩит - Оформлення полісу</title>
    <style>
        .status-message {
            text-align: left; /* Центрувати текст */
            margin-top: 20px; /* Відступ зверху */
            font-size: 18px; /* Розмір шрифту */
        }
        .success {
            color: green; /* Зелений колір для успіху */
        }
        .error {
            color: red; /* Червоний колір для помилки */
        }
    </style>
</head>
<body>
<div class="container">
    <header class="header">
        <img src="./Logo.png" alt="АвтоЩит" class="logo">
        <nav class="nav-container">
            <div>
                <a href="Index.html" class="nav-link">Страхування онлайн</a>
                <a href="#" class="nav-link">Оплатити поліс</a>
                <a href="#" class="nav-link green">Страховий випадок</a>
            </div>
            <button class="header-button">Увійти</button>
        </nav>
    </header>

    <div class="breadcrumb">
        <a href="Index.html" class="breadcrumb">Авто щит</a> > Страховий випадок
    </div>

    <main class="main-content">
        <section class="form-section">
            <h2>Страховий випадок</h2>
            <form id="insuranceForm" method="POST" action="claims.php">
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
                    <label for="phone_number">Номер телефону</label>
                    <input type="text" id="phone_number" name="phone_number" placeholder="+380 XX XXX XX XX" required>
                </div>
                <div class="form-group">
                    <label for="client_id">РНОКПП</label>
                    <input type="text" id="client_id" name="client_id" placeholder="0058246855" required>
                </div>
                <div class="form-group">
                    <label for="claim_id">Номер страхового полісу</label>
                    <input type="text" id="claim_id" name="claim_id" placeholder="№231" required>
                </div>
                <div class="form-group">
                    <label for="description">Опис</label>
                    <input type="text" id="description" name="description" placeholder="ДТП сталась в силу того..." required>
                </div>
                <button class="confirm-btn submit-button" type="submit" name="confirm">Підтвердити</button>
            </form>

            <div id="result" class="status-message"></div> <!-- Місце для виведення статусу -->
        </section>
    </main>
</div>

<script>
    document.querySelector('.submit-button').addEventListener('click', function(event) {
        event.preventDefault(); // Зупинити стандартну поведінку кнопки

        const form = document.getElementById('insuranceForm');
        const formData = new FormData(form); // Отримати дані з форми

        fetch('claims.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json()) // Очікуємо JSON відповідь
            .then(data => {
                const resultElement = document.getElementById('result');
                resultElement.className = 'status-message'; // Скинути попередній клас

                if (data.status === 'success') {
                    resultElement.classList.add('success'); // Додати клас для успішного статусу
                    resultElement.innerHTML = data.message; // Вивести успішне повідомлення
                    form.reset(); // Очистити форму після успіху
                } else {
                    resultElement.classList.add('error'); // Додати клас для помилки
                    resultElement.innerHTML = data.message; // Вивести повідомлення про помилку
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const resultElement = document.getElementById('result');
                resultElement.className = 'status-message error'; // Додати клас для помилки
                resultElement.innerHTML = 'Помилка при відправленні даних!'; // Вивести загальну помилку
            });
    });
</script>
</body>
</html>
