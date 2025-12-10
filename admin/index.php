<?php 
require '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

// Обработка добавления нового оператора
if (isset($_POST['action']) && $_POST['action'] === 'add_carrier') {
    $name = trim($_POST['name'] ?? '');
    $color = $_POST['color'] ?? '#000000';
    $base_cost = (float)($_POST['base_cost'] ?? 0);
    $cost_per_kg = (float)($_POST['cost_per_kg'] ?? 0);
    $cost_per_km = (float)($_POST['cost_per_km'] ?? 0);
    $max_weight = (float)($_POST['max_weight'] ?? 0);
    $speed_kmh = (float)($_POST['speed_kmh'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    
    if (!empty($name)) {
        $stmt = $db->prepare("INSERT INTO carriers (name, color, base_cost, cost_per_kg, cost_per_km, max_weight, speed_kmh, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $color, $base_cost, $cost_per_kg, $cost_per_km, $max_weight, $speed_kmh, $description]);
    }
}

// Обработка редактирования тарифа
if (isset($_POST['action']) && $_POST['action'] === 'update_carrier') {
    $id = (int)($_POST['id'] ?? 0);
    $base_cost = (float)($_POST['base_cost'] ?? 0);
    $cost_per_kg = (float)($_POST['cost_per_kg'] ?? 0);
    $cost_per_km = (float)($_POST['cost_per_km'] ?? 0);
    $max_weight = (float)($_POST['max_weight'] ?? 0);
    $speed_kmh = (float)($_POST['speed_kmh'] ?? 0);

    $stmt = $db->prepare("UPDATE carriers SET base_cost=?, cost_per_kg=?, cost_per_km=?, max_weight=?, speed_kmh=? WHERE id=?");
    $stmt->execute([$base_cost, $cost_per_kg, $cost_per_km, $max_weight, $speed_kmh, $id]);
}

// Обработка изменения статуса заказа
if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? 'created';
    
    $stmt = $db->prepare("UPDATE orders SET tracking_status=? WHERE id=?");
    $stmt->execute([$new_status, $order_id]);
    
    // Also add to status history
    $stmt = $db->prepare("INSERT INTO tracking_status_history (order_id, status, description) VALUES (?, ?, ?)");
    $status_descriptions = [
        'created' => 'Заказ создан',
        'processed' => 'Заказ обработан',
        'in_transit' => 'Посылка в пути',
        'sort_center' => 'Посылка в сортировочном центре',
        'delayed' => 'Возможна задержка доставки',
        'out_for_delivery' => 'Посылка у курьера',
        'delivered' => 'Заказ доставлен',
        'returned' => 'Заказ возвращен отправителю',
        'cancelled' => 'Заказ отменен'
    ];
    $stmt->execute([$order_id, $new_status, $status_descriptions[$new_status] ?? 'Статус обновлен']);
}

// Статистика
$total_orders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $db->query("SELECT SUM(cost) FROM orders")->fetchColumn() ?: 0;
$users_count = $db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();

// Заказы по дням (последние 7 дней)
$stats_days = $db->query("
    SELECT DATE(created_at) as d, COUNT(*) as cnt, SUM(cost) as sum 
    FROM orders 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(created_at)
    ORDER BY d
")->fetchAll();

// По операторам
$stats_carriers = $db->query("
    SELECT c.name, COUNT(o.id) as orders, SUM(o.cost) as revenue 
    FROM orders o 
    JOIN carriers c ON o.carrier_id = c.id 
    GROUP BY c.id
")->fetchAll();

// Топ направлений
$top_routes = $db->query("
    SELECT 
        of1.city as from_city, of2.city as to_city, COUNT(*) as cnt
    FROM orders o
    JOIN offices of1 ON o.from_office = of1.id
    JOIN offices of2 ON o.to_office = of2.id
    GROUP BY o.from_office, o.to_office
    ORDER BY cnt DESC LIMIT 5
")->fetchAll();

// Заказы для отслеживания статуса
$recent_orders = $db->query("
    SELECT o.*, c.name as carrier_name, u.name as user_name 
    FROM orders o 
    LEFT JOIN carriers c ON o.carrier_id = c.id 
    LEFT JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 20
")->fetchAll();

$carriers = $db->query("SELECT * FROM carriers")->fetchAll();

// Define status options
$status_options = [
    'created' => 'Создан',
    'processed' => 'Обработан',
    'in_transit' => 'В пути',
    'sort_center' => 'Сорт. центр',
    'delayed' => 'Задержка',
    'out_for_delivery' => 'У курьера',
    'delivered' => 'Доставлен',
    'returned' => 'Возвращен',
    'cancelled' => 'Отменен'
];
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card { background: rgba(255,255,255,0.1); border-radius: 15px; padding: 20px; text-align: center; }
        body.dark .stat-card { background: rgba(255,255,255,0.08); }
        .edit-input { width: 80px; font-size: 0.9em; }
        .status-badge { font-size: 0.8em; }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <span class="navbar-brand">Админ-панель</span>
        <div>
            <a href="../calculator.php" class="btn btn-outline-light me-2">На сайт</a>
            <a href="../logout.php" class="btn btn-danger">Выйти</a>
        </div>
    </div>
</nav>

<div class="container mt-4">

    <!-- Статистика -->
    <div class="row mb-5 text-white">
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $total_orders ?></h3>
                <p>Всего заказов</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= round($total_revenue, 2) ?> BYN</h3>
                <p>Выручка</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= $users_count ?></h3>
                <p>Пользователей</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <h3><?= round($total_revenue / max($total_orders,1), 2) ?> BYN</h3>
                <p>Средний чек</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Редактирование тарифов -->
        <div class="col-lg-7 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4>Тарифы операторов (редактирование)</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Оператор</th>
                                    <th>База</th>
                                    <th>За кг</th>
                                    <th>За км</th>
                                    <th>Макс. вес</th>
                                    <th>Скорость</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carriers as $c): ?>
                                <tr>
                                    <td><strong style="color:<?= $c['color'] ?>"><?= htmlspecialchars($c['name']) ?></strong></td>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_carrier">
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                        <td><input name="base_cost" value="<?= $c['base_cost'] ?>" class="form-control form-control-sm edit-input" step="0.1"></td>
                                        <td><input name="cost_per_kg" value="<?= $c['cost_per_kg'] ?>" class="form-control form-control-sm edit-input" step="0.05"></td>
                                        <td><input name="cost_per_km" value="<?= $c['cost_per_km'] ?>" class="form-control form-control-sm edit-input" step="0.001"></td>
                                        <td><input name="max_weight" value="<?= $c['max_weight'] ?>" class="form-control form-control-sm edit-input"></td>
                                        <td><input name="speed_kmh" value="<?= $c['speed_kmh'] ?>" class="form-control form-control-sm edit-input"></td>
                                        <td><button class="btn btn-success btn-sm">Сохранить</button></td>
                                    </form>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Добавление нового оператора -->
            <div class="card mt-4">
                <div class="card-header bg-info text-white">
                    <h4>Добавить нового оператора</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="add_carrier">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Название оператора</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Цвет (HEX)</label>
                                <input type="color" name="color" class="form-control form-control-color" value="#0066cc" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Базовая стоимость</label>
                                <input type="number" step="0.01" name="base_cost" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Стоимость за кг</label>
                                <input type="number" step="0.01" name="cost_per_kg" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Стоимость за км</label>
                                <input type="number" step="0.001" name="cost_per_km" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Макс. вес (кг)</label>
                                <input type="number" step="0.1" name="max_weight" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Скорость (км/ч)</label>
                                <input type="number" step="0.1" name="speed_kmh" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Описание</label>
                                <input type="text" name="description" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-info btn-lg w-100">Добавить оператора</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Статистика по статусам заказов -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>Статистика по статусам заказов</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Get order counts by status
                    $status_stats = $db->query("
                        SELECT 
                            tracking_status,
                            COUNT(*) as count
                        FROM orders 
                        GROUP BY tracking_status
                        ORDER BY count DESC
                    ")->fetchAll();
                    ?>
                    <ol class="list-group list-group-numbered">
                        <?php foreach($status_stats as $stat): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>
                                <?= htmlspecialchars($status_options[$stat['tracking_status']] ?? $stat['tracking_status']) ?>
                            </span>
                            <span class="badge bg-primary rounded-pill"><?= $stat['count'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Управление статусами заказов -->
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <h4>Управление статусами заказов</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Трек</th>
                            <th>Клиент</th>
                            <th>Оператор</th>
                            <th>Стоимость</th>
                            <th>Текущий статус</th>
                            <th>Изменить статус</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($recent_orders as $order): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($order['track_number']) ?></strong></td>
                            <td><?= htmlspecialchars($order['user_name'] ?? 'Н/Д') ?></td>
                            <td><?= htmlspecialchars($order['carrier_name'] ?? 'Н/Д') ?></td>
                            <td><?= $order['cost'] ?> BYN</td>
                            <td>
                                <span class="badge bg-info status-badge">
                                    <?= htmlspecialchars($status_options[$order['tracking_status'] ?? 'created'] ?? 'Обработан') ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="update_status">
                                    <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                                    <select name="new_status" class="form-select form-select-sm d-inline w-auto me-2">
                                        <?php foreach($status_options as $status_key => $status_name): ?>
                                        <option value="<?= $status_key ?>" <?= (($order['tracking_status'] ?? 'created') == $status_key) ? 'selected' : '' ?>>
                                            <?= $status_name ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button class="btn btn-sm btn-warning">Изменить</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Графики -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5>Заказы за 7 дней</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartDays"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5>Выручка по операторам</h5>
                </div>
                <div class="card-body">
                    <canvas id="chartCarriers"></canvas>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
// График заказов по дням
new Chart(document.getElementById('chartDays'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($stats_days, 'd')) ?>,
        datasets: [{
            label: 'Заказы',
            data: <?= json_encode(array_column($stats_days, 'cnt')) ?>,
            borderColor: '#00ff88',
            backgroundColor: 'rgba(0,255,136,0.2)',
            tension: 0.4,
            fill: true
        }]
    },
    options: { responsive: true, plugins: { legend: { display: false } } }
});

// Выручка по операторам
new Chart(document.getElementById('chartCarriers'), {
    type: 'doughnut',
    data: {
        labels: <?= json_encode(array_column($stats_carriers, 'name')) ?>,
        datasets: [{
            data: <?= json_encode(array_column($stats_carriers, 'revenue')) ?>,
            backgroundColor: ['#ff6384','#36a2eb','#ffce56','#4bc0c0','#9966ff','#ff9f40']
        }]
    },
    options: { responsive: true }
});
</script>
</body>
</html>