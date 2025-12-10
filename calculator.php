<?php require 'db.php';
if (!isset($_SESSION['user'])) header('Location: index.php');
$user = $_SESSION['user'];

$carriers = $db->query("SELECT * FROM carriers")->fetchAll();

// Граф
$graph = [];
foreach ($db->query("SELECT from_office, to_office, distance_km FROM routes") as $r) {
    $graph[$r['from_office']][$r['to_office']] = $r['distance_km'];
    $graph[$r['to_office']][$r['from_office']] = $r['distance_km'];
}

function dijkstra($graph, $start, $end) {
    $dist = array_fill_keys(array_keys($graph), INF);
    $prev = [];
    $dist[$start] = 0;
    $queue = [$start => 0];

    while (!empty($queue)) {
        $u = array_keys($queue, min($queue))[0];
        unset($queue[$u]);
        if (!isset($graph[$u])) continue;
        foreach ($graph[$u] as $v => $w) {
            $alt = $dist[$u] + $w;
            if ($alt < $dist[$v]) {
                $dist[$v] = $alt;
                $prev[$v] = $u;
                $queue[$v] = $alt;
            }
        }
    }
    if ($dist[$end] === INF) return null;
    $path = [];
    $u = $end;
    while ($u != $start) {
        $path[] = $u;
        $u = $prev[$u] ?? null;
        if ($u === null) return null;
    }
    $path[] = $start;
    return ['path' => array_reverse($path), 'distance' => $dist[$end]];
}

