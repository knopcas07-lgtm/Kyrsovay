<?php
session_start();
require '../db.php';

// только админ
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Нет доступа");
}

// проверка ID
$id = $_POST['id'] ?? null;

if (!$id) {
    die("Нет ID");
}

try {
    // удаление из базы
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);

    // возврат назад
    header("Location: index.php");
    exit;

} catch (PDOException $e) {
    die("Ошибка удаления: " . $e->getMessage());
}