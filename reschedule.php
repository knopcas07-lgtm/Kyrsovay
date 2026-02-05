<?php
session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$id = (int)$_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $date = $_POST['date'];
    $time = $_POST['time'];

    // Обновляем запись только если она принадлежит текущему пользователю
    $stmt = $pdo->prepare("
        UPDATE appointments 
        SET date = ?, time = ? 
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$date, $time, $id, $_SESSION['user_id']]);

    header("Location: profile.php");
    exit;
}