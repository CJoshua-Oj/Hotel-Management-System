<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = post_string('username');
    $password = (string)($_POST['password'] ?? '');

    try {
        $stmt = db()->prepare('SELECT * FROM users WHERE username = ? AND active = 1 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int)$user['id'];
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
            $log = db()->prepare('INSERT INTO login_history (user_id, login_at, ip_address) VALUES (?, NOW(), ?)');
            $log->execute([(int)$user['id'], $_SERVER['REMOTE_ADDR'] ?? '']);
            redirect('dashboard.php');
        }
        $error = 'Invalid username or password.';
    } catch (Throwable $e) {
        $error = 'The system is not installed or the database connection is unavailable. Use install.php first.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Login - Hotel Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card">
    <div class="logo-mark">H</div>
    <h1>Hotel Management System</h1>
    <p class="muted">Administrator Login</p>
    <?php if ($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
        <label>Username<input name="username" autocomplete="username" required></label>
        <label>Password<input type="password" name="password" autocomplete="current-password" required></label>
        <button class="btn btn-primary full-btn" type="submit">Sign In</button>
    </form>
    <p class="small muted">First time setup? <a href="install.php">Install the system</a></p>
</div>
</body>
</html>
