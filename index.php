<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}
redirect('login.php');
