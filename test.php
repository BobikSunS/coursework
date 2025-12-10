<?php
require 'db.php';
echo "<h1>База работает!</h1>";
$result = $db->query("SELECT * FROM users WHERE login = 'admin'")->fetch();
echo "Админ найден: " . $result['login'] . "<br>";
echo "Роль: " . $result['role'];
?>