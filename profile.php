<?php require 'db.php'; 
if (!isset($_SESSION['user'])) header('Location: index.php');
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Профиль</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-primary shadow-lg">
    <div class="container-fluid">
        <a class="navbar-brand">Профиль пользователя</a>
        <div>
            <a href="calculator.php" class="btn btn-light me-2">Калькулятор</a>
            <a href="order_form.php" class="btn btn-success me-2">Оформить заказ</a>
            <a href="history.php" class="btn btn-warning me-2">История</a>
            <?php if($user['role']==='admin'): ?><a href="admin/index.php" class="btn btn-danger me-2">Админка</a><?php endif; ?>
            <a href="logout.php" class="btn btn-outline-light">Выйти</a>
        </div>
    </div>
</nav>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card card-shadow">
                <div class="card-body text-center">
                    <h2>Привет, <?= htmlspecialchars($user['name'] ?: $user['login']) ?>!</h2>
                    <p class="lead">Роль: <strong><?= $user['role']==='admin'?'Администратор':'Пользователь' ?></strong></p>
                    <hr>
                    <div class="d-grid gap-3">
                        <a href="calculator.php" class="btn btn-primary btn-lg">Рассчитать доставку</a>
                        <a href="order_form.php" class="btn btn-success btn-lg">Оформить заказ</a>
                        <a href="history.php" class="btn btn-info btn-lg">История заказов</a>
                        <!-- New tracking functionality -->
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="card-title">Отследить заказ</h5>
                                <form method="GET" action="track.php" class="mb-3">
                                    <div class="input-group">
                                        <input type="text" name="track" class="form-control" placeholder="Введите трек-номер" required>
                                        <button type="submit" class="btn btn-primary">Отследить</button>
                                    </div>
                                </form>
                                <p class="text-muted small">Введите 12-значный трек-номер для отслеживания статуса доставки</p>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <button onclick="document.body.classList.toggle('dark')" class="btn btn-outline-warning btn-lg">
                            Тёмная тема
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="footer mt-5 py-4 bg-light border-top">
    <div class="container text-center">
        <p class="mb-1">&copy; 2025 Служба доставки. Все права защищены.</p>
        <p class="mb-1">Контактный телефон: +375 (29) 123-45-67</p>
        <p class="mb-0">Email: info@delivery.by</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>