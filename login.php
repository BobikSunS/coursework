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
    <title>Вход в систему</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body { 
            background: var(--bg-light);
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            align-items: center;
            transition: all 0.4s;
            font-family: 'Segoe UI', sans-serif;
        }
        body.dark { 
            background: var(--bg-dark);
            color: var(--text-dark);
        }
        .card { 
            max-width: 420px; 
            margin: 100px auto; 
            border-radius: 20px; 
            box-shadow: 0 15px 35px rgba(0,0,0,0.3); 
            background: var(--card-light) !important;
            color: var(--text-light) !important;
        }
        body.dark .card { 
            background: var(--card-dark) !important;
            color: var(--text-dark) !important;
        }
        .form-control, .btn {
            border-radius: 10px !important;
        }
        .login-title {
            font-weight: 600;
            margin-bottom: 1.5rem;
            color: var(--text-light);
        }
        body.dark .login-title {
            color: var(--text-dark);
        }
    </style>
</head>
<body>
<div class="container">
    <div class="card p-5 text-center">
        <h2 class="login-title">Вход в систему</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3 text-start">
                <label for="login" class="form-label">Логин</label>
                <input type="text" id="login" name="login" class="form-control form-control-lg" placeholder="Введите логин" required>
            </div>
            <div class="mb-4 text-start">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Введите пароль" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">ВОЙТИ</button>
        </form>
        <div class="mt-4">
            <small class="text-muted d-block mb-2">Демо-данные:</small>
            <small class="text-muted d-block">Логин: admin, Пароль: admin</small>
            <small class="text-muted d-block">или создайте свой аккаунт</small>
        </div>
        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary btn-sm">На главную</a>
            <a href="register.php" class="btn btn-outline-primary btn-sm ms-2">Регистрация</a>
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
});
</script>
</body>
</html>