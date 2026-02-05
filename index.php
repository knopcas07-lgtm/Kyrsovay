<?php
session_start();
require '../db.php';

// 1. Получаем все товары из базы
$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();

// 2. Получаем данные формы и ошибки из сессии
$form_error = $_SESSION['form_error'] ?? null;
$form_success = $_SESSION['form_success'] ?? null;
$form_data = $_SESSION['form_data'] ?? null;

// Очищаем сообщения после получения
unset($_SESSION['form_error']);
unset($_SESSION['form_success']);
unset($_SESSION['form_data']);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Главная страница магазина</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        /* Стили для слотов времени */
        .time-slot {
            transition: all 0.2s ease;
            border: 2px solid transparent;
            cursor: pointer;
            margin-bottom: 10px;
        }

        .time-slot:hover:not(.disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border-color: #dee2e6;
        }

        .time-slot.selected:not(.disabled) {
            border-color: #198754;
            background-color: rgba(25, 135, 84, 0.05);
            box-shadow: 0 0 0 3px rgba(25, 135, 84, 0.1);
        }

        .time-slot.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            border-color: #dee2e6;
        }

        .time-slot-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 12px 8px;
            border-radius: 8px;
            height: 100%;
        }

        .time-badge {
            font-size: 1.1rem;
            font-weight: 600;
            padding: 8px 16px;
        }

        /* Сетка для слотов времени */
        .time-slots-grid {
            margin: 0 -5px;
        }

        .time-slots-grid > div {
            padding: 0 5px;
        }

        /* Анимации */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .time-slot {
            animation: fadeIn 0.3s ease forwards;
        }

        /* Убедитесь, что контейнеры для времени правильно отображаются */
        .time-slots-container {
            min-height: 100px;
        }

        /* Стили для навигации */
        .navbar {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .card {
            transition: transform 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .modal-header .btn-close {
            filter: invert(1);
        }
        
        /* Стили для алертов */
        .alert-dismissible .btn-close {
            padding: 0.75rem;
        }
        
        /* Стили для спиннера в кнопке */
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }
    </style>
</head>
<body>

<!-- Навигация -->
<nav class="navbar navbar-expand-lg navbar-light bg-white px-4 mb-4 shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold text-primary" href="#">
            <i class="bi bi-shop me-2"></i>Мой Магазин Услуг
        </a>
        
        <div class="d-flex align-items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Если пользователь вошел -->
                <div class="d-flex align-items-center me-3">
                    <div class="me-3">
                        <span class="badge bg-<?= $_SESSION['user_role'] === 'admin' ? 'danger' : 'success' ?> ms-2">
                            <?= $_SESSION['user_role'] === 'admin' ? 'Админ' : 'Пользователь' ?>
                        </span>
                    </div>
                    
                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
                        <!-- Кнопка для администратора -->
                        <a href="admin_panel.php" class="btn btn-outline-danger btn-sm me-2">
                            <i class="bi bi-gear me-1"></i>Панель управления
                        </a>
                    <?php else: ?>
                        <!-- Кнопка для обычного пользователя -->
                        <a href="profile.php" class="btn btn-outline-primary btn-sm me-2">
                            <i class="bi bi-person-circle me-1"></i>Личный кабинет
                        </a>
                    <?php endif; ?>
                    
                    <a href="logout.php" class="btn btn-outline-dark btn-sm" onclick="return confirmLogout()">
                        <i class="bi bi-box-arrow-right me-1"></i>Выйти
                    </a>
                </div>
            <?php else: ?>
                <!-- Если гость -->
                <div>
                    <a href="login.php" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Войти
                    </a>
                    <a href="register.php" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i>Регистрация
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container">
    <h1 class="mb-4 text-center">Каталог услуг</h1>
    
    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <!-- Если картинки нет, ставим заглушку -->
                    <?php $img = $product['image_url'] ?: 'https://via.placeholder.com/300x200/667eea/ffffff?text=' . urlencode($product['title']); ?>
                    <img src="<?= htmlspecialchars($img) ?>" class="card-img-top product-image" alt="<?= htmlspecialchars($product['title']) ?>">
                    
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['title']) ?></h5>
                        <p class="card-text flex-grow-1"><?= htmlspecialchars($product['description']) ?></p>
                        <div class="mt-auto">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary fs-4"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#appointmentModal<?= $product['id'] ?>">
                                    <i class="bi bi-calendar-plus me-1"></i> Записаться
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Модальное окно для записи на услугу -->
            <div class="modal fade" id="appointmentModal<?= $product['id'] ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-calendar-check me-2"></i>
                                Запись на услугу: <?= htmlspecialchars($product['title']) ?>
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Форма записи -->
                            <form id="appointmentForm<?= $product['id'] ?>">
                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <div class="alert alert-warning">
                                        <i class="bi bi-exclamation-triangle me-2"></i>
                                        Для записи необходимо <a href="login.php" class="alert-link">войти в систему</a>.
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Сообщения будут добавляться динамически -->
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-calendar3 me-1"></i> Выберите дату:
                                            </label>
                                            <input type="date" name="date" class="form-control date-selector" 
                                                   data-product-id="<?= $product['id'] ?>"
                                                   min="<?= date('Y-m-d') ?>" 
                                                   value="<?= isset($form_data['date']) && isset($form_data['product_id']) && $form_data['product_id'] == $product['id'] ? htmlspecialchars($form_data['date']) : date('Y-m-d') ?>"
                                                   required>
                                            <small class="text-muted">Минимальная дата: сегодня</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <i class="bi bi-clock me-1"></i> Выберите время:
                                            </label>
                                            <div class="row time-slots-grid time-slots-container" id="timeSlots<?= $product['id'] ?>">
                                                <!-- Слоты времени будут загружены здесь -->
                                                <div class="col-12 text-center py-4">
                                                    <div class="spinner-border text-primary" role="status">
                                                        <span class="visually-hidden">Загрузка...</span>
                                                    </div>
                                                    <p class="mt-2 text-muted">Загрузка свободного времени...</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="alert alert-light border">
                                    <h6 class="fw-bold"><i class="bi bi-info-circle me-2"></i>Информация о услуге:</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p><strong>Услуга:</strong> <?= htmlspecialchars($product['title']) ?></p>
                                            <p><strong>Описание:</strong> <?= htmlspecialchars($product['description']) ?></p>
                                        </div>
                                        <div class="col-md-6">
                                            <p><strong>Цена:</strong> <span class="text-primary fw-bold fs-5"><?= number_format($product['price'], 0, '', ' ') ?> ₽</span></p>
                                            <p class="mb-0"><small class="text-muted">
                                                <i class="bi bi-shield-check me-1"></i>
                                                Каждый временной слот доступен для записи только одного клиента
                                            </small></p>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-lg me-1"></i> Отмена
                            </button>
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <button type="button" class="btn btn-primary" onclick="submitAppointmentForm(<?= $product['id'] ?>)">
                                    <i class="bi bi-check-lg me-1"></i> Подтвердить запись
                                </button>
                            <?php else: ?>
                                <a href="login.php" class="btn btn-primary">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Войти для записи
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (count($products) === 0): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    Услуг пока нет. Администратор может добавить их через панель управления.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Подключение Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Отладочная информация
console.log('Script loaded, user is logged in: <?= isset($_SESSION['user_id']) ? "true" : "false" ?>');
console.log('User role: <?= $_SESSION['user_role'] ?? "guest" ?>');

