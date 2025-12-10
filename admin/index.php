<?php 
require '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

// Обработка редактирования тарифа
if ($_POST['action'] ?? '' === 'update_carrier') {
    $id = (int)$_POST['id'];
    $base_cost = (float)$_POST['base_cost'];
    $cost_per_kg = (float)$_POST['cost_per_kg'];
    $cost_per_km = (float)$_POST['cost_per_km'];
    $max_weight = (float)$_POST['max_weight'];
    $speed_kmh = (float)$_POST['speed_kmh'];

    $stmt = $db->prepare("UPDATE carriers SET base_cost=?, cost_per_kg=?, cost_per_km=?, max_weight=?, speed_kmh=? WHERE id=?");
    $stmt->execute([$base_cost, $cost_per_kg, $cost_per_km, $max_weight, $speed_kmh, $id]);
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

$carriers = $db->query("SELECT * FROM carriers")->fetchAll();
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
        </div>

        <!-- Топ направлений -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5>ТОП-5 направлений</h5>
                </div>
                <div class="card-body">
                    <ol class="list-group list-group-numbered">
                        <?php foreach($top_routes as $r): ?>
                        <li class="list-group-item d-flex justify-content-between">
                            <span><?= htmlspecialchars($r['from_city']) ?> → <?= htmlspecialchars($r['to_city']) ?></span>
                            <span class="badge bg-primary rounded-pill"><?= $r['cnt'] ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
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