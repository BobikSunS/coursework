<?php require 'db.php'; 
if (!isset($_SESSION['user'])) header('Location: index.php');
$user = $_SESSION['user'];
$orders = $db->prepare("SELECT o.*, c.name as carrier_name FROM orders o JOIN carriers c ON o.carrier_id=c.id WHERE o.user_id=? ORDER BY o.created_at DESC");
$orders->execute([$user['id']]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История заказов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-lg">
    <div class="container-fluid">
        <a class="navbar-brand">Расчёт доставки</a>
        <div>
            <a href="calculator.php" class="btn btn-light me-2">Назад к расчёту</a>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h2 class="text-white">История расчётов</h2>
    <div class="card shadow-lg">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Трек</th><th>Оператор</th><th>Вес</th><th>Стоимость</th><th>Время</th><th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders->fetchAll() as $o): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($o['track_number']) ?></strong></td>
                        <td><?= htmlspecialchars($o['carrier_name']) ?></td>
                        <td><?= $o['weight'] ?> кг</td>
                        <td><?= $o['cost'] ?> BYN</td>
                        <td><?= round($o['delivery_hours'],1) ?> ч</td>
                        <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>