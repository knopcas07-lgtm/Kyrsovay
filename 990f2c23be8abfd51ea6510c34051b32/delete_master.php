<?php
session_start();
require '../db.php';

// только админ
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Нет доступа");
}

$id = $_POST['id'] ?? null;

if (!$id) {
    die("Нет ID");
}

try {
    $stmt = $pdo->prepare("DELETE FROM masters WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Ошибка удаления: " . $e->getMessage());
}