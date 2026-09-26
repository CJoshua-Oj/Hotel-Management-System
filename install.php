<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $host = trim((string)($_POST['db_host'] ?? DB_HOST));
    $port = trim((string)($_POST['db_port'] ?? DB_PORT));
    $name = trim((string)($_POST['db_name'] ?? DB_NAME));
    $user = trim((string)($_POST['db_user'] ?? DB_USER));
    $pass = (string)($_POST['db_pass'] ?? DB_PASS);
    $adminUser = trim((string)($_POST['admin_user'] ?? 'admin'));
    $adminPass = (string)($_POST['admin_pass'] ?? 'admin123');
    $adminEmail = trim((string)($_POST['admin_email'] ?? 'admin@example.com'));

    try {
        $server = new PDO(
            "mysql:host={$host};port={$port};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $server->exec("CREATE DATABASE IF NOT EXISTS `" . str_replace('`', '``', $name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        $pdo = new PDO(
            "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4",
            $user,
            $pass,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        $sql = file_get_contents(__DIR__ . '/database.sql');
        if ($sql === false) {
            throw new RuntimeException('database.sql could not be read.');
        }

        $pdo->exec($sql);

        $stmt = $pdo->prepare('INSERT INTO users (first_name,last_name,email,username,password_hash,role) VALUES (?,?,?,?,?,?)');
        $stmt->execute([
            'System',
            'Administrator',
            $adminEmail,
            $adminUser,
            password_hash($adminPass, PASSWORD_DEFAULT),
            'admin'
        ]);

        $settings = [
            'hotel_name' => 'My Hotel',
            'hotel_phone' => '',
            'hotel_email' => '',
            'hotel_address' => ''
        ];
        $s = $pdo->prepare('INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
        foreach ($settings as $k => $v) {
            $s->execute([$k, $v]);
        }

        $success = true;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Install - Hotel Management System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="auth-page">
<div class="auth-card wide">
    <h1>Hotel Management System</h1>
    <p class="muted">PHP 8.3 installation</p>
    <?php if ($success): ?>
        <div class="alert success">Installation completed successfully.</div>
        <p><a class="btn btn-primary" href="login.php">Go to Login</a></p>
    <?php else: ?>
        <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <form method="post">
            <h2>Database</h2>
            <div class="form-grid">
                <label>Host<input name="db_host" value="<?= htmlspecialchars($_POST['db_host'] ?? DB_HOST, ENT_QUOTES) ?>"></label>
                <label>Port<input name="db_port" value="<?= htmlspecialchars($_POST['db_port'] ?? DB_PORT, ENT_QUOTES) ?>"></label>
                <label>Database<input name="db_name" value="<?= htmlspecialchars($_POST['db_name'] ?? DB_NAME, ENT_QUOTES) ?>"></label>
                <label>Username<input name="db_user" value="<?= htmlspecialchars($_POST['db_user'] ?? DB_USER, ENT_QUOTES) ?>"></label>
                <label class="full">Password<input type="password" name="db_pass" value="<?= htmlspecialchars($_POST['db_pass'] ?? DB_PASS, ENT_QUOTES) ?>"></label>
            </div>
            <h2>Administrator</h2>
            <div class="form-grid">
                <label>Username<input name="admin_user" value="<?= htmlspecialchars($_POST['admin_user'] ?? 'admin', ENT_QUOTES) ?>" required></label>
                <label>Email<input type="email" name="admin_email" value="<?= htmlspecialchars($_POST['admin_email'] ?? 'admin@example.com', ENT_QUOTES) ?>" required></label>
                <label class="full">Password<input type="password" name="admin_pass" value="<?= htmlspecialchars($_POST['admin_pass'] ?? 'admin123', ENT_QUOTES) ?>" required minlength="6"></label>
            </div>
            <button class="btn btn-primary" type="submit">Install System</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
