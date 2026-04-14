<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_GET['id'] ?? null;

$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);
$appt = $stmt->fetch();

if (!$appt) {
    die("Запись не найдена");
}

$timeSlots = ['12:00', '14:30', '17:00', '19:30'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Перенос записи</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

/* HEADER */
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
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

/* BODY */
body {
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

/* КОНТЕЙНЕР КАК В ПРИМЕРЕ */
.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

/* ЦЕНТРОВКА ЗАГОЛОВКА */
.page-title {
    text-align: center;
    font-weight: 700;
}

/* BACK BUTTON */
.back-btn {
    display: inline-block;
    margin-bottom: 10px;
    text-decoration: none;
    color: #333;
}

.back-btn:hover {
    text-decoration: underline;
}

/* КАРТОЧКА */
.card-box {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    padding: 20px;
}

/* TIME BUTTONS */
.time-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
}

.time-btn {
    padding: 10px 16px;
    border-radius: 10px;
    border: 1px solid #007bff;
    background: #fff;
    cursor: pointer;
    transition: 0.2s;
}

.time-btn:hover {
    background: #007bff;
    color: #fff;
}

.time-btn.active {
    background: #007bff;
    color: #fff;
}

/* MOBILE */
@media (max-width: 768px) {
    .card-box {
        padding: 15px;
    }

    .time-btn {
        width: 100%;
        text-align: center;
    }
}

</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="container mt-3">

    <a href="javascript:history.back()" class="back-btn">← Назад</a>

    <h1 class="page-title mb-3">Перенос записи</h1>

</div>

<div class="container">

    <div class="row justify-content-center">
        <div class="col-12 col-md-6">

            <div class="card-box">

                <form action="save_update_appointment.php" method="POST">

                    <input type="hidden" name="id" value="<?= $appt['id'] ?>">

                    <!-- ДАТА -->
                    <div class="mb-3">
                        <label class="form-label">Дата</label>
                        <input type="date"
                               name="date"
                               class="form-control"
                               value="<?= htmlspecialchars($appt['date']) ?>"
                               required>
                    </div>

                    <!-- ВРЕМЯ -->
                    <div class="mb-3">
                        <label class="form-label">Время</label>

                        <div class="time-grid">
                            <?php foreach ($timeSlots as $t): ?>
                                <button type="button"
                                        class="time-btn <?= $appt['time'] === $t ? 'active' : '' ?>"
                                        data-time="<?= $t ?>">
                                    <?= $t ?>
                                </button>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="time"
                               id="timeInput"
                               value="<?= $appt['time'] ?>">
                    </div>

                    <button class="btn btn-success w-100">
                        Сохранить изменения
                    </button>

                </form>

            </div>

        </div>
    </div>

</div>

<script>
const buttons = document.querySelectorAll('.time-btn');
const timeInput = document.getElementById('timeInput');

buttons.forEach(btn => {
    btn.addEventListener('click', () => {
        buttons.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        timeInput.value = btn.dataset.time;
    });
});
</script>

</body>
</html>