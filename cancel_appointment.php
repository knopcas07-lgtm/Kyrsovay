<?php
session_start();
require '../db.php';

$id = (int)$_GET['id'];

$stmt = $pdo->prepare("
    UPDATE appointments 
    SET status = 'cancelled' 
    WHERE id = ? AND user_id = ?
");
$stmt->execute([$id, $_SESSION['user_id']]);

header("Location: profile.php");
exit;