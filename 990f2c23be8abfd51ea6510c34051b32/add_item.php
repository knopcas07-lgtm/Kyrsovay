<?php
require '../db.php';
require 'check_admin.php';

$message = '';
$uploadedImage = null;

// Загрузка изображения
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['image'];

    $uploadDir = 'uploads/';
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($file['type'], $allowedTypes)) {
        $message = '<div class="alert alert-danger">Можно загружать только картинки</div>';
    } else {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $newName = uniqid('img_') . '.' . $extension;
        $destination = $uploadDir . $newName;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $uploadedImage = $destination;
            $message = '<div class="alert alert-success">Изображение загружено!</div>';
        }
    }
}

// Добавление услуги
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $price = $_POST['price'];
    $desc  = trim($_POST['description']);
    $duration = trim($_POST['duration']);

    $img = $uploadedImage ?? '';
    $userId = $_SESSION['user_id'];

    if (!empty($title) && !empty($price)) {
        $stmt = $pdo->prepare("
            INSERT INTO products (title, description, price, image_url, user_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$title, $desc, $price, $img, $userId]);

        $message = '<div class="alert alert-success">Услуга добавлена!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Добавить услугу</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

/* Контейнер как в каталоге */
.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

/* 🔥 HEADER СТИЛИ (ДОБАВЛЕНО) */
.header { 
    background-color: #fff; 
    width: 100%; 
    box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
    position: sticky; 
    top: 0; 
    z-index: 999; 
    padding: 10px 0; 
}

.header-container { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    width: 100%; 
    max-width: 1200px; 
    margin: 0 auto; 
    padding: 0 15px; 
}

.logo a { 
    font-weight: 700; 
    color: #007bff; 
    text-decoration: none; 
    font-size: 1.5rem; 
}

.nav ul { 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    list-style: none; 
    margin: 0; 
    padding: 0; 
}

.nav ul li { 
    display: flex; 
    align-items: center; 
}

.nav ul li a { 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    height: 38px; 
    padding: 0 14px; 
    text-decoration: none; 
    border-radius: 6px; 
    color: #333; 
    font-size: 14px; 
}

.nav ul li a.btn { 
    background-color: #007bff; 
    color: #fff; 
}

.nav ul li a.btn-outline { 
    border: 1px solid #007bff; 
    color: #007bff; 
}

.nav ul li a:hover { 
    opacity: 0.8; 
}

/* остальное */
.main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
}

form.card {
    border-radius: 12px;
}

.back-link:hover {
    text-decoration: underline;
}

/* 📱 мобилка */
@media (max-width: 768px) {
    .main-container {
        padding: 10px;
    }

    .btn {
        width: 100%;
    }

    .form-control {
        font-size: 14px;
    }
}
</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="main-container">

    <div class="container mb-3">

        <a href="admin_panel.php" class="btn btn-light mb-2 d-inline-block">← Назад</a>
    
        <h1 class="text-center">Добавление услуги</h1>
    
    </div>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">

        <div class="mb-3">
            <label>Название:</label>
            <input type="text" name="title" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Продолжительность:</label>
            <input type="text" name="duration" class="form-control">
        </div>

        <div class="mb-3">
            <label>Цена:</label>
            <input type="number" name="price" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Описание:</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>Изображение:</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button class="btn btn-success">Сохранить</button>

    </form>

</div>

</body>
</html>