$result = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $carrier_id = (int)$_POST['carrier'];
    $from = (int)$_POST['from'];
    $to = (int)$_POST['to'];

    if ($from === $to) {
        $error = "Нельзя отправить в то же отделение!";
    } else {
        $carrier = $db->query("SELECT * FROM carriers WHERE id = $carrier_id")->fetch();
        $pathData = dijkstra($graph, $from, $to);
        if (!$pathData) {
            $error = "Маршрут не найден!";
        } else {
            $distance = $pathData['distance'];
            $base_hours = $distance / $carrier['speed_kmh'];

            $type = $_POST['package_type'];
            $gabarit = $_POST['gabarit'] ?? 'small';
            $speed = $_POST['delivery_speed'] ?? 'standard';
            $insurance = isset($_POST['insurance']);

            $volume_weight = 0;
            if ($type === 'parcel') {
                if ($gabarit === 'medium') $volume_weight = 8;
                if ($gabarit === 'large') $volume_weight = 20;
            }

            $weight = $type === 'letter' 
                ? 0.02 * (int)($_POST['letter_count'] ?? 1)
                : max((float)$_POST['weight'], $volume_weight);

            $max_weight = $carrier['max_weight'];
            if ($gabarit === 'medium') $max_weight += 8;
            if ($gabarit === 'large') $max_weight += 20;

            if ($weight > $max_weight) {
                $error = "Вес превышает лимит оператора ($max_weight кг)!";
            } else {
                $cost = $carrier['base_cost'] 
                      + $weight * $carrier['cost_per_kg'] 
                      + $distance * $carrier['cost_per_km'];

                if ($gabarit === 'medium') $cost += 6;
                if ($gabarit === 'large') $cost += 15;
                if ($speed === 'express') { $cost *= 1.25; $base_hours *= 0.7; }
                if ($insurance) $cost *= 1.02;
                if ($type === 'letter') $cost = max($cost, 2.5);

                $cost = round($cost, 2);
                $hours = round($base_hours, 1);

                $track = strtoupper(substr(md5(uniqid()), 0, 12));
                $stmt = $db->prepare("INSERT INTO orders (user_id, carrier_id, from_office, to_office, weight, cost, delivery_hours, track_number) VALUES (?,?,?,?,?,?,?,?)");
                $stmt->execute([$user['id'], $carrier_id, $from, $to, $weight, $cost, $hours, $track]);

                $result = [
                    'carrier' => $carrier,
                    'cost' => $cost,
                    'hours' => $hours,
                    'track' => $track,
                    'distance' => $distance
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Почтовый калькулятор</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-lg fixed-top">
    <div class="container-fluid">
        <a class="navbar-brand">Почтовый калькулятор</a>
        <div>
            <a href="profile.php" class="btn btn-light me-2">Профиль</a>
            <a href="order_form.php" class="btn btn-success me-2">Оформить заказ</a>
            <a href="history.php" class="btn btn-warning me-2">История</a>
            <?php if($user['role']==='admin'): ?><a href="admin/index.php" class="btn btn-danger me-2">Админка</a><?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light">Выйти</a>
        </div>
    </div>
</nav>

<!-- Spacer to prevent content from being hidden behind fixed navbar -->
<div style="height: 80px;"></div>

<div class="container mt-5">
    <h2 class="text-center text-white mb-4">Выберите оператора</h2>
    <div class="row justify-content-center g-4">
        <?php foreach($carriers as $c): ?>
        <div class="col-md-4 col-sm-6">
            <div class="carrier-card p-4 text-center text-white shadow-lg" 
                 style="background: <?= $c['color'] ?>;" 
                 onclick="selectCarrier(<?= $c['id'] ?>, '<?= htmlspecialchars($c['name']) ?>')">
                <h4><?= htmlspecialchars($c['name']) ?></h4>
                <small>до <?= $c['max_weight'] ?> кг</small>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-5 shadow-lg" id="calc-form" style="display:none;">
        <div class="card-body">
            <h4 class="text-center mb-4">Расчёт для: <strong id="carrier-name"></strong></h4>
            <form method="POST">
                <input type="hidden" name="carrier" id="selected-carrier">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Откуда</label>
                        <select name="from" class="form-select" required><option value="">Выберите</option></select>
                    </div>
                    <div class="col-md-6">
                        <label>Куда</label>
                        <select name="to" class="form-select" required><option value="">Выберите</option></select>
                    </div>

                    <div class="col-md-4">
                        <label>Тип отправления</label>
                        <select name="package_type" class="form-select" onchange="toggleFields(this.value)" required>
                            <option value="parcel">Посылка</option>
                            <option value="letter">Письмо</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="weight-div">
                        <label>Вес (кг)</label>
                        <input type="number" step="0.1" name="weight" class="form-control" value="1" min="0.1" required>
                    </div>

                    <div class="col-md-4" id="letter-div" style="display:none;">
                        <label>Количество писем</label>
                        <input type="number" name="letter_count" class="form-control" value="1" min="1" max="50">
                    </div>

                    <div class="col-md-4" id="gabarit-div">
                        <label>Габариты</label>
                        <select name="gabarit" class="form-select">
                            <option value="small">Маленький</option>
                            <option value="medium">Средний (+6 BYN)</option>
                            <option value="large">Крупный (+15 BYN)</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label>Скорость</label>
                        <select name="delivery_speed" class="form-select">
                            <option value="standard">Стандарт</option>
                            <option value="express">Экспресс (+25%)</option>
                        </select>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <div class="form-check">
                            <input type="checkbox" name="insurance" class="form-check-input" id="ins">
                            <label class="form-check-label" for="ins">Страховка (+2%)</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg mt-4 w-100">Рассчитать</button>
            </form>
        </div>
    </div>

    <?php if($result): ?>
    <div class="card mt-5 result-box">
        <div class="card-body text-center">
            <h2><?= $result['cost'] ?> BYN</h2>
            <p class="lead">Время доставки: ~<?= $result['hours'] ?> ч (<?= round($result['distance']) ?> км)</p>
            <p><strong>Трек-номер:</strong> 
                <span class="badge bg-danger fs-5"><?= $result['track'] ?></span>
                <button onclick="navigator.clipboard.writeText('<?= $result['track'] ?>')" class="btn btn-sm btn-outline-light ms-2">Копировать</button>
            </p>
            <a href="generate_pdf.php?track=<?= $result['track'] ?>" target="_blank" class="btn btn-danger btn-lg">
                Скачать квитанцию (PDF)
            </a>
            <a href="track.php?track=<?= $result['track'] ?>" target="_blank" class="btn btn-primary btn-lg ms-3">
                Отследить посылку
            </a>
            <a href="order_form.php?carrier=<?= $result['carrier']['id'] ?>&weight=<?= ($_POST['package_type'] === 'letter' ? ($_POST['letter_count'] ?? 1) * 0.02 : $_POST['weight'] ?? 1) ?>&cost=<?= $result['cost'] ?>" class="btn btn-success btn-lg ms-3">
                Оформить заказ
            </a>
        </div>
    </div>
    <?php endif; ?>

    <?php 
    // Get all calculation results for comparison if form was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$error) {
        // Fetch all carrier options for the same route
        $from = (int)$_POST['from'];
        $to = (int)$_POST['to'];
        
        $all_results = [];
        foreach($carriers as $c) {
            $pathData = dijkstra($graph, $from, $to);
            if ($pathData) {
                $distance = $pathData['distance'];
                $base_hours = $distance / $c['speed_kmh'];

                $type = $_POST['package_type'];
                $gabarit = $_POST['gabarit'] ?? 'small';
                $speed = $_POST['delivery_speed'] ?? 'standard';
                $insurance = isset($_POST['insurance']);

                $volume_weight = 0;
                if ($type === 'parcel') {
                    if ($gabarit === 'medium') $volume_weight = 8;
                    if ($gabarit === 'large') $volume_weight = 20;
                }

                $weight = $type === 'letter' 
                    ? 0.02 * (int)($_POST['letter_count'] ?? 1)
                    : max((float)$_POST['weight'], $volume_weight);

                $max_weight = $c['max_weight'];
                if ($gabarit === 'medium') $max_weight += 8;
                if ($gabarit === 'large') $max_weight += 20;

                if ($weight <= $max_weight) {
                    $cost = $c['base_cost'] 
                          + $weight * $c['cost_per_kg'] 
                          + $distance * $c['cost_per_km'];

                    if ($gabarit === 'medium') $cost += 6;
                    if ($gabarit === 'large') $cost += 15;
                    if ($speed === 'express') { $cost *= 1.25; $base_hours *= 0.7; }
                    if ($insurance) $cost *= 1.02;
                    if ($type === 'letter') $cost = max($cost, 2.5);

                    $cost = round($cost, 2);
                    $hours = round($base_hours, 1);

                    $all_results[] = [
                        'carrier' => $c,
                        'cost' => $cost,
                        'hours' => $hours,
                        'distance' => $distance
                    ];
                }
            }
        }
        
        if (!empty($all_results)) {
            // Sort by different criteria for filters
            $cheapest = $all_results;
            $fastest = $all_results;
            
            usort($cheapest, function($a, $b) { return $a['cost'] <=> $b['cost']; });
            usort($fastest, function($a, $b) { return $a['hours'] <=> $b['hours']; });
            
            $filters = [
                'all' => $all_results,
                'cheapest' => $cheapest,
                'fastest' => $fastest
            ];
            
            $active_filter = $_GET['filter'] ?? 'all';
            $results_to_show = $filters[$active_filter];
    ?>
    <div class="card mt-5 shadow-lg">
        <div class="card-header bg-secondary text-white">
            <h4>Сравнение операторов</h4>
            <div class="btn-group" role="group">
                <a href="?filter=all" class="btn btn-sm <?= $active_filter === 'all' ? 'btn-primary' : 'btn-outline-light' ?>">Все</a>
                <a href="?filter=cheapest" class="btn btn-sm <?= $active_filter === 'cheapest' ? 'btn-success' : 'btn-outline-light' ?>">Самый дешевый</a>
                <a href="?filter=fastest" class="btn btn-sm <?= $active_filter === 'fastest' ? 'btn-info' : 'btn-outline-light' ?>">Самый быстрый</a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Оператор</th>
                            <th>Стоимость</th>
                            <th>Время доставки</th>
                            <th>Расстояние</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results_to_show as $res): ?>
                        <tr>
                            <td style="color: <?= $res['carrier']['color'] ?>"><strong><?= htmlspecialchars($res['carrier']['name']) ?></strong></td>
                            <td><strong><?= $res['cost'] ?> BYN</strong></td>
                            <td>~<?= $res['hours'] ?> ч</td>
                            <td><?= round($res['distance']) ?> км</td>
                            <td>
                                <a href="order_form.php?carrier=<?= $res['carrier']['id'] ?>&weight=<?= ($_POST['package_type'] === 'letter' ? ($_POST['letter_count'] ?? 1) * 0.02 : $_POST['weight'] ?? 1) ?>&cost=<?= $res['cost'] ?>" 
                                   class="btn btn-sm btn-success">Оформить</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
        }
    }
    ?>

    <?php if($error): ?>
    <div class="alert alert-danger mt-4"><?= $error ?></div>
    <?php endif; ?>
</div>

<!-- Footer -->
<footer class="footer mt-5 py-4 bg-light border-top">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Служба доставки. Все права защищены.</p>
        <p class="mb-1">Контактный телефон: +375 (29) 123-45-67</p>
        <p class="mb-0">Email: info@delivery.by</p>
    </div>
</footer>

<script>
let selected = null;

function selectCarrier(id, name) {
    if (selected) selected.classList.remove('selected');
    const card = event.currentTarget;
    card.classList.add('selected');
    selected = card;

    document.getElementById('selected-carrier').value = id;
    document.getElementById('carrier-name').textContent = name;
    document.getElementById('calc-form').style.display = 'block';

    fetch('get_offices.php?carrier=' + id)
        .then(r => r.json())
        .then(data => {
            ['from', 'to'].forEach(f => {
                const sel = document.querySelector(`select[name="${f}"]`);
                sel.innerHTML = '<option value="">Выберите</option>' + 
                    data.map(o => `<option value="${o.id}">${o.city} — ${o.address}</option>`).join('');
            });
        });
}

function toggleFields(type) {
    const isLetter = type === 'letter';
    document.getElementById('weight-div').style.display = isLetter ? 'none' : 'block';
    document.getElementById('letter-div').style.display = isLetter ? 'block' : 'none';
    document.getElementById('gabarit-div').style.display = isLetter ? 'none' : 'block';
}
</script>
</body>
</html>