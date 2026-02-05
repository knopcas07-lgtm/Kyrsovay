<?php
require 'check_admin.php';
require '../db.php';

$sql = "
    SELECT a.id as appointment_id, a.date, a.time, a.status, a.cancel_reason,
           u.email as client_email, p.title as service_title
    FROM appointments a
    JOIN users u ON a.user_id = u.id
    JOIN products p ON a.product_id = p.id
    ORDER BY a.date DESC, a.time DESC
";
$appointments = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<title>Админка: Записи</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f8f9fa; }
.appointment-card { background: white; border-radius: 10px; padding: 15px; margin-bottom: 15px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);}
.appointment-header { display:flex; justify-content: space-between; align-items:center; margin-bottom:8px;}
.status-badge { padding:3px 10px; border-radius:12px; font-size:0.85em;}
</style>
</head>
<body>
<div class="container py-4">
<h3 class="mb-3">Все записи</h3>

<a href="admin_panel.php" class="btn btn-secondary mb-3">← Назад</a>

<?php foreach ($appointments as $a):
    // Статус
    switch($a['status']){
        case 'pending': $status_class='warning'; $status_text='Ожидается'; break;
        case 'completed': $status_class='success'; $status_text='Завершена'; break;
        case 'cancelled': $status_class='danger'; $status_text='Отменена'; break;
        default: $status_class='secondary'; $status_text='Неизвестно';
    }

    $formatted_date = $a['date'] ? date('d.m.Y', strtotime($a['date'])) : 'Не указана';
    $formatted_time = $a['time'] ? substr($a['time'],0,5) : '';
?>
<div class="appointment-card">
    <div class="appointment-header">
        <div>
            <strong>#<?= $a['appointment_id'] ?></strong> — <?= $formatted_date ?> <?= $formatted_time ?> — <?= htmlspecialchars($a['client_email']) ?>
        </div>
        <span class="badge bg-<?= $status_class ?> status-badge"><?= $status_text ?></span>
    </div>
    <div><?= htmlspecialchars($a['service_title']) ?></div>

    <?php if ($a['status'] === 'pending'): ?>
        <!-- Кнопка отмены с модальным окном -->
        <button class="btn btn-outline-danger btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#cancelModal<?= $a['appointment_id'] ?>">Отменить</button>

        <div class="modal fade" id="cancelModal<?= $a['appointment_id'] ?>" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="post" action="cancel_appointment_admin.php?id=<?= $a['appointment_id'] ?>">
                        <div class="modal-header">
                            <h5 class="modal-title">Отмена записи #<?= $a['appointment_id'] ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Причина отмены</label>
                                <input type="text" name="cancel_reason" class="form-control" required placeholder="Укажите причину отмены">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="submit" class="btn btn-danger">Отменить запись</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php elseif ($a['status'] === 'cancelled' && $a['cancel_reason']): ?>
        <div class="text-danger mt-1">Причина отмены: <?= htmlspecialchars($a['cancel_reason']) ?></div>
    <?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>