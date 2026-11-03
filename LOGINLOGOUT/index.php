<?php
session_start();

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$form = ($_GET['form'] ?? 'signin') === 'signup' ? 'signup' : 'signin';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrfToken = htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register &amp; Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php if (isset($_SESSION['user_id'])): ?>
    <div class="container">
        <h1 class="form-title">Signed In</h1>
        <p class="account-message">Welcome, <?= htmlspecialchars($_SESSION['user_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
        <form method="post" action="auth.php">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit" class="btn" name="action" value="logout">Log Out</button>
        </form>
    </div>
<?php else: ?>
    <div class="container" id="signup" style="<?= $form === 'signup' ? '' : 'display: none;' ?>">
        <h1 class="form-title">Register</h1>

        <?php if ($flash && $form === 'signup'): ?>
            <p class="form-message" role="status"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="auth.php">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="first_name" id="Fname" placeholder="First Name" autocomplete="given-name" maxlength="100" required>
                <label for="Fname">First Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="last_name" id="Lname" placeholder="Last Name" autocomplete="family-name" maxlength="100" required>
                <label for="Lname">Last Name</label>
            </div>

            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="email" placeholder="Email" autocomplete="email" maxlength="191" required>
                <label for="email">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="password" placeholder="Password" autocomplete="new-password" minlength="8" required>
                <label for="password">Password</label>
            </div>

            <button type="submit" class="btn" name="action" value="register">Sign Up</button>
        </form>

        <p class="or">-------------- or --------------</p>
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
        <div class="links">
            <p>Already have an account?</p>
            <button type="button" id="signInButton">Sign In</button>
        </div>
    </div>

    <div class="container" id="signIn" style="<?= $form === 'signup' ? 'display: none;' : '' ?>">
        <h1 class="form-title">Sign In</h1>

        <?php if ($flash && $form === 'signin'): ?>
            <p class="form-message" role="status"><?= htmlspecialchars($flash, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <form method="post" action="auth.php">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" id="loginEmail" placeholder="Email" autocomplete="email" required>
                <label for="loginEmail">Email</label>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" id="loginPassword" placeholder="Password" autocomplete="current-password" required>
                <label for="loginPassword">Password</label>
            </div>

            <button type="submit" class="btn" name="action" value="signin">Sign In</button>
        </form>

        <p class="or">-------------- or --------------</p>
        <div class="icons">
            <i class="fab fa-google"></i>
            <i class="fab fa-facebook"></i>
        </div>
        <div class="links">
            <p>Don't have an account yet?</p>
            <button type="button" id="signUpButton">Sign Up</button>
        </div>
    </div>
<?php endif; ?>

<script src="script.js"></script>
</body>
</html>