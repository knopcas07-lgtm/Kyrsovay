<?php
require 'check_admin.php';
require '../db.php';

$id = $_POST['id'];
$status = $_POST['status'];

// проверяем текущий статус
$stmt = $pdo->prepare("SELECT status FROM appointments WHERE id=?");
$stmt->execute([$id]);
$current = $stmt->fetchColumn();

// запрещаем менять отменённые
if ($current === 'cancelled') {
    die("Запись уже отменена");
}

$stmt = $pdo->prepare("
UPDATE appointments 
SET status = ?
WHERE id = ?
");

$stmt->execute([$status, $id]);

header("Location: admin_appointments.php");
exit;