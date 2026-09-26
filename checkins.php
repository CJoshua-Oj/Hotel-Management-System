<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
$rows=$pdo->query("SELECT ch.*,CONCAT(c.first_name,' ',c.last_name) guest,rm.room_number
                   FROM checkins ch JOIN customers c ON c.id=ch.customer_id
                   JOIN rooms rm ON rm.id=ch.room_id
                   WHERE ch.checkout_status='active' ORDER BY ch.id DESC")->fetchAll();
page_start('Check-in','checkins');
?>
<div class="page-head"><div><h1>Check-in</h1><p class="muted">Current guests staying in the hotel</p></div><a class="btn btn-primary" href="checkin_form.php">Check In Guest</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Expected Check-out</th><th>Guests</th><th>Amount</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['guest']) ?></td><td><?= e($r['room_number']) ?></td><td><?= e($r['checkin_date']) ?></td><td><?= e($r['checkout_date']) ?></td><td><?= (int)$r['adults'] ?> / <?= (int)$r['children'] ?></td><td><?= money($r['amount']) ?></td><td><a class="btn btn-small" href="checkouts.php?id=<?= (int)$r['id'] ?>">Check out</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="7" class="empty">No active check-ins.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_end(); ?>
