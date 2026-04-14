<?php
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

$uploadDir = 'uploads/';
$error = '';
$success = '';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Загрузка фото только для админа
if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
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
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Портфолио мастера</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { background-color: #f8f9fa; overflow-x: hidden; margin: 0; padding: 0; }
.card { border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: 0.3s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }
.card img { width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
.btn-primary { border-radius: 8px; }
h1, h2 { font-weight: 700; }
.container { max-width: 1200px; padding-left: 15px; padding-right: 15px; margin: 0 auto; }

/* Header */
.header { background-color: #fff; width: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; padding: 10px 0; }
.header-container { display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }
.logo a { font-weight: 700; color: #007bff; text-decoration: none; font-size: 1.5rem; }
.nav ul { display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; }
.nav ul li { display: flex; align-items: center; }
.nav ul li a { display: flex; align-items: center; justify-content: center; height: 38px; padding: 0 14px; text-decoration: none; border-radius: 6px; color: #333; font-size: 14px; line-height: 1; }
.nav ul li a.btn { background-color: #007bff; color: #fff; }
.nav ul li a.btn-outline { border: 1px solid #007bff; color: #007bff; }
.nav ul li a:hover { opacity: 0.8; }
.burger { display: none; flex-direction: column; cursor: pointer; gap: 4px; }
.burger span { display: block; height: 3px; width: 25px; background-color: #333; border-radius: 2px; }

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

/* Адаптация портфолио */
@media (max-width: 768px) {
    .portfolio-img { height: 130px; }

    .back-btn {
        display: block;
        width: fit-content;
        margin-left: 0 !important;
        margin-right: auto;
        text-align: left;
    }
}
</style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container mt-4">

    <!-- Кнопка назад -->
    <div class="mb-3 text-start">
        <a href="index.php" class="btn btn-light mb-2 d-inline-block back-btn">← Назад</a>
    </div>
    
    <!-- Имя мастера -->
    <h3 class="mb-4">Мастер: <?= htmlspecialchars($master['name']) ?></h3>

    <?php if($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>
    <?php if($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Форма загрузки только для админа -->
    <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
    <form action="" method="POST" enctype="multipart/form-data" class="mb-4">
        <div class="mb-3">
            <label class="form-label">Фотографии работ</label>
            <input type="file" name="images[]" class="form-control" accept=".jpg,.jpeg,.png,.gif" multiple required>
            <small class="text-muted">Можно выбрать несколько файлов одновременно. Форматы: JPG, PNG, GIF</small>
        </div>
        <button type="submit" class="btn btn-primary">Загрузить фото</button>
    </form>
    <?php endif; ?>

    <!-- Просмотр портфолио -->
    <div class="row g-3">
        <?php if(count($portfolio_items) > 0): ?>
            <?php foreach($portfolio_items as $item): ?>
                <div class="col-6 col-sm-4 col-md-3">
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