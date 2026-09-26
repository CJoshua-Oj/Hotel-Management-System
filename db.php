<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        $safe = htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        echo '<!doctype html><html><head><meta charset="utf-8"><title>Database Error</title>';
        echo '<link rel="stylesheet" href="assets/css/style.css"></head><body class="error-page">';
        echo '<div class="error-card"><h1>Database connection failed</h1>';
        echo '<p>Check the database settings in <code>config.php</code>, make sure MySQL is running, and create/import the database.</p>';
        echo '<details><summary>Technical message</summary><pre>' . $safe . '</pre></details>';
        echo '</div></body></html>';
        exit;
    }

    return $pdo;
}
