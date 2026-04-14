<?php
session_start();
require '../db.php';

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    exit(json_encode(['status' => 'error', 'message' => 'Не авторизован']));
}

// CSRF токен
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header('Content-Type: application/json');

    // CSRF проверка
    if (!isset($_POST['csrf_token']) || 
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        
        echo json_encode(['status' => 'error', 'message' => 'CSRF ошибка']);
        exit;
    }

    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($current, $user['password_hash'])) {
        echo json_encode(['status' => 'error', 'message' => 'Неверный текущий пароль']);
        exit;
    }

    if ($new !== $confirm) {
        echo json_encode(['status' => 'error', 'message' => 'Пароли не совпадают']);
        exit;
    }

    // Минимум 8 символов
    if (strlen($new) < 8) {
        echo json_encode(['status' => 'error', 'message' => 'Минимум 8 символов']);
        exit;
    }

    $newHash = password_hash($new, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$newHash, $_SESSION['user_id']]);

    echo json_encode(['status' => 'success', 'message' => 'Пароль успешно изменён']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Смена пароля</title>

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
    line-height: 1;
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

body {
    background-color: #f8f9fa;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1200px;
    padding-left: 15px;
    padding-right: 15px;
    margin: 0 auto;
}

.card {
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* уведомление */
#alertBox {
    display: none;
}

/* 📱 адаптация */
@media (max-width: 768px) {
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 0;
    }

    .btn {
        width: 100%;
    }

    .container {
        padding-left: 10px;
        padding-right: 10px;
    }
}

</style>

</head>

<body>

<?php require 'header.php'; ?>

<div class="container mt-3">
    <a href="profile.php" class="btn btn-light mb-2 d-inline-block">← Назад</a>
    <h1 class="mb-3 text-center">Смена пароля</h1>
</div>

<div class="container mt-4">

    <div class="row justify-content-center">

        <div class="col-12 col-md-6 col-lg-5">

            <div class="card p-3">

                <div id="alertBox" class="alert"></div>

                <form id="passwordForm">

                    <!-- CSRF -->
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="mb-2 position-relative">
                        <input type="password" name="current_password" id="current_password"
                               class="form-control" placeholder="Текущий пароль" required>
                    </div>

                    <div class="mb-2 position-relative">
                        <input type="password" name="new_password" id="new_password"
                               class="form-control" placeholder="Новый пароль" required>
                    </div>

                    <div class="mb-3 position-relative">
                        <input type="password" name="confirm_password" id="confirm_password"
                               class="form-control" placeholder="Повторите пароль" required>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="togglePassword">
                        <label class="form-check-label" for="togglePassword">
                            Показать пароли
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        Сохранить
                    </button>

                </form>

            </div>

        </div>

    </div>
</div>

<script>

document.getElementById('togglePassword').addEventListener('change', function () {
    const type = this.checked ? 'text' : 'password';

    document.getElementById('current_password').type = type;
    document.getElementById('new_password').type = type;
    document.getElementById('confirm_password').type = type;
});

document.getElementById('passwordForm').addEventListener('submit', function (e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {

        const alertBox = document.getElementById('alertBox');
        alertBox.style.display = 'block';

        if (data.status === 'success') {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message;
        } else {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = data.message;
        }

    });
});

</script>

</body>
</html>