<?php
require 'db.php';
if (!isset($_SESSION['user'])) header('Location: index.php');

$track = $_GET['track'] ?? '';

if (!$track) {
    die('Трек-номер не указан');
}

// В реальной реализации здесь будет генерация PDF
// Пока что просто выводим информацию о заказе

$order = $db->prepare("SELECT o.*, c.name as carrier_name FROM orders o LEFT JOIN carriers c ON o.carrier_id=c.id WHERE o.track_number=?");
$order->execute([$track]);
$order = $order->fetch();

if (!$order) {
    die('Заказ не найден');
}

// Просто выводим информацию о заказе (в реальной реализации будет генерация PDF)
header('Content-Type: application/pdf');
echo "PDF квитанция для заказа: " . $order['track_number'];
?>