<?php
session_start();
require '../db.php';

// 1. Проверка: Вошел ли пользователь?
if (!isset($_SESSION['user_id'])) {
    die("Сначала войдите в систему! <a href='login.php'>Вход</a>");
}

// 2. Получаем ID товара из ссылки (например, make_order.php?id=5)
// (int) — это защита от хакеров, превращаем всё в число
$product_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

if ($product_id <= 0) {
    die("Неверный товар.");
}

// ПРОВЕРКА БЕЗОПАСНОСТИ №2: А есть ли такой товар?
$check = $pdo->prepare("SELECT id FROM products WHERE id = ?");
$check->execute([$product_id]);
$exists = $check->fetch();

if (!$exists) {
    $user_ip = $_SERVER['REMOTE_ADDR'];
    die("Ошибка: Попытка заказать несуществующий товар! Ваш IP ($user_ip) записан.");
}

// 3. Создаем заказ
$stmt = $pdo->prepare("INSERT INTO appointments (user_id, product_id) VALUES (?, ?)");
try {
    $stmt->execute([$user_id, $product_id]);
    echo "Заказ успешно оформлен! Менеджер свяжется с вами. <a href='index.php'>Вернуться</a>";
} catch (PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}
?>