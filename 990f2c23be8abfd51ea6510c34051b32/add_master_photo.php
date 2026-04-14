<?php
require 'check_admin.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require '../db.php';

$master_id = $_GET['master_id'] ?? 0;

// Получаем информацию о мастере
$stmt = $pdo->prepare("SELECT * FROM masters WHERE id = ?");
$stmt->execute([$master_id]);
$master = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$master) {
    header('Location: masters.php');
    exit;
}

$uploadDir = 'uploads/'; // просто папка uploads
$error = '';
$success = '';

// Создаем папку если её нет
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Обработка загрузки нескольких файлов
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['images']['name'][0])) {
    $files = $_FILES['images'];
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $uploaded = 0;

    for ($i = 0; $i < count($files['name']); $i++) {
        $ext = pathinfo($files['name'][$i], PATHINFO_EXTENSION);

        if (!in_array(strtolower($ext), $allowed)) {
            $error .= "Файл {$files['name'][$i]} имеет недопустимый формат.<br>";
            continue;
        }

        $newName = 'master_' . $master_id . '_' . uniqid() . '.' . $ext;
        $dest = $uploadDir . $newName;

        if (move_uploaded_file($files['tmp_name'][$i], $dest)) {
            $relative_path = $uploadDir . $newName;
            try {
                $stmt = $pdo->prepare("INSERT INTO master_portfolio (master_id, image_url) VALUES (?, ?)");
                $stmt->execute([$master_id, $relative_path]);
                $uploaded++;
            } catch (PDOException $e) {
                $error .= "Ошибка базы данных для файла {$files['name'][$i]}: " . $e->getMessage() . "<br>";
            }
        } else {
            $error .= "Ошибка при загрузке файла {$files['name'][$i]}<br>";
        }
    }

    if ($uploaded > 0) {
        $success = "Успешно загружено $uploaded фото!";
    }
}

// Получаем портфолио мастера
$portfolio_items = [];
try {
    $stmt = $pdo->prepare("SELECT * FROM master_portfolio WHERE master_id = ? ORDER BY created_at DESC");
    $stmt->execute([$master_id]);
    $portfolio_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error .= "Ошибка при загрузке портфолио: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Портфолио мастера</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    .portfolio-img {
        height: 150px;
        width: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
        cursor: pointer;
    }
    .portfolio-img:hover {
        transform: scale(1.2);
        z-index: 10;
        position: relative;
    }
</style>
</head>
<body>
<div class="container mt-5">

    <h3>Мастер: <?= htmlspecialchars($master['name']) ?></h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <a href="masters.php" class="btn btn-secondary mb-3">← Назад к мастерам</a>

    <!-- Форма загрузки нескольких фото -->
    <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Фотографии работ</label>
            <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.gif" multiple required>
            <small class="text-muted">Можно выбрать несколько файлов одновременно. Форматы: JPG, PNG, GIF</small>
        </div>
        <button type="submit" class="btn btn-primary">Загрузить фото</button>
    </form>

    <!-- Просмотр портфолио -->
    <div class="row">
        <?php if(count($portfolio_items) > 0): ?>
            <?php foreach($portfolio_items as $item): ?>
                <div class="col-md-3 mb-3">
                    <img src="<?= htmlspecialchars($item['image_url']) ?>" 
                         class="img-fluid rounded portfolio-img" alt="Фото работы"
                         onerror="this.src='https://via.placeholder.com/300x200?text=Фото'">
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="text-muted">Портфолио пусто</p>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
