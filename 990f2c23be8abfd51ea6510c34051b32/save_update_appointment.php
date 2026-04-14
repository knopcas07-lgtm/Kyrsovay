<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Нет доступа");
}

$id = $_POST['id'];
$date = $_POST['date'];
$time = $_POST['time'];
$user_id = $_SESSION['user_id'];

// получаем текущую запись
$stmt = $pdo->prepare("SELECT * FROM appointments WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $user_id]);
$current = $stmt->fetch();

if (!$current) {
    die("Запись не найдена");
}

// 🔴 ПРОВЕРКА: занято ли время у этого мастера
$stmt = $pdo->prepare("
    SELECT COUNT(*) 
    FROM appointments
    WHERE master_id = ?
    AND date = ?
    AND time = ?
    AND status != 'cancelled'
    AND id != ?
");
$stmt->execute([
    $current['master_id'],
    $date,
    $time,
    $id
]);

if ($stmt->fetchColumn() > 0) {
    die("Это время уже занято другим клиентом");
}

// ✅ если свободно — обновляем
$stmt = $pdo->prepare("
    UPDATE appointments 
    SET date = ?, time = ?, status = 'pending'
    WHERE id = ?
");
$stmt->execute([$date, $time, $id]);

header("Location: profile.php");
exit;