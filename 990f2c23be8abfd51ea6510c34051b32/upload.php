<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die('Доступ запрещен');
}

$uploadDir = 'uploads/';
$maxSize = 5 * 1024 * 1024; // 5MB

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {

    $file = $_FILES['file'];

    // 1. Проверка ошибок загрузки
    if ($file['error'] !== UPLOAD_ERR_OK) {
        die("Ошибка загрузки: " . $file['error']);
    }

    // 2. Проверка размера файла
    if ($file['size'] > $maxSize) {
        die("Ошибка: файл больше 5MB");
    }

    // 3. Проверка MIME через finfo (ПРАВИЛЬНЫЙ способ)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    if (!in_array($mimeType, $allowedTypes)) {
        die("Ошибка: разрешены только изображения (JPG, PNG, GIF, WEBP)");
    }

    // 4. Проверка через getimagesize (доп. защита)
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        die("Ошибка: файл не является изображением");
    }

    // 5. Безопасное имя файла
    $extension = image_type_to_extension($imageInfo[2]);
    $newName = uniqid('img_', true) . $extension;
    $destination = $uploadDir . $newName;

    // 6. Перемещение файла
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        die("Ошибка сохранения файла");
    }

    // 7. Сохранение в БД (ИСПРАВЛЕНО)
    $user_id = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare("
        UPDATE users 
        SET avatar = ? 
        WHERE id = ?
    ");

    $stmt->execute([$destination, $user_id]);

    echo "Файл успешно загружен!";
}
?>