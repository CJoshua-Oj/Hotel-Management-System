<?php
declare(strict_types=1);

const APP_NAME = 'Hotel Management System';
const APP_VERSION = '1.0.0';

define('DB_HOST', getenv('HMS_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('HMS_DB_PORT') ?: '3306');
define('DB_NAME', getenv('HMS_DB_NAME') ?: 'hotel_management');
define('DB_USER', getenv('HMS_DB_USER') ?: 'root');
define('DB_PASS', getenv('HMS_DB_PASS') !== false ? getenv('HMS_DB_PASS') : 'guards');

date_default_timezone_set(getenv('HMS_TIMEZONE') ?: 'Africa/Lagos');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('hotel_management_session');
    session_start();
}
