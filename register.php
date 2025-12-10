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
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Регистрация</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea, #764ba2);
            min-height: 100vh;
        }
        .register-card {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        body.dark .register-card {
            background: #16213e !important;
        }
    </style>
</head>
<body>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card register-card shadow-lg">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">Регистрация</h3>
                    <?php if($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <?php if($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Логин</label>
                            <input type="text" name="login" class="form-control" placeholder="Введите логин" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Пароль</label>
                            <input type="password" name="password" class="form-control" placeholder="Введите пароль" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Имя</label>
                            <input type="text" name="name" class="form-control" placeholder="Введите ваше имя">
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Введите email">
                        </div>
                        <button class="btn btn-success w-100 btn-lg">Создать аккаунт</button>
                    </form>
                    <a href="login.php" class="btn btn-link d-block text-center mt-3">Уже есть аккаунт? Войти</a>
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