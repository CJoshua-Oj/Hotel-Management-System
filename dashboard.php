<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();

$pdo = db();
$stats = [];
$stats['customers'] = (int)$pdo->query('SELECT COUNT(*) FROM customers')->fetchColumn();
$stats['rooms'] = (int)$pdo->query('SELECT COUNT(*) FROM rooms')->fetchColumn();
$stats['available'] = (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn();
$stats['occupied'] = (int)$pdo->query("SELECT COUNT(*) FROM rooms WHERE status='occupied'")->fetchColumn();
$stats['reservations'] = (int)$pdo->query("SELECT COUNT(*) FROM reservations WHERE status IN ('pending','confirmed')")->fetchColumn();
$stats['today_checkins'] = (int)$pdo->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkin_date)=CURDATE()")->fetchColumn();
$stats['today_checkouts'] = (int)$pdo->query("SELECT COUNT(*) FROM checkins WHERE DATE(checkout_date)=CURDATE() AND checkout_status='completed'")->fetchColumn();

$recent = $pdo->query("
    SELECT r.id, r.reservation_date, r.checkin_date, r.checkout_date, r.status,
           c.first_name, c.last_name, rm.room_number
    FROM reservations r
    JOIN customers c ON c.id=r.customer_id
    LEFT JOIN rooms rm ON rm.id=r.room_id
    ORDER BY r.id DESC LIMIT 8
")->fetchAll();

page_start('Dashboard', 'dashboard');
?>
<div class="page-head">
    <div><h1>Dashboard</h1><p class="muted">Hotel operations overview</p></div>
    <a class="btn btn-primary" href="reservation_form.php">New Reservation</a>
</div>

<div class="stats-grid">
    <div class="stat-card"><span>Customers</span><strong><?= $stats['customers'] ?></strong></div>
    <div class="stat-card"><span>Total Rooms</span><strong><?= $stats['rooms'] ?></strong></div>
    <div class="stat-card"><span>Available</span><strong><?= $stats['available'] ?></strong></div>
    <div class="stat-card"><span>Occupied</span><strong><?= $stats['occupied'] ?></strong></div>
    <div class="stat-card"><span>Active Reservations</span><strong><?= $stats['reservations'] ?></strong></div>
    <div class="stat-card"><span>Today's Check-ins</span><strong><?= $stats['today_checkins'] ?></strong></div>
    <div class="stat-card"><span>Today's Check-outs</span><strong><?= $stats['today_checkouts'] ?></strong></div>
</div>

<div class="card">
    <div class="card-head"><h2>Recent Reservations</h2><a href="reservations.php">View all</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($recent as $row): ?>
                <tr>
                    <td><?= e($row['first_name'] . ' ' . $row['last_name']) ?></td>
                    <td><?= e($row['room_number'] ?? 'Not assigned') ?></td>
                    <td><?= e($row['checkin_date']) ?></td>
                    <td><?= e($row['checkout_date']) ?></td>
                    <td><span class="badge <?= e($row['status']) ?>"><?= e(ucfirst($row['status'])) ?></span></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$recent): ?><tr><td colspan="5" class="empty">No reservations yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php page_end(); ?>
