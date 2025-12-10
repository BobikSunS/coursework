<?php require 'db.php'; 
if (isset($_SESSION['user'])) header('Location: calculator.php');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Расчёт доставки по Беларуси</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height:100vh">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card card-shadow bg-white p-5">
                <h1 class="text-center mb-4 fw-bold text-primary">Расчёт доставки</h1>
                <p class="text-center text-muted mb-4">Автоматизированная система расчёта почтовых маршрутов</p>
                <a href="login.php" class="btn btn-primary btn-lg w-100 mb-3">Войти</a>
                <a href="register.php" class="btn btn-outline-success btn-lg w-100">Регистрация</a>
                <hr>
                <small class="text-center d-block text-muted">Админ: admin / admin123</small>
            </div>
        </div>
    </div>
</div>
</body>
</html>