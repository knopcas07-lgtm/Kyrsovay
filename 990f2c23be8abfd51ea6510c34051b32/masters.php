<?php
session_start();
require '../db.php';

// Получаем мастеров
try {
    $stmt = $pdo->query("SELECT * FROM masters ORDER BY id DESC");
    $masters = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Ошибка: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Мастера</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* Общие */
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* HEADER */
        .header {
            background-color: #fff;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            position: sticky;
            top: 0;
            z-index: 999;
            padding: 10px 15px;
        }

        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
            gap: 15px;
        }

        .logo a {
            font-weight: 700;
            color: #007bff;
            text-decoration: none;
            font-size: 1.5rem;
        }

        .nav ul {
            display: flex;
            align-items: center; /* 🔥 ключевое */
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
        
            height: 38px; /* одинаковая высота */
            padding: 0 14px;
        
            text-decoration: none;
            border-radius: 6px;
            color: #333;
            font-size: 14px;
            line-height: 1;
        }
        
        /* кнопки */
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

        .burger {
            display: none;
            flex-direction: column;
            cursor: pointer;
            gap: 4px;
        }

        .burger span {
            display: block;
            height: 3px;
            width: 25px;
            background-color: #333;
            border-radius: 2px;
        }

        /* Контейнер */
        .container {
            max-width: 1200px;
            padding-left: 15px;
            padding-right: 15px;
            margin: 0 auto;
        }

        /* Карточки */
        .master-card {
            transition: 0.3s;
            border-radius: 12px;
        }

        .master-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .master-photo {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 10px;
        }

        .master-info {
            font-size: 0.9rem;
            color: #555;
        }

        /* Назад */
        .back-link {
            text-decoration: none;
            color: #000;
            font-size: 16px;
            margin-bottom: 10px;
            display: inline-block;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        /* 📱 Мобильная адаптация */
        @media (max-width: 768px) {
            
            .back-btn {
                display: block;
                width: fit-content;
                margin-left: 0 !important;
                margin-right: auto;
                text-align: left;
            }

            h1 {
                font-size: 20px;
                text-align: left;
            }

            .row {
                margin: 0 -7.5px;
            }

            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
                padding: 0 7.5px;
            }

            .master-card {
                margin-bottom: 12px;
            }

            .btn {
                width: 100%;
            }

            .container {
                padding-left: 10px;
                padding-right: 10px;
            }

            .burger {
                display: flex;
            }

            .nav {
                position: absolute;
                top: 60px;
                right: 0;
                background: #fff;
                width: 200px;
                transform: translateX(100%);
                transition: 0.3s;
                box-shadow: 0 2px 8px rgba(0,0,0,0.2);
                border-radius: 0 0 8px 8px;
            }

            .nav ul {
                flex-direction: column;
                padding: 10px;
            }

            .nav.show {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body>

    <!-- HEADER -->
    <?php require 'header.php'; ?>

    <!-- Назад + заголовок -->
    <div class="container mt-3">
        <a href="index.php" class="btn btn-light mb-2 d-inline-block back-btn">← Назад</a>
        <h1 class="mb-3">Наши мастера</h1>
    </div>

    <!-- Контент -->
    <div class="container">
        <div class="row">

            <?php if (count($masters) === 0): ?>

                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Мастеров пока нет.
                    </div>
                </div>

            <?php else: ?>

                <?php foreach ($masters as $master): ?>

                    <div class="col-md-4 mb-4">
                        <div class="card master-card h-100 p-2">

                            <?php
                            $photo = $master['image_url'] ?: 'https://via.placeholder.com/300x200?text=Фото';
                            ?>

                            <img 
                                src="<?= htmlspecialchars($photo) ?>" 
                                alt="<?= htmlspecialchars($master['name']) ?>" 
                                class="master-photo mb-2"
                            >

                            <h5><?= htmlspecialchars($master['name']) ?></h5>

                            <div class="master-info mb-2">
                                <?= htmlspecialchars($master['description'] ?? 'Без описания') ?><br>
                                <strong>Специализация:</strong> <?= htmlspecialchars($master['specialization'] ?? 'Не указана') ?><br>
                                Стаж: <?= intval($master['experience_years'] ?? 0) ?> лет
                            </div>

                            <a href="portfolio.php?master_id=<?= $master['id'] ?>" class="btn btn-primary w-100">
                                <i class="bi bi-card-image me-1"></i> Работы
                            </a>

                        </div>
                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Бургер-меню
        const burger = document.getElementById('burger');
        const navMenu = document.getElementById('nav-menu');

        if (burger && navMenu) {
            burger.addEventListener('click', () => {
                navMenu.classList.toggle('show');
            });
        }
    </script>

</body>
</html>