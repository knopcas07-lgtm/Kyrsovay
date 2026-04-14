<?php
session_start();
require '../db.php';
require 'check_admin.php';

$message = '';
$error = '';
$success = '';

// Папка для загрузки изображений
$uploadDir = 'uploads/';

// Обработка отправки формы
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $specialization = $_POST['specialization'] ?? '';
    $description = $_POST['description'] ?? '';
    $experience_years = $_POST['experience_years'] ?? 0;

    // Обработка файла
    $image_url = null;
    if (!empty($_FILES['image']['name'])) {
        $file = $_FILES['image'];
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg','jpeg','png','gif'];

        if (!in_array(strtolower($ext), $allowed)) {
            $error = "Недопустимый формат файла. Используйте jpg, png или gif.";
        } else {
            $newName = uniqid() . '.' . $ext;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $dest = $uploadDir . $newName;
            if (move_uploaded_file($file['tmp_name'], $dest)) {
                $image_url = $dest;
            } else {
                $error = "Ошибка при загрузке файла";
            }
        }
    }

    // Сохраняем в базу, если нет ошибок
    if (empty($error)) {
        $stmt = $pdo->prepare("
            INSERT INTO masters (name, specialization, description, experience_years, image_url) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $specialization, $description, $experience_years, $image_url]);
        $success = "Мастер успешно добавлен!";
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Добавить мастера</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
html, body {
    margin: 0; padding: 0; overflow-x: hidden; background-color: #f8f9fa;
}

.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

.main-container {
    max-width: 880px;
    margin: 0 auto;
    padding: 15px;
}

/* Header */
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
    gap: 10px;
    list-style: none;
    margin: 0;
    padding: 0;
}
.nav ul li a {
    text-decoration: none;
    padding: 6px 12px;
    border-radius: 6px;
    transition: 0.3s;
    color: #333;
}
.nav ul li a.btn {
    background-color: #007bff;
    color: #fff;
}
.nav ul li a.btn-outline {
    border: 1px solid #007bff;
    color: #007bff;
}
.nav ul li a:hover { opacity: 0.8; }

.burger { display: none; flex-direction: column; cursor: pointer; gap: 4px; }
.burger span { display: block; height: 3px; width: 25px; background-color: #333; border-radius: 2px; }

/* КНОПКА + ЗАГОЛОВОК (как ты просил) */
.back-link:hover {
    text-decoration: underline;
}

/* мобилка */
@media (max-width: 768px) {
    .main-container { padding: 10px; }
    h2 { text-align: center; font-size: 18px; }
    form.card { padding: 15px !important; }
    .btn { width: 100%; padding: 10px; font-size: 14px; }
}
</style>
</head>

<body>

<!-- Header -->
<header class="header">
    <div class="header-container">
        <div class="logo"><a href="index.php">Салон красоты</a></div>
        <nav class="nav" id="nav-menu">
            <ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <li><a href="admin_panel.php">Админка</a></li>
                    <?php else: ?>
                        <li><a href="profile.php">Профиль</a></li>
                    <?php endif; ?>
                    <li><a href="masters.php">Мастера</a></li>
                    <li><a href="logout.php" class="btn-outline">Выйти</a></li>
                <?php else: ?>
                    <li><a href="login.php">Войти</a></li>
                    <li><a href="register.php" class="btn">Регистрация</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="burger" id="burger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
</header>

<!-- Контент -->
<div class="main-container">

    <div class="container mb-3">

        <a href="admin_panel.php" class="btn btn-light mb-2 d-inline-block">← Назад</a>

        <h2 class="text-center">Добавление мастера</h2>

    </div>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if(!empty($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Имя мастера</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Направление</label>
            <input type="text" name="specialization" class="form-control">
        </div>

        <div class="mb-3">
            <label class="form-label">Описание</label>
            <textarea name="description" class="form-control" rows="4"></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Опыт (лет)</label>
            <input type="number" name="experience_years" class="form-control" min="0" value="0">
        </div>

        <div class="mb-3">
            <label class="form-label">Фотография</label>
            <input type="file" name="image" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Добавить мастера</button>
    </form>
</div>

<script>
const burger = document.getElementById('burger');
const navMenu = document.getElementById('nav-menu');
burger.addEventListener('click', () => {
    navMenu.classList.toggle('show');
});
</script>

</body>
</html>