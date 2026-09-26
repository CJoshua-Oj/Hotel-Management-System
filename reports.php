<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
$report=post_string('report', (string)($_GET['report']??'overview'));

$rows=[];$title='Overview';
switch($report){
 case 'customers':$title='Customer Report';$rows=$pdo->query('SELECT id,first_name,last_name,gender,phone,email,nationality,created_at FROM customers ORDER BY id DESC')->fetchAll();break;
 case 'rooms':$title='Room Report';$rows=$pdo->query('SELECT r.room_number,t.name room_type,r.floor,r.status,t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id ORDER BY r.room_number')->fetchAll();break;
 case 'available':$title='Available Rooms';$rows=$pdo->query("SELECT r.room_number,t.name room_type,r.floor,t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id WHERE r.status='available' ORDER BY r.room_number")->fetchAll();break;
 case 'occupied':$title='Occupied Rooms';$rows=$pdo->query("SELECT r.room_number,t.name room_type,r.floor,t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id WHERE r.status='occupied' ORDER BY r.room_number")->fetchAll();break;
 case 'checkins':$title='Check-in Report';$rows=$pdo->query("SELECT ch.checkin_date,ch.checkout_date,CONCAT(c.first_name,' ',c.last_name) guest,r.room_number,ch.amount,ch.checkout_status FROM checkins ch JOIN customers c ON c.id=ch.customer_id JOIN rooms r ON r.id=ch.room_id ORDER BY ch.id DESC")->fetchAll();break;
 case 'checkouts':$title='Completed Check-outs';$rows=$pdo->query("SELECT ch.actual_checkout,CONCAT(c.first_name,' ',c.last_name) guest,r.room_number,ch.amount FROM checkins ch JOIN customers c ON c.id=ch.customer_id JOIN rooms r ON r.id=ch.room_id WHERE ch.checkout_status='completed' ORDER BY ch.actual_checkout DESC")->fetchAll();break;
 default:
   $revenue=(float)$pdo->query('SELECT COALESCE(SUM(amount),0) FROM payments')->fetchColumn();
   $allCheckins=(int)$pdo->query('SELECT COUNT(*) FROM checkins')->fetchColumn();
   $completed=(int)$pdo->query("SELECT COUNT(*) FROM checkins WHERE checkout_status='completed'")->fetchColumn();
}
page_start('Reports','reports');
?>
<div class="page-head"><div><h1>Reports</h1><p class="muted">Operational and financial summaries</p></div></div>
<form class="toolbar" method="get"><select name="report"><option value="overview" <?= $report==='overview'?'selected':'' ?>>Overview</option><option value="customers" <?= $report==='customers'?'selected':'' ?>>Customers</option><option value="rooms" <?= $report==='rooms'?'selected':'' ?>>All Rooms</option><option value="available" <?= $report==='available'?'selected':'' ?>>Available Rooms</option><option value="occupied" <?= $report==='occupied'?'selected':'' ?>>Occupied Rooms</option><option value="checkins" <?= $report==='checkins'?'selected':'' ?>>Check-ins</option><option value="checkouts" <?= $report==='checkouts'?'selected':'' ?>>Check-outs</option></select><button class="btn">Run Report</button></form>
<?php if($report==='overview'): ?>
<div class="stats-grid"><div class="stat-card"><span>Total Payments</span><strong><?= money($revenue) ?></strong></div><div class="stat-card"><span>Total Check-ins</span><strong><?= $allCheckins ?></strong></div><div class="stat-card"><span>Completed Check-outs</span><strong><?= $completed ?></strong></div></div>
<?php else: ?>
<div class="card"><div class="card-head"><h2><?= e($title) ?></h2><button class="btn btn-light" onclick="window.print();return false;">Print</button></div><div class="table-wrap"><table><thead>
<?php if($report==='customers'): ?><tr><th>Name</th><th>Gender</th><th>Phone</th><th>Email</th><th>Nationality</th><th>Created</th></tr>
<?php elseif(in_array($report,['rooms','available','occupied'],true)): ?><tr><th>Room</th><th>Type</th><th>Floor</th><th>Status</th><th>Rate</th></tr>
<?php elseif($report==='checkins'): ?><tr><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Amount</th><th>Status</th></tr>
<?php else: ?><tr><th>Guest</th><th>Room</th><th>Checkout</th><th>Amount</th></tr><?php endif; ?>
</thead><tbody>
<?php foreach($rows as $r): ?>
<tr>
<?php if($report==='customers'): ?><td><?= e($r['first_name'].' '.$r['last_name']) ?></td><td><?= e($r['gender']) ?></td><td><?= e($r['phone']) ?></td><td><?= e($r['email']) ?></td><td><?= e($r['nationality']) ?></td><td><?= e($r['created_at']) ?></td>
<?php elseif(in_array($report,['rooms','available','occupied'],true)): ?><td><?= e($r['room_number']) ?></td><td><?= e($r['room_type']) ?></td><td><?= e($r['floor']) ?></td><td><?= e($r['status']??'available') ?></td><td><?= money($r['price']) ?></td>
<?php elseif($report==='checkins'): ?><td><?= e($r['guest']) ?></td><td><?= e($r['room_number']) ?></td><td><?= e($r['checkin_date']) ?></td><td><?= e($r['checkout_date']) ?></td><td><?= money($r['amount']) ?></td><td><?= e($r['checkout_status']) ?></td>
<?php else: ?><td><?= e($r['guest']) ?></td><td><?= e($r['room_number']) ?></td><td><?= e($r['actual_checkout']) ?></td><td><?= money($r['amount']) ?></td><?php endif; ?>
</tr>
<?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="6" class="empty">No data available.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php endif; ?>
<?php page_end(); ?>
