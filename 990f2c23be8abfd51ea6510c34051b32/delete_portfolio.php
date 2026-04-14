<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $portfolio_id = $_POST['portfolio_id'] ?? 0;
    $master_id = $_POST['master_id'] ?? 0;

    if ($portfolio_id && $master_id) {
        // Получаем информацию о файле
        $stmt = $pdo->prepare("SELECT image_url FROM master_portfolio WHERE id = ?");
        $stmt->execute([$portfolio_id]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($item) {
            // Удаляем файл
            $file_path = $item['image_url'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Удаляем запись из БД
            $stmt = $pdo->prepare("DELETE FROM master_portfolio WHERE id = ?");
            $stmt->execute([$portfolio_id]);
            
            $_SESSION['flash_message'] = 'Фото успешно удалено';
        }
    }
}

// Возвращаемся на страницу добавления портфолио или к мастерам
if ($master_id) {
    header("Location: add_portfolio.php?master_id=$master_id");
} else {
    header('Location: masters.php');
}
exit;
?>