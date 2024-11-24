<?php
$host = 'localhost'; // сервер бази даних
$db = 'insurancecar'; // назва бази даних
$user = 'root';      // користувач бази даних
$pass = 'root';          // пароль для користувача бази даних

// Створюємо з'єднання
$conn = new mysqli($host, $user, $pass, $db);

// Перевіряємо з'єднання
if ($conn->connect_error) {
    die("З'єднання не вдалося: " . $conn->connect_error);
}
?>
