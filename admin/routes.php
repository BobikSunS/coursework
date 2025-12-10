<?php 
require '../db.php';
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: ../index.php'); 
    exit;
}

$carrier_id = (int)($_GET['carrier'] ?? 0);
if ($carrier_id <= 0) {
    header('Location: index.php');
    exit;
}

// Get carrier info
$carrier = $db->prepare("SELECT * FROM carriers WHERE id = ?");
$carrier->execute([$carrier_id]);
$carrier_info = $carrier->fetch();
if (!$carrier_info) {
    header('Location: index.php');
    exit;
}

// Get all offices for this carrier
$offices = $db->prepare("SELECT * FROM offices WHERE carrier_id = ?");
$offices->execute([$carrier_id]);
$all_offices = $offices->fetchAll();

// Get existing routes for this carrier
$routes = $db->prepare("
    SELECT r.*, o1.city as from_city, o1.address as from_address, o2.city as to_city, o2.address as to_address
    FROM routes r
    JOIN offices o1 ON r.from_office = o1.id
    JOIN offices o2 ON r.to_office = o2.id
    WHERE o1.carrier_id = ? AND o2.carrier_id = ?
");
$routes->execute([$carrier_id, $carrier_id]);
$existing_routes = $routes->fetchAll();

// Handle adding new route
if (isset($_POST['action']) && $_POST['action'] === 'add_route') {
    $from_office = (int)($_POST['from_office'] ?? 0);
    $to_office = (int)($_POST['to_office'] ?? 0);
    $distance = (float)($_POST['distance'] ?? 0);
    
    if ($from_office > 0 && $to_office > 0 && $distance > 0 && $from_office !== $to_office) {
        // Check if route already exists
        $check = $db->prepare("SELECT COUNT(*) FROM routes WHERE (from_office = ? AND to_office = ?) OR (from_office = ? AND to_office = ?)");
        $check->execute([$from_office, $to_office, $to_office, $from_office]);
        if ($check->fetchColumn() == 0) {
            // Add route (bidirectional)
            $stmt = $db->prepare("INSERT INTO routes (from_office, to_office, distance_km) VALUES (?, ?, ?)");
            $stmt->execute([$from_office, $to_office, $distance]);
            $stmt->execute([$to_office, $from_office, $distance]);
        }
    }
}

// Handle deleting route
if (isset($_POST['action']) && $_POST['action'] === 'delete_route') {
    $from_office = (int)($_POST['from_office'] ?? 0);
    $to_office = (int)($_POST['to_office'] ?? 0);
    
    if ($from_office > 0 && $to_office > 0) {
        $stmt = $db->prepare("DELETE FROM routes WHERE (from_office = ? AND to_office = ?) OR (from_office = ? AND to_office = ?)");
        $stmt->execute([$from_office, $to_office, $to_office, $from_office]);
    }
}

?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Маршруты - <?= htmlspecialchars($carrier_info['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        .stat-card { background: rgba(255,255,255,0.1); border-radius: 15px; padding: 20px; text-align: center; }
        body.dark .stat-card { background: rgba(255,255,255,0.08); }
    </style>
</head>
<body>
<nav class="navbar navbar-dark bg-dark sticky-top">
    <div class="container-fluid">
        <span class="navbar-brand">Админ-панель - Маршруты: <?= htmlspecialchars($carrier_info['name']) ?></span>
        <div>
            <a href="index.php" class="btn btn-outline-light me-2">Назад к админке</a>
            <a href="../calculator.php" class="btn btn-outline-light me-2">На сайт</a>
            <a href="../logout.php" class="btn btn-danger">Выйти</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h4>Добавить новый маршрут для <?= htmlspecialchars($carrier_info['name']) ?></h4>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="add_route">
                        <div class="row">
                            <div class="col-md-5">
                                <label class="form-label">Откуда</label>
                                <select name="from_office" class="form-select" required>
                                    <option value="">Выберите офис</option>
                                    <?php foreach($all_offices as $office): ?>
                                        <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['city']) ?> - <?= htmlspecialchars($office['address']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2 text-center d-flex align-items-center justify-content-center">
                                <span class="fs-4">→</span>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label">Куда</label>
                                <select name="to_office" class="form-select" required>
                                    <option value="">Выберите офис</option>
                                    <?php foreach($all_offices as $office): ?>
                                        <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['city']) ?> - <?= htmlspecialchars($office['address']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Расстояние (км)</label>
                                <input type="number" step="0.1" name="distance" class="form-control" placeholder="Расстояние в км" required>
                            </div>
                            <div class="col-md-6 d-flex align-items-end">
                                <button type="submit" class="btn btn-success w-100">Добавить маршрут</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info text-white">
                    <h4>Существующие маршруты</h4>
                </div>
                <div class="card-body">
                    <?php if(count($existing_routes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Откуда</th>
                                        <th>Куда</th>
                                        <th>Расстояние</th>
                                        <th>Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($existing_routes as $route): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($route['from_city']) ?> - <?= htmlspecialchars($route['from_address']) ?></td>
                                            <td><?= htmlspecialchars($route['to_city']) ?> - <?= htmlspecialchars($route['to_address']) ?></td>
                                            <td><?= $route['distance_km'] ?> км</td>
                                            <td>
                                                <form method="POST" class="d-inline" onsubmit="return confirm('Удалить маршрут?');">
                                                    <input type="hidden" name="action" value="delete_route">
                                                    <input type="hidden" name="from_office" value="<?= $route['from_office'] ?>">
                                                    <input type="hidden" name="to_office" value="<?= $route['to_office'] ?>">
                                                    <button type="submit" class="btn btn-danger btn-sm">Удалить</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-muted">Нет добавленных маршрутов</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Apply saved theme on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'dark') {
        document.body.classList.add('dark');
    }
    
    // Set up a global theme listener for cross-page consistency
    window.addEventListener('storage', function(e) {
        if (e.key === 'theme') {
            if (e.newValue === 'dark') {
                document.body.classList.add('dark');
            } else {
                document.body.classList.remove('dark');
            }
        }
    });
});
</script>
</body>
</html>