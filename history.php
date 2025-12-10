<?php require 'db.php'; 
if (!isset($_SESSION['user'])) header('Location: index.php');
$user = $_SESSION['user'];
$orders = $db->prepare("SELECT o.*, c.name as carrier_name FROM orders o LEFT JOIN carriers c ON o.carrier_id=c.id WHERE o.user_id=? ORDER BY o.created_at DESC");
$orders->execute([$user['id']]);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История заказов</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        .order-details {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-top: 10px;
        }
        body.dark .order-details {
            background: #1a2a4a;
        }
        .detail-row {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid #e9ecef;
        }
        body.dark .detail-row {
            border-color: #444;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-lg">
    <div class="container-fluid">
        <a class="navbar-brand">История заказов</a>
        <div>
            <a href="calculator.php" class="btn btn-light me-2">Калькулятор</a>
            <a href="order_form.php" class="btn btn-success me-2">Оформить заказ</a>
            <?php if($user['role']==='admin'): ?><a href="admin/index.php" class="btn btn-danger me-2">Админка</a><?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light">Выйти</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <h2 class="text-white">История заказов</h2>
    <div class="card shadow-lg">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>Трек</th><th>Оператор</th><th>Вес</th><th>Стоимость</th><th>Статус</th><th>Дата</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders->fetchAll() as $o): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($o['track_number']) ?></strong></td>
                        <td><?= htmlspecialchars($o['carrier_name'] ?? 'Н/Д') ?></td>
                        <td><?= $o['weight'] ?> кг</td>
                        <td><?= $o['cost'] ?> BYN</td>
                        <td>
                            <?php if($o['delivery_hours']): ?>
                                <span class="badge bg-info">Рассчитан</span>
                            <?php else: ?>
                                <span class="badge bg-success">Оформлен</span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y H:i', strtotime($o['created_at'])) ?></td>
                    </tr>
                    <?php if($o['full_name'] || $o['pickup_city'] || $o['delivery_city']): ?>
                    <tr>
                        <td colspan="6">
                            <div class="order-details">
                                <div class="row">
                                    <?php if($o['full_name']): ?>
                                    <div class="col-md-6">
                                        <div class="detail-row"><strong>ФИО:</strong> <?= htmlspecialchars($o['full_name']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($o['home_address']): ?>
                                    <div class="col-md-6">
                                        <div class="detail-row"><strong>Домашний адрес:</strong> <?= htmlspecialchars($o['home_address']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($o['pickup_city'] && $o['pickup_address']): ?>
                                    <div class="col-md-6">
                                        <div class="detail-row"><strong>Адрес получения:</strong> <?= htmlspecialchars($o['pickup_city']) ?>, <?= htmlspecialchars($o['pickup_address']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($o['delivery_city'] && $o['delivery_address']): ?>
                                    <div class="col-md-6">
                                        <div class="detail-row"><strong>Адрес доставки:</strong> <?= htmlspecialchars($o['delivery_city']) ?>, <?= htmlspecialchars($o['delivery_address']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($o['insurance'] || $o['packaging'] || $o['fragile']): ?>
                                    <div class="col-md-12">
                                        <div class="detail-row">
                                            <strong>Дополнительно:</strong> 
                                            <?php if($o['insurance']): ?><span class="badge bg-warning me-1">Страховка</span><?php endif; ?>
                                            <?php if($o['packaging']): ?><span class="badge bg-info me-1">Упаковка</span><?php endif; ?>
                                            <?php if($o['fragile']): ?><span class="badge bg-danger me-1">Хрупкое</span><?php endif; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if($o['comment']): ?>
                                    <div class="col-md-12">
                                        <div class="detail-row"><strong>Комментарий:</strong> <?= htmlspecialchars($o['comment']) ?></div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>