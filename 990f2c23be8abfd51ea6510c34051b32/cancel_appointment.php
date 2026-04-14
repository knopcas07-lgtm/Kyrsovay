<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Нет доступа");
}

$id = $_POST['id'];
$user_id = $_SESSION['user_id'];

// проверяем запись
$stmt = $pdo->prepare("
    SELECT * FROM appointments 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $user_id]);
$appt = $stmt->fetch();

if (!$appt) {
    die("Запись не найдена");
}

// ❗ если уже отменена — ничего не делаем
if ($appt['status'] === 'cancelled') {
    header("Location: profile.php");
    exit;
}

// отмена
$stmt = $pdo->prepare("
    UPDATE appointments 
    SET 
        status = 'cancelled',
        cancel_reason = 'Отменено пользователем'
    WHERE id = ?
");
$stmt->execute([$id]);

header("Location: profile.php");
exit;