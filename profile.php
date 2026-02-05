<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// --- Автоматическое завершение прошедших записей ---
$stmt = $pdo->prepare("
    UPDATE appointments
    SET status = 'completed'
    WHERE user_id = ? 
      AND status = 'pending' 
      AND CONCAT(date, ' ', time) < NOW()
");
$stmt->execute([$user_id]);

// --- Получение всех записей пользователя ---
$sql = "
    SELECT 
        appointments.id as appointment_id,
        appointments.date,
        appointments.time,
        appointments.status,
        appointments.cancel_reason,
        appointments.created_at,
        products.title as service_title,
        products.price as service_price
    FROM appointments 
    JOIN products ON appointments.product_id = products.id 
    WHERE appointments.user_id = ? 
    ORDER BY appointments.date DESC, appointments.time DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$my_appointments = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Мои записи</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f8f9fa; }
.appointment-item { background: white; border: 1px solid #dee2e6; border-radius: 8px; padding: 15px; margin-bottom: 10px; }
.appointment-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
.appointment-id { font-weight: bold; color: #495057; }
.appointment-date { color: #6c757d; font-size: 0.9em; }
.service-title { font-weight: 500; margin-bottom: 5px; }
.service-price { color: #0d6efd; font-weight: bold; }
.status-badge { padding: 3px 10px; border-radius: 12px; font-size: 0.85em; }
.empty-state { text-align: center; padding: 60px 20px; color: #6c757d; }
.cancel-reason { font-size:0.85em; color:#dc3545; margin-top:5px; }
.btn-check:disabled + label { opacity: 0.6; pointer-events: none; }
</style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Мои записи</a>
        <div>
            <span class="me-3"><?= htmlspecialchars($_SESSION['user_name'] ?? 'Пользователь') ?></span>
            <a href="index.php" class="btn btn-outline-secondary btn-sm me-2">Главная</a>
            <a href="logout.php" class="btn btn-outline-dark btn-sm">Выйти</a>
        </div>
    </div>
</nav>

<div class="container">
<h5 class="mb-3">Записи на услуги</h5>

<?php if (count($my_appointments) > 0): ?>
<div class="mb-3">
    <small class="text-muted">Найдено записей: <?= count($my_appointments) ?></small>
</div>

<?php foreach ($my_appointments as $appointment): ?>
<?php
    // Статус и цвет
    $status_class = 'secondary';
    $status_text = 'Неизвестно';
    if ($appointment['status'] == 'pending') { $status_class = 'warning'; $status_text = 'Ожидается'; } 
    if ($appointment['status'] == 'completed') { $status_class = 'success'; $status_text = 'Завершено'; }
    if ($appointment['status'] == 'cancelled') { $status_class = 'danger'; $status_text = 'Отменена'; }

    $formatted_date = !empty($appointment['date']) && $appointment['date'] != '0000-00-00' ? date('d.m.Y', strtotime($appointment['date'])) : 'Не указана';
    $formatted_time = !empty($appointment['time']) && $appointment['time'] != '00:00:00' ? substr($appointment['time'],0,5) : '';
?>
<div class="appointment-item">
    <div class="appointment-header">
        <div>
            <span class="appointment-id">Запись #<?= $appointment['appointment_id'] ?></span>
            <span class="appointment-date ms-2"><?= $formatted_date ?> <?php if($formatted_time) echo "в $formatted_time"; ?></span>
        </div>
        <span class="badge bg-<?= $status_class ?> status-badge"><?= $status_text ?></span>
    </div>

    <div class="service-title"><?= htmlspecialchars($appointment['service_title']) ?></div>

    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="service-price"><?= number_format($appointment['service_price'],0,'',' ') ?> ₽</div>

            <?php if($appointment['status']==='pending'): ?>
            <a href="cancel_appointment.php?id=<?= $appointment['appointment_id'] ?>" class="btn btn-outline-danger btn-sm mt-2 cancel-btn" data-id="<?= $appointment['appointment_id'] ?>">Отменить</a>
            
            <button type="button" class="btn btn-outline-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#rescheduleModal<?= $appointment['appointment_id'] ?>">Перенести</button>

            <div class="modal fade" id="rescheduleModal<?= $appointment['appointment_id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" action="reschedule.php?id=<?= $appointment['appointment_id'] ?>" class="reschedule-form" data-id="<?= $appointment['appointment_id'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Перенос записи #<?= $appointment['appointment_id'] ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Новая дата</label>
                                    <input type="date" class="form-control reschedule-date" name="date" required 
                                           value="<?= $appointment['date'] ?: date('Y-m-d') ?>" 
                                           min="<?= date('Y-m-d') ?>"
                                           data-appointment-id="<?= $appointment['appointment_id'] ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Выберите время</label>
                                    <div id="timeSlots<?= $appointment['appointment_id'] ?>" class="d-flex flex-wrap gap-2"></div>
                                </div>
                                <p class="text-muted">Выберите новую дату и время для вашей записи.</p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                                <button type="submit" class="btn btn-primary">Сохранить изменения</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if($appointment['status']==='cancelled' && $appointment['cancel_reason']): ?>
            <div class="cancel-reason mt-2">Причина отмены: <?= htmlspecialchars($appointment['cancel_reason']) ?></div>
            <?php endif; ?>

        </div>
        <small class="text-muted">Создано: <?= date('d.m.Y H:i', strtotime($appointment['created_at'])) ?></small>
    </div>
</div>
<?php endforeach; ?>

<?php else: ?>
<div class="empty-state">
    <p class="mb-3">У вас пока нет записей</p>
    <a href="index.php" class="btn btn-primary">Перейти в каталог</a>
</div>
<?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
const TIME_OPTIONS = ["12:00","14:30","17:00","19:30"];

async function loadAvailableTimes(appointmentId, date) {
    const container = document.getElementById(`timeSlots${appointmentId}`);
    container.innerHTML = 'Загрузка...';

    try {
        const res = await fetch(`get_booked_times.php?date=${date}&ignore_id=${appointmentId}`);
        const data = await res.json();
        const booked = data.booked_times || [];
        container.innerHTML = '';

        TIME_OPTIONS.forEach(time => {
            const slotId = `time_${appointmentId}_${time.replace(':','')}`;
            const radio = document.createElement('input');
            radio.type = 'radio';
            radio.name = 'time';
            radio.value = time;
            radio.id = slotId;
            radio.classList.add('btn-check');
            radio.disabled = booked.includes(time);

            const label = document.createElement('label');
            label.className = `btn btn-outline-primary ${booked.includes(time)?'disabled':''}`;
            label.htmlFor = slotId;
            label.textContent = time;

            container.appendChild(radio);
            container.appendChild(label);
        });
    } catch (e) {
        console.error(e);
        container.innerHTML = '<span class="text-danger">Ошибка загрузки времени</span>';
    }
}

document.querySelectorAll('.reschedule-date').forEach(input => {
    const appointmentId = input.dataset.appointmentId;
    loadAvailableTimes(appointmentId, input.value);
    input.addEventListener('change', () => loadAvailableTimes(appointmentId, input.value));
});

document.querySelectorAll('[data-bs-toggle="modal"]').forEach(btn => {
    btn.addEventListener('click', () => {
        const modal = document.querySelector(btn.dataset.bsTarget);
        const dateInput = modal.querySelector('.reschedule-date');
        loadAvailableTimes(dateInput.dataset.appointmentId, dateInput.value);
    });
});
</script>

</body>
</html>