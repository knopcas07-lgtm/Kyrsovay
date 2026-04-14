<?php 
session_start();
require 'check_admin.php';
require '../db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    die("ID не указан");
}

// получаем услугу
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    die("Услуга не найдена");
}

$message = '';
$uploadedImage = $product['image_url'] ?? '';

// ОБРАБОТКА ФОРМЫ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $title = trim($_POST['title']);
    $price = $_POST['price'];
    $desc  = trim($_POST['description']);

    // загрузка изображения
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $file = $_FILES['image'];
        $allowedTypes = ['image/jpeg','image/png','image/gif'];

        if (in_array($file['type'], $allowedTypes)) {

            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newName = uniqid('img_') . '.' . $ext;
            $path = 'uploads/' . $newName;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                $uploadedImage = $path;
            }

        } else {
            $message = '<div class="alert alert-danger">Только изображения</div>';
        }
    }

    // ОБНОВЛЕНИЕ
    if (!empty($title) && !empty($price)) {

        $stmt = $pdo->prepare("
            UPDATE products 
            SET title = ?, description = ?, price = ?, image_url = ?
            WHERE id = ?
        ");

        $stmt->execute([$title, $desc, $price, $uploadedImage, $id]);

        $message = '<div class="alert alert-success">Сохранено!</div>';
        
        $product['title'] = $title;
        $product['price'] = $price;
        $product['description'] = $desc;
        $product['image_url'] = $uploadedImage;
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">

<title>Редактировать услугу</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

/* контейнер */
.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

/* 🔥 HEADER СТИЛИ */
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

/* основной контейнер */
.main-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 15px;
}

/* карточка */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* мобилка */
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

        <a href="javascript:history.back()" class="btn btn-light mb-2 d-inline-block">← Назад</a>
    
        <h1 class="text-center">Редактирование услуги</h1>
    
    </div>

    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" class="card p-3 shadow-sm">

        <div class="mb-3">
            <label>Название</label>
            <input type="text" name="title" class="form-control"
                   value="<?= htmlspecialchars($product['title']) ?>" required>
        </div>

        <div class="mb-3">
            <label>Цена</label>
            <input type="number" name="price" class="form-control"
                   value="<?= $product['price'] ?>" required>
        </div>

        <div class="mb-3">
            <label>Описание</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label>Текущее изображение:</label><br>
            <?php if (!empty($product['image_url'])): ?>
                <img src="<?= htmlspecialchars($product['image_url']) ?>" style="max-width:100%; height:150px; object-fit:cover;">
            <?php endif; ?>
        </div>

        <div class="mb-3">
            <label>Новое изображение</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button class="btn btn-success w-100">Сохранить</button>

    </form>

</div>

</body>
</html>