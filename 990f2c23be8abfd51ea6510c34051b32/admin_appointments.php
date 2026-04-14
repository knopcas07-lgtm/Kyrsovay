<?php
require 'check_admin.php';
require '../db.php';

$data = $pdo->query("
SELECT a.*, 
       u.username AS user_name, 
       m.name AS master_name, 
       p.title AS product_title
FROM appointments a
LEFT JOIN users u ON a.user_id = u.id
LEFT JOIN masters m ON a.master_id = m.id
LEFT JOIN products p ON a.product_id = p.id
ORDER BY 
    CASE WHEN a.date = CURDATE() THEN 0 ELSE 1 END,
    a.date DESC,
    a.time DESC
")->fetchAll(PDO::FETCH_ASSOC);

function statusRu($status) {
    return match($status) {
        'pending' => 'Ожидает',
        'confirmed' => 'Подтверждено',
        'completed' => 'Завершено',
        'cancelled' => 'Отменено',
        default => 'Неизвестно'
    };
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админ - записи</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* ===== RESET ===== */
html, body {
    margin: 0;
    padding: 0;
    background: #f8f9fa;
}

/* ===== HEADER FIX ===== */
.header {
    background: #fff;
    width: 100%;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 10px 0;
}

.header-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* ===== GLOBAL ALIGNMENT ===== */
.page-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 30px 15px 20px; /* 🔥 единый отступ под header */
}

/* ===== GRID ===== */
.row {
    margin-left: -10px;
    margin-right: -10px;
}

.col-md-4 {
    padding: 10px;
}

/* ===== CARD ===== */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 12px;
    font-size: 14px;
    height: 100%;
}

/* ===== STATUS ===== */
.status {
    font-weight: 600;
}

/* ===== MOBILE ===== */
@media (max-width: 768px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
    }
}

</style>
</head>

<body>

<?php include 'header.php'; ?>

<div class="page-wrapper">

    <a href="index.php" class="btn btn-light mb-3">← Назад</a>

    <h3 class="mb-4">Все записи</h3>

    <div class="row">

        <?php foreach ($data as $a): ?>

            <div class="col-md-4">

                <div class="card">

                    <div><b>Пользователь:</b> <?= htmlspecialchars($a['user_name'] ?? '-') ?></div>
                    <div><b>Мастер:</b> <?= htmlspecialchars($a['master_name'] ?? '-') ?></div>
                    <div><b>Услуга:</b> <?= htmlspecialchars($a['product_title'] ?? '-') ?></div>

                    <hr>

                    <div><b>Дата:</b> <?= $a['date'] ?></div>
                    <div><b>Время:</b> <?= $a['time'] ?></div>

                    <div class="mt-2 status">
                        Статус: <?= statusRu($a['status']) ?>
                    </div>

                    <?php if ($a['status'] !== 'cancelled'): ?>

                        <form method="POST" action="update_status.php" class="mt-2">

                            <input type="hidden" name="id" value="<?= $a['id'] ?>">

                            <select name="status" class="form-select form-select-sm">
                                <option value="pending">Ожидает</option>
                                <option value="confirmed">Подтверждено</option>
                                <option value="completed">Завершено</option>
                                <option value="cancelled">Отменено</option>
                            </select>

                            <button class="btn btn-primary btn-sm mt-2 w-100">
                                Сохранить
                            </button>

                        </form>

                    <?php else: ?>

                        <div class="text-danger mt-2">
                            Отменено
                        </div>

                    <?php endif; ?>

                </div>

            </div>

        <?php endforeach; ?>

    </div>

</div>

</body>
</html>