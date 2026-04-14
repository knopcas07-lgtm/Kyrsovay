<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require '../db.php';

if (!isset($_SESSION['user_id'])) {
    die("Войдите в аккаунт");
}

if (
    empty($_POST['product_id']) ||
    empty($_POST['master_id']) ||
    empty($_POST['date']) ||
    empty($_POST['time'])
) {
    die("Ошибка: не все данные переданы");
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$master_id = (int)$_POST['master_id'];
$date = $_POST['date'];
$time = $_POST['time'];

// нормализация времени
if (strlen($time) == 5) {
    $time .= ":00";
}

try {

    $stmt = $pdo->prepare("
        INSERT INTO appointments 
        (user_id, product_id, master_id, date, time, status)
        VALUES (?, ?, ?, ?, ?, 'pending')
    ");

    $stmt->execute([
        $user_id,
        $product_id,
        $master_id,
        $date,
        $time
    ]);

    header("Location: profile.php");
    exit;

} catch (PDOException $e) {

    if ($e->getCode() == 23000) {
        die("❌ Этот мастер уже занят в это время");
    }

    die("DB ERROR: " . $e->getMessage());
}