// Функция для генерации временных слотов с интервалом 2.5 часа (150 минут)
function generateTimeSlots(startTime, endTime, intervalMinutes) {
    const slots = [];
    const [startHour, startMin] = startTime.split(':').map(Number);
    const [endHour, endMin] = endTime.split(':').map(Number);
    
    let currentHour = startHour;
    let currentMin = startMin;
    
    while (currentHour < endHour || (currentHour === endHour && currentMin < endMin)) {
        const timeStr = `${currentHour.toString().padStart(2, '0')}:${currentMin.toString().padStart(2, '0')}`;
        slots.push(timeStr);
        
        // Добавляем интервал
        currentMin += intervalMinutes;
        while (currentMin >= 60) {
            currentHour += 1;
            currentMin -= 60;
        }
    }
    
    return slots;
}

// Все возможные варианты времени с интервалом 2.5 часа (150 минут)
const ALL_TIME_OPTIONS = generateTimeSlots('12:00', '22:00', 150);
console.log('Все временные слоты:', ALL_TIME_OPTIONS);

// Функция для получения ЗАНЯТОГО времени с сервера
async function getBookedTimes(productId, date) {
    try {
        console.log(`Запрос занятого времени: product=${productId}, date=${date}`);
        
        const response = await fetch(`get_booked_times.php?date=${date}&_=${Date.now()}`);
        
        if (!response.ok) {
            console.error('Ошибка сети:', response.status);
            return [];
        }
        
        const data = await response.json();
        console.log('Получены данные:', data);
        
        return data.booked_times || [];
        
    } catch (error) {
        console.error('Ошибка при получении занятого времени:', error);
        return [];
    }
}

