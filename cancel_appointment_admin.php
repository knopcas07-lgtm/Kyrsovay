<?php
require 'check_admin.php';
require '../db.php';

$appointment_id = $_GET['id'] ?? null;
$cancel_reason = $_POST['cancel_reason'] ?? null;

if (!$appointment_id || !$cancel_reason) {
    header("Location: admin_appointments.php");
    exit;
}

// Обновляем запись
$stmt = $pdo->prepare("UPDATE appointments SET status = 'cancelled', cancel_reason = ? WHERE id = ?");
$stmt->execute([$cancel_reason, $appointment_id]);

header("Location: admin_appointments.php");
exit;