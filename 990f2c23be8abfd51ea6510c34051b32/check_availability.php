<?php
// check_availability.php
session_start();
require '../db.php';

if (!isset($_GET['product_id']) || !isset($_GET['date'])) {
    echo json_encode([]);
    exit();
}

$product_id = (int)$_GET['product_id'];
$date = $_GET['date'];

// Получаем все занятые времена для выбранной даты
$stmt = $pdo->prepare("SELECT time FROM appointments WHERE product_id = ? AND date = ?");
$stmt->execute([$product_id, $date]);

$booked_times = [];
while ($row = $stmt->fetch()) {
    $booked_times[] = $row['time'];
}

echo json_encode($booked_times);
?>