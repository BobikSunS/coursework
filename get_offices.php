<?php
// get_offices.php — AJAX-запрос для отделений выбранного оператора
require 'db.php';
header('Content-Type: application/json');

$carrier_id = (int)$_GET['carrier'];
$stmt = $db->prepare("SELECT id, city, address FROM offices WHERE carrier_id = ? ORDER BY city");
$stmt->execute([$carrier_id]);
$offices = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($offices, JSON_UNESCAPED_UNICODE);
?>