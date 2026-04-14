<?php 
session_start();

require '../db.php';

// Получаем все товары/услуги и мастеров
$products = $pdo->query("SELECT * FROM products ORDER BY id DESC")->fetchAll();
$masters = $pdo->query("SELECT * FROM masters ORDER BY id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Каталог услуг и мастеров</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">

<style>
body { background-color: #f8f9fa; overflow-x: hidden; margin: 0; padding: 0; }

.card { border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.05); transition: 0.3s; }
.card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

.card img { border-radius: 10px 10px 0 0; width: 100%; height: 200px; object-fit: cover; }

.btn-primary { border-radius: 8px; }

h1, h2 { font-weight: 700; }

.container { max-width: 1200px; padding-left: 15px; padding-right: 15px; margin: 0 auto; }

/* Header */
.header { background-color: #fff; width: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.05); position: sticky; top: 0; z-index: 999; padding: 10px 0; }

.header-container { display: flex; justify-content: space-between; align-items: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 15px; }

.logo a { font-weight: 700; color: #007bff; text-decoration: none; font-size: 1.5rem; }

.nav ul { display: flex; align-items: center; gap: 10px; list-style: none; margin: 0; padding: 0; }

.nav ul li { display: flex; align-items: center; }

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

.nav ul li a.btn { background-color: #007bff; color: #fff; }
.nav ul li a.btn-outline { border: 1px solid #007bff; color: #007bff; }
.nav ul li a:hover { opacity: 0.8; }

/* поиск */
.search-wrapper { display: flex; gap: 5px; margin-bottom: 20px; }

.search-wrapper input { flex: 1; }

@media (max-width: 768px) { 
    .card img { height: 180px; } 
    h1,h2 { font-size: 24px; } 
}

@media (max-width: 576px) {
    #searchBtn { width: 15%; }
    .card img { height: 160px; }
    .card { margin-bottom: 15px; }
}
</style>
</head>

<body>

<?php require 'header.php'; ?>

<div class="container mt-4">

    <!-- ПОИСК -->
    <div class="container mb-3 p-3 bg-white rounded shadow-sm">

        <div class="d-flex gap-2 mb-3">
            <input type="text" id="searchInput" class="form-control" placeholder="Поиск мастера или услуги...">
            <button id="searchBtn" class="btn btn-primary">
                <i class="bi bi-search"></i>
            </button>
        </div>

    </div>

    <!-- УСЛУГИ -->
    <h2 class="mb-3 mt-4">Услуги</h2>
    <div class="row g-3">

    <?php foreach ($products as $product): ?>
        <div class="col-12 col-sm-6 col-md-4 search-item">
            <div class="card h-100">

                <?php $img = $product['image_url'] ?: 'https://via.placeholder.com/300x200'; ?>
                <img src="<?= htmlspecialchars($img) ?>">

                <div class="card-body d-flex flex-column">

                    <h5><?= htmlspecialchars($product['title']) ?></h5>
                    <p><?= htmlspecialchars($product['description'] ?? '') ?></p>

                    <div class="mt-auto">

                        <span class="fw-bold text-primary">
                            <?= number_format($product['price'],0,'',' ') ?> ₽
                        </span>

                        <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>

                            <div class="d-flex gap-2 mt-2">

                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="btn btn-warning w-50">
                                    Редактировать
                                </a>

                                <form action="delete_product.php" method="POST" class="w-50"
                                      onsubmit="return confirm('Удалить услугу?');">
                                    <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                    <button class="btn btn-danger w-100">Удалить</button>
                                </form>

                            </div>

                        <?php else: ?>

                            <?php if (isset($_SESSION['user_id'])): ?>
                                <a href="book.php?product_id=<?= $product['id'] ?>" class="btn btn-primary w-100 mt-2">
                                    Записаться
                                </a>
                            <?php endif; ?>

                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>

    </div>

    <!-- МАСТЕРА -->
    <h2 class="mb-3 mt-4">Мастера</h2>
    <div class="row g-3">

    <?php foreach ($masters as $master): ?>
        <div class="col-12 col-sm-6 col-md-4 search-item">
            <div class="card h-100">

                <?php $photo = $master['image_url'] ?: 'https://via.placeholder.com/300x200?text=Мастер'; ?>
                <img src="<?= htmlspecialchars($photo) ?>">

                <div class="card-body d-flex flex-column">

                    <h5><?= htmlspecialchars($master['name']) ?></h5>

                    <p class="text-muted mb-1">
                        <?= htmlspecialchars($master['specialization'] ?? 'Без специализации') ?>
                    </p>

                    <p class="small text-secondary">
                        Стаж: <?= intval($master['experience_years'] ?? 0) ?> лет
                    </p>

                    <div class="mt-auto">

                        <?php if (!empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>

                            <div class="d-flex gap-2 mt-2">

                                <form action="delete_master.php" method="POST"
                                      onsubmit="return confirm('Удалить мастера?');"
                                      class="w-50">

                                    <input type="hidden" name="id" value="<?= $master['id'] ?>">

                                    <button type="submit" class="btn btn-danger w-100">
                                        Удалить
                                    </button>

                                </form>

                                <a href="portfolio.php?master_id=<?= $master['id'] ?>" 
                                   class="btn btn-outline-primary w-50">
                                    Работы
                                </a>

                            </div>

                        <?php else: ?>

                            <a href="portfolio.php?master_id=<?= $master['id'] ?>" 
                               class="btn btn-outline-primary w-100 mt-2">
                                Работы
                            </a>

                        <?php endif; ?>

                    </div>

                </div>
            </div>
        </div>
    <?php endforeach; ?>

    </div>

</div>

<script>
document.getElementById('searchBtn').addEventListener('click', search);
document.getElementById('searchInput').addEventListener('input', search);

function search() {
    let value = document.getElementById('searchInput').value.toLowerCase().trim();

    let items = document.querySelectorAll('.search-item');

    items.forEach(item => {
        let text = item.innerText.toLowerCase();

        if (text.includes(value)) {
            item.style.display = '';
        } else {
            item.style.display = 'none';
        }
    });
}
</script>

</body>
</html>