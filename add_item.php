<?php
// 1. Подключаем БД и проверку на админа
require '../db.php';
require 'check_admin.php'; // session_start() + проверка admin

$message = '';

// 2. Если нажата кнопка "Сохранить"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $price = $_POST['price'];
    $desc  = trim($_POST['description']);
    $img   = trim($_POST['image_url']);

    // ID текущего пользователя из сессии
    $userId = $_SESSION['user_id'];

    if (empty($title) || empty($price)) {
        $message = '<div class="alert alert-danger">Заполните название и цену!</div>';
    } else {

        // 3. Сохраняем в БД с user_id
        $sql = "INSERT INTO products 
                (title, description, price, image_url, user_id)
                VALUES (:t, :d, :p, :i, :u)";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':t' => $title,
                ':d' => $desc,
                ':p' => $price,
                ':i' => $img,
                ':u' => $userId
            ]);

            $message = '<div class="alert alert-success">Товар успешно добавлен!</div>';

        } catch (PDOException $e) {
            $message = '<div class="alert alert-danger">Ошибка БД: ' . $e->getMessage() . '</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Добавить товар</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h1>Добавление нового товара</h1>
    <a href="admin_panel.php" class="btn btn-secondary mb-3">← Назад</a>

    <?= $message ?>

    <form method="POST" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label>Название товара:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Цена (руб):</label>
            <input type="number" name="price" class="form-control" step="0.01" required>
        </div>

        <div class="mb-3">
            <label>Ссылка на картинку (URL):</label>
            <input type="text" name="image_url" class="form-control">
        </div>

        <div class="mb-3">
            <label>Описание:</label>
            <textarea name="description" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Сохранить в БД</button>
    </form>
</div>
</body>
</html>