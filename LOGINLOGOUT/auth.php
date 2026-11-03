<?php
session_start();

function redirectToForm(string $form = 'signin'): void
{
    header('Location: index.php' . ($form === 'signup' ? '?form=signup' : ''));
    exit;
}

function failWithMessage(string $message, string $form = 'signin'): void
{
    $_SESSION['flash'] = $message;
    redirectToForm($form);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirectToForm();
}

$action = $_POST['action'] ?? '';
$actionAliases = [
    'Sign Up' => 'register',
    'Sign In' => 'signin',
];
$action = $actionAliases[$action] ?? $action;
$form = $action === 'register' ? 'signup' : 'signin';

if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    failWithMessage('Your session expired. Please try again.', $form);
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $cookie = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $cookie['path'], $cookie['domain'], $cookie['secure'], $cookie['httponly']);
    }
    session_destroy();
    redirectToForm();
}

if ($action === 'register') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if ($firstName === '' || strlen($firstName) > 100 || $lastName === '' || strlen($lastName) > 100) {
        failWithMessage('Enter a first and last name, up to 100 characters each.', 'signup');
    }
    if (strlen($email) > 191 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        failWithMessage('Enter a valid email address.', 'signup');
    }
    if (strlen($password) < 8) {
        failWithMessage('Use a password with at least 8 characters.', 'signup');
    }

    try {
        $pdo = require __DIR__ . '/db.php';
        $statement = $pdo->prepare('INSERT INTO users (first_name, last_name, email, password_hash) VALUES (?, ?, ?, ?)');
        $statement->execute([$firstName, $lastName, $email, password_hash($password, PASSWORD_DEFAULT)]);
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        if ($exception->getCode() === '23000') {
            failWithMessage('An account with that email already exists.', 'signup');
        }
        failWithMessage('The database is unavailable. Check that MySQL is running and configured.', 'signup');
    }

    failWithMessage('Account created. You can now sign in.', 'signin');
}

if ($action === 'signin') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        failWithMessage('Email or password is incorrect.');
    }

    try {
        $pdo = require __DIR__ . '/db.php';
        $statement = $pdo->prepare('SELECT id, first_name, last_name, password_hash FROM users WHERE email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();
    } catch (PDOException $exception) {
        error_log($exception->getMessage());
        failWithMessage('The database is unavailable. Check that MySQL is running and configured.');
    }

    if (!$user || !password_verify($password, $user['password_hash'])) {
        failWithMessage('Email or password is incorrect.');
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
    redirectToForm();
}

redirectToForm();