<?php
$dsn = 'mysql:host=localhost;dbname=login_system;charset=utf8mb4';
$username = 'root';
$password = '';

return new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
]);