// Функция для отображения только СВОБОДНОГО времени
async function loadFreeTimeSlots(productId, selectedDate) {
    console.log(`Загрузка свободных слотов для продукта ${productId} на дату ${selectedDate}`);
    
    const container = document.getElementById(`timeSlots${productId}`);
    const isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
    
    // Показываем загрузку
    container.innerHTML = `
        <div class="col-12 text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Загрузка...</span>
            </div>
            <p class="mt-2 text-muted">Проверка доступности времени...</p>
        </div>
    `;
    
    try {
        // Получаем занятое время
        const bookedTimes = await getBookedTimes(productId, selectedDate);
        console.log('Занятые времена:', bookedTimes);
        
        // Фильтруем: оставляем только НЕзанятое время
        const freeTimes = ALL_TIME_OPTIONS.filter(time => !bookedTimes.includes(time));
        console.log('Свободные времена:', freeTimes);
        
        // Очищаем контейнер
        container.innerHTML = '';
        
        if (freeTimes.length === 0) {
            // Нет свободного времени
            container.innerHTML = `
                <div class="col-12">
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        На выбранную дату все слоты заняты. Выберите другую дату.
                    </div>
                </div>
            `;
            return;
        }
        
        // Создаем слоты только для СВОБОДНОГО времени
        freeTimes.forEach((time, index) => {
            const isPast = checkIfPastTime(selectedDate, time);
            const isDisabled = !isLoggedIn || isPast;
            
            const slotDiv = document.createElement('div');
            slotDiv.className = `col-6 col-md-4 mb-3`;
            
            const inputId = `time_${productId}_${time.replace(':', '')}`;
            
            slotDiv.innerHTML = `
                <div class="time-slot ${isDisabled ? 'disabled' : ''}">
                    <input class="form-check-input visually-hidden" type="radio" 
                           name="time" 
                           id="${inputId}" 
                           value="${time}"
                           ${isDisabled ? 'disabled' : ''}>
                    <label class="form-check-label w-100 h-100" for="${inputId}" style="cursor: ${isDisabled ? 'not-allowed' : 'pointer'}">
                        <div class="time-slot-content text-center p-3 rounded border ${isPast ? 'bg-light' : 'bg-white'}">
                            <div class="time-badge ${isPast ? 'text-muted' : 'text-primary'}">
                                <i class="bi bi-clock me-1"></i> ${time}
                            </div>
                            <div class="mt-1">
                                ${isPast ? 
                                    '<small class="text-danger"><i class="bi bi-exclamation-triangle"></i> Время прошло</small>' : 
                                    '<small class="text-success">Доступно</small>'
                                }
                                ${!isLoggedIn && !isPast ? 
                                    '<div><small class="text-info"><i class="bi bi-person"></i> Требуется вход</small></div>' : 
                                    ''
                                }
                            </div>
                        </div>
                    </label>
                </div>
            `;
            
            // Добавляем обработчик выбора, если слот не отключен
            if (!isDisabled) {
                const radioInput = slotDiv.querySelector('input');
                const label = slotDiv.querySelector('label');
                const timeSlotDiv = slotDiv.querySelector('.time-slot');
                
                radioInput.addEventListener('change', function() {
                    // Убираем выделение у всех слотов
                    container.querySelectorAll('.time-slot').forEach(slot => {
                        slot.classList.remove('selected');
                    });
                    
                    // Добавляем выделение выбранному слоту
                    if (this.checked) {
                        timeSlotDiv.classList.add('selected');
                    }
                });
                
                label.addEventListener('click', function(e) {
                    if (!isDisabled) {
                        radioInput.checked = true;
                        radioInput.dispatchEvent(new Event('change'));
                    }
                });
            }
            
            container.appendChild(slotDiv);
        });
    } catch (error) {
        console.error('Ошибка при загрузке слотов:', error);
        container.innerHTML = `
            <div class="col-12">
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Произошла ошибка при загрузке времени. Пожалуйста, попробуйте позже.
                </div>
            </div>
        `;
    }
}

