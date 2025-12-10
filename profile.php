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
</body>
</html>