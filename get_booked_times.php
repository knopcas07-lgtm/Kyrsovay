<?php
require '../db.php';

$date = $_GET['date'] ?? null;
$ignoreId = $_GET['ignore_id'] ?? null;

if (!$date) {
    echo json_encode(['booked_times' => []]);
    exit;
}

if ($ignoreId) {
    $stmt = $pdo->prepare("
        SELECT TIME_FORMAT(time, '%H:%i') 
        FROM appointments 
        WHERE date = ? AND id != ?
    ");
    $stmt->execute([$date, $ignoreId]);
} else {
    $stmt = $pdo->prepare("
        SELECT TIME_FORMAT(time, '%H:%i') 
        FROM appointments 
        WHERE date = ?
    ");
    $stmt->execute([$date]);
}

$times = $stmt->fetchAll(PDO::FETCH_COLUMN);

echo json_encode([
    'booked_times' => $times
]);