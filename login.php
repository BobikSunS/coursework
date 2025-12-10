<?php
require 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE login = ? AND password = ?");
    $stmt->execute([$login, $pass]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        if ($user['role'] === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: calculator.php');
        }
        exit;
    } else {
        $error = 'Неверный логин или пароль';
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Вход</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea, #764ba2); }
        .card { max-width: 420px; margin: 100px auto; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
<div class="container">
    <div class="card">
        <div class="card-body p-5 text-center">
            <h2 class="text-white mb-4">Вход в систему</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= $error ?></div>
            <?php endif; ?>
            <form method="POST" class="text-white">
                <input type="text" name="login" class="form-control form-control-lg mb-3" placeholder="Логин" required>
                <input type="password" name="password" class="form-control form-control-lg mb-4" placeholder="Пароль" required>
                <button type="submit" class="btn btn-light btn-lg w-100">ВОЙТИ</button>
            </form>
            <small class="text-white-50 d-block mt-3">admin / admin123</small>
            <a href="index.php" class="text-white mt-3 d-block">На главную</a>
        </div>
    </div>
</div>
</body>
</html>