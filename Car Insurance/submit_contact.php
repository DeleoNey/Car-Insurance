<?php
// Підключаємо файл для з'єднання з базою даних
include 'db.php';

// Перевіряємо, чи були надіслані дані
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Отримуємо дані з форми
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    // SQL-запит для вставки даних у базу
    $sql = "INSERT INTO contact_requests (first_name, last_name, phone_number, email) 
            VALUES ('$first_name', '$last_name', '$phone', '$email')";

    if ($conn->query($sql) === TRUE) {
        echo "Дані успішно збережені!";
    } else {
        echo "Помилка: " . $sql . "<br>" . $conn->error;
    }

    // Закриваємо з'єднання
    $conn->close();
}
?>

