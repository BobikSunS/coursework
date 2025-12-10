<?php 
require 'db.php';
$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $pass = $_POST['password'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (strlen($pass) < 3) {
        $error = "Пароль слишком короткий";
    } elseif ($db->query("SELECT id FROM users WHERE login = '$login'")->fetch()) {
        $error = "Логин уже занят";
    } else {
        $db->prepare("INSERT INTO users (login, password, name, email, role) VALUES (?,?,?,?,'user')")
            ->execute([$login, $pass, $name, $email]);
        $success = "Аккаунт создан! Теперь войдите.";
    }
}
?>
<!DOCTYPE html>
<html lang="ru">
<head><meta charset="UTF-8"><title>Регистрация</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-gradient" style="background: linear-gradient(135deg, #667eea, #764ba2); min-height:100vh;">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Регистрация</h3>
                    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                    <form method="POST">
                        <input type="text" name="login" class="form-control mb-3" placeholder="Логин" required>
                        <input type="password" name="password" class="form-control mb-3" placeholder="Пароль" required>
                        <input type="text" name="name" class="form-control mb-3" placeholder="Имя (не обязательно)">
                        <input type="email" name="email" class="form-control mb-4" placeholder="Email (не обязательно)">
                        <button class="btn btn-success w-100 btn-lg">Создать аккаунт</button>
                    </form>
                    <a href="login.php" class="btn btn-link d-block text-center mt-3">Уже есть аккаунт? Войти</a>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>