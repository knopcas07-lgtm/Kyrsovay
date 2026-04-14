<?php
// Самая первая строчка — вызов охраны!
require 'check_admin.php'; 
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8" name="viewport" content="width=device-width, initial-scale=1">
    <title>Админка</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-btn {
            margin-right: 10px;
            margin-bottom: 10px;
            min-width: 180px;
        }
        
        /* 📱 АДАПТАЦИЯ ДЛЯ ТЕЛЕФОНА */
        @media (max-width: 768px) {
            body {
                padding: 15px !important;
            }
        
            .admin-btn {
                width: 100%;        /* кнопка на всю ширину */
                margin-right: 0;    /* убираем сдвиг */
                font-size: 14px;    /* уменьшаем текст */
                padding: 10px;
            }
        
            h1 {
                font-size: 22px;
            }
        
            .alert {
                padding: 15px;
            }
        }
    </style>
</head>
<body class="p-5">
    <div class="container">
        <div class="alert alert-success">
            <h1>Панель Администратора</h1>
            <p>Здесь вы будете управлять: <?php echo "Салоном красоты"; ?></p>
        </div>
        
        <!-- Кнопки управления -->
        <div class="d-flex flex-wrap mb-3">
            <a href="index.php" class="btn btn-primary admin-btn">
                <i class="bi bi-house-door me-1"></i> На главную
            </a>
            <a href="add_item.php" class="btn btn-success admin-btn">
                <i class="bi bi-plus-lg me-1"></i> Добавить услугу
            </a>
            
            <a href="admin_appointments.php" class="btn btn-primary admin-btn">
                <i class="bi bi-calendar-check me-1"></i> Управление записями
            </a>
            
            <a href="add_master.php" class="btn btn-success admin-btn">
                <i class="bi bi-plus-lg me-1"></i> Добавить мастера
            </a>
        </div>
    </div>

    <!-- Подключаем иконки Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>