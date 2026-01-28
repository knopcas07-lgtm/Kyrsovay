<?php
require 'check_admin.php'; // Только админ!
require '../db.php';


// САМОЕ СЛОЖНОЕ: Объединяем 3 таблицы в одном запросе
// orders (главная) + users (чтобы взять email) + products (чтобы взять название)
$sql = "
    SELECT 
        appointments.id as appointment_id,
        appointments.date,                    
        appointments.time,                     
        appointments.created_at,
        users.email as client_email,                        
        products.title as service_title,       
        products.price as service_price
    FROM appointments
    JOIN users ON appointments.user_id = users.id
    JOIN products ON appointments.product_id = products.id
    ORDER BY appointments.id DESC
";

$stmt = $pdo->query($sql);
$appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <title>Управление записями</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
    <h1>Все записи</h1>
    <a href="index.php">На главную</a>
    
    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID Записи</th>
                <th>Дата и Время</th>
                <th>Клиент (Email)</th>
                <th>Услуга</th>
                <th>Цена</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment): ?>
            <tr>
                <td><?= $appointment['appointment_id'] ?></td>
                <td><?= $appointment['date'] ?> <?= substr($appointment['time'], 0, 5) ?></td>
                <td><?= htmlspecialchars($appointment['client_email']) ?></td>
                <td><?= htmlspecialchars($appointment['service_title']) ?></td>
                <td><?= $appointment['service_price'] ?> ₽</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>