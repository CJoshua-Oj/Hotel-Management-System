<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $items = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return is_array($items) ? $items : [];
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return;
    }
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid or expired security token.');
    }
}

function is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function current_admin(): ?array
{
    if (!is_logged_in()) {
        return null;
    }

    static $admin = false;
    if ($admin !== false) {
        return $admin;
    }

    $stmt = db()->prepare('SELECT id, first_name, last_name, email, username FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int)$_SESSION['admin_id']]);
    $admin = $stmt->fetch() ?: null;
    return $admin;
}

function old(string $key, mixed $default = ''): mixed
{
    return $_POST[$key] ?? $default;
}

function post_string(string $key, string $default = ''): string
{
    $value = $_POST[$key] ?? $default;
    return is_string($value) ? trim($value) : $default;
}

function post_int(string $key, int $default = 0): int
{
    return filter_var($_POST[$key] ?? $default, FILTER_VALIDATE_INT) !== false
        ? (int)$_POST[$key]
        : $default;
}

function money(mixed $amount): string
{
    return number_format((float)$amount, 2);
}

function valid_date(string $date): bool
{
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d !== false && $d->format('Y-m-d') === $date;
}

function setting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        try {
            $rows = db()->query('SELECT setting_key, setting_value FROM settings')->fetchAll();
            foreach ($rows as $row) {
                $settings[$row['setting_key']] = (string)$row['setting_value'];
            }
        } catch (Throwable) {
            // Database may not have been installed yet.
        }
    }
    return $settings[$key] ?? $default;
}

function page_start(string $title, string $active = ''): void
{
    $admin = current_admin();
    $flashes = consume_flash();
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> - <?= e(APP_NAME) ?></title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
    <header class="topbar">
        <div class="brand">
            <a href="dashboard.php"><?= e(setting('hotel_name', APP_NAME)) ?></a>
            <span>Hotel Management System</span>
        </div>
        <div class="top-actions">
            <span><?= e(trim(($admin['first_name'] ?? '') . ' ' . ($admin['last_name'] ?? ''))) ?></span>
            <a class="btn btn-light btn-small" href="logout.php">Logout</a>
        </div>
    </header>
    <div class="layout">
        <aside class="sidebar">
            <nav>
                <a class="<?= $active === 'dashboard' ? 'active' : '' ?>" href="dashboard.php">Dashboard</a>
                <a class="<?= $active === 'customers' ? 'active' : '' ?>" href="customers.php">Customers</a>
                <a class="<?= $active === 'room_types' ? 'active' : '' ?>" href="room_types.php">Room Types</a>
                <a class="<?= $active === 'rooms' ? 'active' : '' ?>" href="rooms.php">Rooms</a>
                <a class="<?= $active === 'reservations' ? 'active' : '' ?>" href="reservations.php">Reservations</a>
                <a class="<?= $active === 'checkins' ? 'active' : '' ?>" href="checkins.php">Check-in</a>
                <a class="<?= $active === 'checkouts' ? 'active' : '' ?>" href="checkouts.php">Check-out</a>
                <a class="<?= $active === 'reports' ? 'active' : '' ?>" href="reports.php">Reports</a>
                <a class="<?= $active === 'settings' ? 'active' : '' ?>" href="settings.php">Hotel Settings</a>
            </nav>
        </aside>
        <main class="content">
            <?php foreach ($flashes as $flash): ?>
                <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
            <?php endforeach; ?>
    <?php
}

function page_end(): void
{
    ?>
        </main>
    </div>
    <footer class="footer"><?= e(APP_NAME) ?> &middot; PHP 8.3</footer>
    </body>
    </html>
    <?php
}

function require_install(): void
{
    try {
        db()->query('SELECT 1 FROM users LIMIT 1');
    } catch (Throwable) {
        redirect('install.php');
    }
}
