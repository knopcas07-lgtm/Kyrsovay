<?php
session_start();
require '../db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем записи
$stmt = $pdo->prepare("
    SELECT 
        a.*, 
        p.title AS product_title, 
        p.price AS product_price,
        m.name AS master_name
    FROM appointments a
    JOIN products p ON a.product_id = p.id
    JOIN masters m ON a.master_id = m.id
    WHERE a.user_id = :user_id
    ORDER BY a.date ASC, a.time ASC
");
$stmt->execute(['user_id' => $user_id]);
$appointments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Можно ли отменить
function canCancel($date, $time) {
    return (strtotime("$date $time") - time()) > 86400;
}

// Статус
function getStatusLabel($status) {
    switch ($status) {
        case 'completed': return ['text' => 'Завершено', 'class' => 'success'];
        case 'pending': return ['text' => 'Ожидается', 'class' => 'warning'];
        case 'cancelled': return ['text' => 'Отменено', 'class' => 'danger'];
        default: return ['text' => 'Неизвестно', 'class' => 'secondary'];
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Мои записи</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* Общие */
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

/* Контейнер */
.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

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

/* Карточки */
.card {
    border-radius: 12px;
    transition: 0.3s;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

/* Статус */
.status-badge {
    font-size: 0.9rem;
}

/* 📱 Мобильная версия */
@media (max-width: 768px) {

    h1 {
        font-size: 20px;
    }

    .container {
        padding: 10px;
    }

    .row {
        margin: 0;
    }

    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 0;
    }

    .card {
        margin-bottom: 10px;
    }
    
    .main-container {
        padding: 10px;
    }
    
    .back-btn {
        display: block;
        width: fit-content;
        margin-left: 0 !important;
        margin-right: auto;
        text-align: left;
    }

    .form-control {
        font-size: 14px;
    }
}

</style>
</head>

<body>

<!-- HEADER -->
<?php require 'header.php'; ?>

<!-- КНОПКА НАЗАД -->
<div class="container mt-2 px-2">
    <a href="index.php" class="btn btn-light mb-2 d-inline-block back-btn">← Назад</a>

    <h1 class="mb-3 text-start">Мои записи</h1>
</div>

<div class="container">

    <?php if (count($appointments) === 0): ?>
        <div class="alert alert-info text-center">
            У вас пока нет записей. <a href="index.php">Запишитесь на услугу</a>.
        </div>
    <?php else: ?>

        <div class="row">
            <?php foreach ($appointments as $appt): 
                $status = getStatusLabel($appt['status']);
                $canCancel = canCancel($appt['date'], $appt['time']);
            ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 p-3">

                        <h5><?= htmlspecialchars($appt['product_title']) ?></h5>

                        <p><strong>Дата:</strong> <?= htmlspecialchars($appt['date']) ?></p>
                        <p><strong>Время:</strong> <?= htmlspecialchars($appt['time']) ?></p>
                        <p><strong>Мастер:</strong> <?= htmlspecialchars($appt['master_name']) ?></p>
                        
                        <span class="badge bg-<?= $status['class'] ?> status-badge">
                            <?= $status['text'] ?>
                        </span>

                        <div class="mt-3 d-flex flex-wrap gap-2">

                            <?php if ($appt['status'] === 'pending'): ?>

                                <a href="update_appointment.php?id=<?= $appt['id'] ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                   Перенести
                                </a>

                                <?php if ($canCancel): ?>
                                    <form action="cancel_appointment.php" method="POST" 
                                          onsubmit="return confirm('Вы уверены?');">
                                    
                                        <input type="hidden" name="id" value="<?= $appt['id'] ?>">
                                    
                                        <button class="btn btn-sm btn-outline-danger">
                                            Отменить
                                        </button>
                                    
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-outline-secondary" disabled>
                                        Отменить
                                    </button>
                                <?php endif; ?>

                            <?php endif; ?>

                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    <?php endif; ?>
    
    <a href="change_password.php" class="btn btn-outline-secondary mb-2 ms-2">
    Сменить пароль
    </a>

</div>

</body>
</html>