// Функция для проверки, прошло ли время
function checkIfPastTime(selectedDate, time) {
    const today = new Date().toISOString().split('T')[0];
    
    // Если выбрана сегодняшняя дата
    if (selectedDate === today) {
        const now = new Date();
        const currentHour = now.getHours();
        const currentMinute = now.getMinutes();
        
        const timeParts = time.split(':');
        const hour = parseInt(timeParts[0]);
        const minute = parseInt(timeParts[1]);
        
        // Проверяем, прошло ли выбранное время
        return hour < currentHour || (hour === currentHour && minute <= currentMinute);
    }
    
    return false;
}

// Вспомогательная функция для показа сообщений
function showAlert(modalId, type, message, autoClose = false) {
    const modalBody = document.querySelector(`#${modalId} .modal-body`);
    
    // Удаляем старые алерты
    const oldAlerts = modalBody.querySelectorAll('.alert[role="alert"]');
    oldAlerts.forEach(alert => alert.remove());
    
    // Создаем новый алерт
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.setAttribute('role', 'alert');
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    // Вставляем после формы или в начало
    const form = modalBody.querySelector('form');
    if (form) {
        form.insertBefore(alertDiv, form.firstChild);
    } else {
        modalBody.insertBefore(alertDiv, modalBody.firstChild);
    }
    
    // Автоматическое скрытие через 5 секунд
    if (autoClose) {
        setTimeout(() => {
            if (alertDiv.parentNode) {
                const bsAlert = new bootstrap.Alert(alertDiv);
                bsAlert.close();
            }
        }, 5000000);
    }
}

