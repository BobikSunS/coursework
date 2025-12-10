<?php require 'db.php';
if (!isset($_SESSION['user'])) header('Location: index.php');

$track = $_GET['track'] ?? '';

if (!$track) {
    die('Трек-номер не указан');
}

$order = $db->prepare("SELECT o.*, c.name as carrier_name FROM orders o LEFT JOIN carriers c ON o.carrier_id=c.id WHERE o.track_number=?");
$order->execute([$track]);
$order = $order->fetch();

if (!$order) {
    die('Заказ не найден');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отслеживание посылки</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-lg">
    <div class="container-fluid">
        <a class="navbar-brand">Отслеживание посылки</a>
        <div>
            <a href="calculator.php" class="btn btn-light me-2">Калькулятор</a>
            <a href="order_form.php" class="btn btn-success me-2">Оформить заказ</a>
            <a href="history.php" class="btn btn-warning me-2">История</a>
            <?php if($_SESSION['user']['role']==='admin'): ?><a href="admin/index.php" class="btn btn-danger me-2">Админка</a><?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light">Выйти</a>
        </div>
    </div>
</nav>

<div class="container mt-5">
    <div class="card shadow-lg">
        <div class="card-body">
            <h3 class="text-center">Статус посылки</h3>
            <div class="row mt-4">
                <div class="col-md-6">
                    <p><strong>Трек-номер:</strong> <?= htmlspecialchars($order['track_number']) ?></p>
                    <p><strong>Оператор:</strong> <?= htmlspecialchars($order['carrier_name'] ?? 'Н/Д') ?></p>
                    <p><strong>Вес:</strong> <?= $order['weight'] ?> кг</p>
                    <p><strong>Стоимость:</strong> <?= $order['cost'] ?> BYN</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Дата создания:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
                    <?php if($order['delivery_hours']): ?>
                        <p><strong>Расчетное время доставки:</strong> ~<?= $order['delivery_hours'] ?> ч</p>
                    <?php endif; ?>
                    <p><strong>Статус:</strong> 
                        <span class="badge bg-success">Обработан</span>
                    </p>
                </div>
            </div>
            
            <?php if($order['full_name'] || $order['pickup_city'] || $order['delivery_city']): ?>
            <div class="mt-4">
                <h4>Детали заказа</h4>
                <div class="row">
                    <?php if($order['full_name']): ?>
                    <div class="col-md-6">
                        <p><strong>ФИО:</strong> <?= htmlspecialchars($order['full_name']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($order['home_address']): ?>
                    <div class="col-md-6">
                        <p><strong>Домашний адрес:</strong> <?= htmlspecialchars($order['home_address']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($order['pickup_city'] && $order['pickup_address']): ?>
                    <div class="col-md-6">
                        <p><strong>Адрес получения:</strong> <?= htmlspecialchars($order['pickup_city']) ?>, <?= htmlspecialchars($order['pickup_address']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($order['delivery_city'] && $order['delivery_address']): ?>
                    <div class="col-md-6">
                        <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['delivery_city']) ?>, <?= htmlspecialchars($order['delivery_address']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if($order['insurance'] || $order['packaging'] || $order['fragile']): ?>
                    <div class="col-md-12">
                        <p><strong>Дополнительно:</strong> 
                            <?php if($order['insurance']): ?><span class="badge bg-warning me-1">Страховка</span><?php endif; ?>
                            <?php if($order['packaging']): ?><span class="badge bg-info me-1">Упаковка</span><?php endif; ?>
                            <?php if($order['fragile']): ?><span class="badge bg-danger me-1">Хрупкое</span><?php endif; ?>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="history.php" class="btn btn-primary">Вернуться к истории заказов</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>