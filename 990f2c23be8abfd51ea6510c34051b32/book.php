<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Войдите в аккаунт");
}

$product_id = $_GET['product_id'] ?? null;

// получаем мастеров
$masters = $pdo->query("SELECT * FROM masters")->fetchAll();

// фиксированные слоты
$timeSlots = ['12:00', '14:30', '17:00', '19:30'];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Запись</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

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
    max-width: 600px;
    margin: 0 auto;
    padding: 15px;
}

/* карточка */
.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Календарь */
.flatpickr-calendar {
    border-radius: 14px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    border: none;
}

.flatpickr-day.selected {
    background: #007bff;
    border-color: #007bff;
}

.flatpickr-day.today {
    border-color: #007bff;
}

/* Блок времени */
#timeWrapper {
    opacity: 0;
    transform: translateY(15px);
    transition: all 0.3s ease;
}

#timeWrapper.show {
    opacity: 1;
    transform: translateY(0);
}

/* Кнопки времени */
.time-btn {
    padding: 10px 16px;
    border: 1px solid #007bff;
    border-radius: 10px;
    background: #fff;
    cursor: pointer;
    transition: 0.25s;
    font-weight: 500;
}

.time-btn:hover {
    background: #007bff;
    color: #fff;
}

.time-btn.active {
    background: #007bff;
    color: #fff;
}

.time-btn.disabled {
    background: #eee;
    border-color: #ccc;
    color: #aaa;
    cursor: not-allowed;
}

/* 📱 мобилка */
@media (max-width: 768px) {
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
    
    <a href="profile.php" class="btn btn-light mb-2 d-inline-block back-btn">← Назад</a>

    <div class="container-fluid mb-3 px-0">
    
        <h1 class="text-center">Запись</h1>
    
    </div>

<form action="save_appointment.php" method="POST" class="card p-3 shadow-sm">
<input type="hidden" name="product_id" value="<?= $product_id ?>">

<!-- ДАТА -->
<div class="mb-3">
<label>Дата</label>
<input type="text" name="date" id="dateInput" class="form-control" placeholder="Выберите дату" required>
</div>

<!-- ВРЕМЯ -->
<div id="timeWrapper" class="mb-3">
<label>Время</label>
<div id="timeButtons" class="d-flex flex-wrap gap-2"></div>
<input type="hidden" name="time" id="timeInput" required>
</div>

<!-- МАСТЕР -->
<div class="mb-3">
<label>Мастер</label>
<select name="master_id" id="masterSelect" class="form-select" required>
<option value="">Сначала выберите время</option>
</select>
</div>

<button class="btn btn-primary">Записаться</button>

</form>

</div>

<script>

const timeSlots = ['12:00', '14:30', '17:00', '19:30'];

const timeContainer = document.getElementById('timeButtons');
const timeInput = document.getElementById('timeInput');
const timeWrapper = document.getElementById('timeWrapper');
const masterSelect = document.getElementById('masterSelect');

flatpickr("#dateInput", {
    minDate: "today",
    dateFormat: "Y-m-d",

    onChange: function(selectedDates, dateStr) {

        fetch('get_busy.php?date=' + dateStr)
        .then(res => res.json())
        .then(data => {

            timeContainer.innerHTML = '';
            timeInput.value = '';
            masterSelect.innerHTML = '<option value="">Сначала выберите время</option>';

            timeSlots.forEach((time, index) => {

                let btn = document.createElement('button');
                btn.type = 'button';
                btn.textContent = time;
                btn.classList.add('time-btn');

                if (data.busyTimes.includes(time)) {
                    btn.classList.add('disabled');
                    btn.disabled = true;
                }

                btn.addEventListener('click', () => {

                    document.querySelectorAll('.time-btn').forEach(b => b.classList.remove('active'));
                    btn.classList.add('active');

                    timeInput.value = time;

                    fetch(`get_masters.php?date=${dateStr}&time=${time}`)
                    .then(res => res.json())
                    .then(data => {

                        masterSelect.innerHTML = '<option value="">Выберите мастера</option>';

                        data.forEach(m => {
                            let opt = document.createElement('option');
                            opt.value = m.id;
                            opt.textContent = m.name;
                            masterSelect.appendChild(opt);
                        });

                        if (data.length === 0) {
                            masterSelect.innerHTML = '<option>Нет свободных мастеров</option>';
                        }

                    });

                });

                btn.style.opacity = 0;
                btn.style.transform = 'translateY(10px)';
                btn.style.transition = '0.3s';

                setTimeout(() => {
                    btn.style.opacity = 1;
                    btn.style.transform = 'translateY(0)';
                }, index * 80);

                timeContainer.appendChild(btn);
            });

            timeWrapper.classList.add('show');

        });
    }
});

</script>

</body>
</html>