<?php
// get_offices.php — AJAX-запрос для отделений выбранного оператора
require 'db.php';
header('Content-Type: application/json');

$carrier_id = (int)($_GET['carrier'] ?? 0);
$search = trim($_GET['search'] ?? '');

if ($carrier_id > 0) {
    $sql = "SELECT id, city, address FROM offices WHERE carrier_id = ?";
    $params = [$carrier_id];
    
    if (!empty($search)) {
        $sql .= " AND (city LIKE ? OR address LIKE ?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
    }
    
    $sql .= " ORDER BY city, address";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $offices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($offices, JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode([]);
}
?>