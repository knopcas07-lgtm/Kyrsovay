<?php
require '../db.php';

$errorMsg = '';
$successMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';
    $passConfirm = $_POST['password_confirm'] ?? '';

    if (empty($email) || empty($username) || empty($pass)) {
        $errorMsg = "Заполните все поля!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMsg = "Некорректный формат Email!";
    } elseif ($pass !== $passConfirm) {
        $errorMsg = "Пароли не совпадают!";
    } elseif (strlen($pass) < 6) {
        $errorMsg = "Пароль должен быть минимум 6 символов!";
    } else {

        $hash = password_hash($pass, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (email, username, password_hash, role)
                VALUES (:email, :username, :hash, 'client')";

        $stmt = $pdo->prepare($sql);

        try {
            $stmt->execute([
                ':email' => $email,
                ':username' => $username,
                ':hash' => $hash
            ]);

            $successMsg = "Регистрация успешна! <a href='login.php'>Войти</a>";

        } catch (PDOException $e) {

            if ($e->getCode() == 23000) {
                $errorMsg = "Такой email уже зарегистрирован.";
            } else {
                $errorMsg = "Ошибка БД: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Регистрация</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

<style>
html, body {
    margin: 0;
    padding: 0;
    overflow-x: hidden;
    background-color: #f8f9fa;
}

.main-container {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.card {
    width: 100%;
    max-width: 500px;
    margin: 10px;
    border-radius: 12px;
}

.card-header {
    text-align: center;
}

@media (max-width: 768px) {
    .card {
        max-width: 100%;
    }

    .form-control {
        font-size: 14px;
        padding: 10px;
    }

    .btn {
        font-size: 14px;
    }
}
</style>
</head>

<body>

<div class="main-container">

    <div class="card shadow">

        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">Регистрация</h4>
        </div>

        <div class="card-body">

            <?php if($errorMsg): ?>
                <div class="alert alert-danger"><?= $errorMsg ?></div>
            <?php endif; ?>

            <?php if($successMsg): ?>
                <div class="alert alert-success"><?= $successMsg ?></div>
            <?php else: ?>

            <form method="POST">

                <!-- USERNAME -->
                <div class="mb-3">
                    <label>Имя пользователя</label>
                    <input type="text" name="username" class="form-control" required>
                </div>

                <!-- EMAIL -->
                <div class="mb-3">
                    <label>Email адрес</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <label>Пароль</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <!-- CONFIRM -->
                <div class="mb-3">
                    <label>Подтверждение пароля</label>
                    <input type="password" name="password_confirm" class="form-control" required>
                </div>

                <div class="text-center">
                    <button type="submit" class="btn btn-primary px-5">
                        Зарегистрироваться
                    </button>
                </div>

            </form>

            <div class="mt-3 text-center">
                <a href="login.php">Уже есть аккаунт? Войти</a>
            </div>

            <?php endif; ?>

        </div>
    </div>

</div>

</body>
</html>