// Функция отправки формы записи
window.submitAppointmentForm = async function(productId) {
    const form = document.getElementById(`appointmentForm${productId}`);
    const dateInput = form.querySelector('.date-selector');
    const selectedTime = form.querySelector(`input[name="time"]:checked`);
    
    if (!selectedTime) {
        showAlert(`appointmentModal${productId}`, 'warning', 
            '<i class="bi bi-exclamation-triangle me-2"></i>Пожалуйста, выберите время для записи');
        return;
    }
    
    const date = dateInput.value;
    const time = selectedTime.value;
    
    // Валидация времени
    if (checkIfPastTime(date, time)) {
        showAlert(`appointmentModal${productId}`, 'warning', 
            '<i class="bi bi-exclamation-triangle me-2"></i>Нельзя записаться на прошедшее время');
        return;
    }
    
    // Создаем FormData
    const formData = new FormData();
    formData.append('product_id', productId);
    formData.append('date', date);
    formData.append('time', time);
    
    try {
        // Показываем индикатор загрузки
        const submitBtn = document.querySelector(`#appointmentModal${productId} .modal-footer .btn-primary`);
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<div class="spinner-border spinner-border-sm me-2" role="status"></div> Обработка...';
        submitBtn.disabled = true;
        
        console.log('Отправка данных на make_appointments.php:', { productId, date, time });
        
        const response = await fetch('make_appointments.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        console.log('Результат от сервера:', result);
        
        // Восстанавливаем кнопку
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
        
        if (result.success) {
            // Показываем красивое сообщение об успехе
            showAlert(`appointmentModal${productId}`, 'success', 
                `<div>
                    <h5 class="alert-heading"><i class="bi bi-check-circle-fill me-2"></i>Запись успешно создана!</h5>
                    <p>${result.message}</p>
                    <hr>
                    <div class="text-start">
                        <p><strong>Услуга:</strong> ${result.product_title}</p>
                        <p><strong>Дата:</strong> ${result.date}</p>
                        <p><strong>Время:</strong> ${result.time}</p>
                        <p><strong>Стоимость:</strong> ${result.price} ₽</p>
                    </div>
                </div>`,
                true // autoClose
            );
            
            // Обновляем список свободного времени
            await loadFreeTimeSlots(productId, date);
            
            // Автоматически закрываем модальное окно через 5 секунд
            setTimeout(() => {
                const modalElement = document.getElementById(`appointmentModal${productId}`);
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                
                // Перенаправляем в личный кабинет через 1 секунду
                setTimeout(() => {
                    window.location.href = 'profile.php';
                }, 1000);
            }, 50000000);
            
        } else {
            // Показываем сообщение об ошибке
            showAlert(`appointmentModal${productId}`, 'danger', 
                `<div>
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ошибка!</h5>
                    <p>${result.message}</p>
                </div>`
            );
            
            // Обновляем список свободного времени
            await loadFreeTimeSlots(productId, date);
        }
    } catch (error) {
        console.error('Ошибка сети:', error);
        
        // Восстанавливаем кнопку
        const submitBtn = document.querySelector(`#appointmentModal${productId} .modal-footer .btn-primary`);
        submitBtn.innerHTML = '<i class="bi bi-check-lg me-1"></i> Подтвердить запись';
        submitBtn.disabled = false;
        
        showAlert(`appointmentModal${productId}`, 'danger', 
            `<div>
                <h5 class="alert-heading"><i class="bi bi-exclamation-triangle-fill me-2"></i>Ошибка сети!</h5>
                <p>Произошла ошибка при отправке формы. Пожалуйста, проверьте подключение к интернету и попробуйте еще раз.</p>
            </div>`
        );
    }
};

// Обработчик изменения даты
document.addEventListener('DOMContentLoaded', function() {
    // Загружаем время при изменении даты
    document.querySelectorAll('.date-selector').forEach(dateInput => {
        const productId = dateInput.getAttribute('data-product-id');
        
        dateInput.addEventListener('change', async function() {
            const selectedDate = this.value;
            await loadFreeTimeSlots(productId, selectedDate);
        });
    });
    
    // При открытии модального окна загружаем время для текущей даты
    document.querySelectorAll('[data-bs-toggle="modal"]').forEach(button => {
        button.addEventListener('click', async function() {
            const modalId = this.getAttribute('data-bs-target');
            const productId = modalId.replace('#appointmentModal', '');
            const dateInput = document.querySelector(`${modalId} .date-selector`);
            
            if (dateInput) {
                const selectedDate = dateInput.value;
                await loadFreeTimeSlots(productId, selectedDate);
            }
        });
    });
    
    // Функция подтверждения выхода
    window.confirmLogout = function() {
        return confirm('Вы уверены, что хотите выйти?');
    };
    
    // Проверка доступности файлов для отладки
    async function checkFiles() {
        try {
            const response1 = await fetch('get_booked_times.php');
            console.log('get_booked_times.php доступен:', response1.ok);
            
            const response2 = await fetch('make_appointments.php');
            console.log('make_appointments.php доступен:', response2.ok);
        } catch (error) {
            console.error('Ошибка проверки файлов:', error);
        }
    }
    
    // Проверяем доступность файлов
    checkFiles();
});

// Обработчик для модальных окон Bootstrap
document.addEventListener('shown.bs.modal', function(event) {
    const modal = event.target;
    const productId = modal.id.replace('appointmentModal', '');
    const dateInput = modal.querySelector('.date-selector');
    
    if (dateInput) {
        const selectedDate = dateInput.value;
        loadFreeTimeSlots(productId, selectedDate);
    }
});

// Обработчик для скрытия модальных окон (очистка выбранного времени)
document.addEventListener('hidden.bs.modal', function(event) {
    const modal = event.target;
    const container = modal.querySelector('.time-slots-container');
    if (container) {
        // Снимаем выделение с выбранного слота
        container.querySelectorAll('.time-slot.selected').forEach(slot => {
            slot.classList.remove('selected');
        });
        // Снимаем выбор с radio button
        container.querySelectorAll('input[type="radio"]:checked').forEach(input => {
            input.checked = false;
        });
    }
});
</script>

</body>
</html>
