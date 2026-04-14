<?php
require '../db.php';

$date = $_GET['date'];

$stmt = $pdo->prepare("
    SELECT time 
    FROM appointments 
    WHERE date = ? AND status != 'cancelled'
");
$stmt->execute([$date]);

$busyTimes = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'busyTimes' => $busyTimes
]);