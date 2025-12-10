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
        <a class="navbar-brand">Расчёт доставки</a>
        <div>
            <a href="calculator.php" class="btn btn-light me-2">К калькулятору</a>
            <a href="logout.php" class="btn btn-danger">Выйти</a>
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
                    <a href="calculator.php" class="btn btn-primary btn-lg">Рассчитать доставку</a>
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