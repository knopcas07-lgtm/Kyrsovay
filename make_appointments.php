<?php
session_start();
require '../db.php';

header('Content-Type: application/json');

// Проверка авторизации
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Для записи необходимо войти в систему']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'] ?? null;
$date = $_POST['date'] ?? null;
$time = $_POST['time'] ?? null;

// Приводим время к формату HH:MM:SS
if ($time && substr_count($time, ':') == 1) {
    $time .= ':00';
}

// Базовая валидация
if (!$product_id || !$date || !$time) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны']);
    exit;
}

// Проверка: дата не в прошлом
$selectedDate = new DateTime($date);
$today = new DateTime();
$today->setTime(0, 0, 0);

if ($selectedDate < $today) {
    echo json_encode(['success' => false, 'message' => 'Нельзя записаться на прошедшую дату']);
    exit;
}

// Проверка: время не прошло (если сегодня)
if ($selectedDate == $today) {
    $now = new DateTime();
    $selectedTime = DateTime::createFromFormat('H:i:s', $time);

    if ($selectedTime <= $now) {
        echo json_encode(['success' => false, 'message' => 'Нельзя записаться на прошедшее время']);
        exit;
    }
}

// ГЛОБАЛЬНАЯ проверка (БЕЗ product_id)
$check = $pdo->prepare("
    SELECT id 
    FROM appointments 
    WHERE date = ? 
    AND time = ?
");
$check->execute([$date, $time]);

if ($check->fetch()) {
    echo json_encode([
        'success' => false,
        'message' => 'Это время уже занято другим клиентом'
    ]);
    exit;
}

// Получаем услугу
$stmt = $pdo->prepare("SELECT title, price FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    echo json_encode(['success' => false, 'message' => 'Услуга не найдена']);
    exit;
}

// Запись
try {
    $stmt = $pdo->prepare("
    INSERT INTO appointments (user_id, product_id, date, time, status)
    VALUES (?, ?, ?, ?, 'pending')
    ");
    $stmt->execute([$product_id, $user_id, $date, $time]);

    $display_time = substr($time, 0, 5);

    echo json_encode([
        'success' => true,
        'message' => 'Запись подтверждена',
        'product_title' => $product['title'],
        'price' => $product['price'],
        'date' => $date,
        'time' => $display_time
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Это время только что заняли'
    ]);
}