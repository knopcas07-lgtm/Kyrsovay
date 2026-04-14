<?php
require '../db.php';

$date = $_GET['date'];
$time = $_GET['time'];

$stmt = $pdo->prepare("
    SELECT * FROM masters 
    WHERE id NOT IN (
        SELECT master_id FROM appointments 
        WHERE date = ? AND time = ? AND status != 'cancelled'
    )
");
$stmt->execute([$date, $time]);

echo json_encode($stmt->fetchAll());