<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=post_int('id');$action=post_string('action');
 if($id&&$action==='delete'){$s=$pdo->prepare('DELETE FROM reservations WHERE id=?');$s->execute([$id]);flash('success','Reservation deleted.');}
 if($id&&$action==='confirm'){$s=$pdo->prepare("UPDATE reservations SET status='confirmed' WHERE id=?");$s->execute([$id]);flash('success','Reservation confirmed.');}
 if($id&&$action==='cancel'){$s=$pdo->prepare("UPDATE reservations SET status='cancelled' WHERE id=?");$s->execute([$id]);flash('success','Reservation cancelled.');}
 redirect('reservations.php');
}
$q=trim((string)($_GET['q']??''));
$sql="SELECT r.*,CONCAT(c.first_name,' ',c.last_name) guest,rm.room_number,t.name room_type
      FROM reservations r JOIN customers c ON c.id=r.customer_id
      LEFT JOIN rooms rm ON rm.id=r.room_id LEFT JOIN room_types t ON t.id=rm.room_type_id";
$params=[];
if($q!==''){$sql.=" WHERE c.first_name LIKE ? OR c.last_name LIKE ? OR rm.room_number LIKE ? OR r.status LIKE ?";$like="%$q%";$params=[$like,$like,$like,$like];}
$sql.=" ORDER BY r.id DESC";
$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
page_start('Reservations','reservations');
?>
<div class="page-head"><div><h1>Reservations</h1><p class="muted">Bookings and reservation status</p></div><a class="btn btn-primary" href="reservation_form.php">New Reservation</a></div>
<form class="toolbar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search guest, room or status"><button class="btn">Search</button><a class="btn btn-light" href="reservations.php">Clear</a></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Room</th><th>Booked</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr>
<td><?= e($r['guest']) ?></td><td><?= e($r['room_number']??'Not assigned') ?></td><td><?= e($r['reservation_date']) ?></td><td><?= e($r['checkin_date']) ?></td><td><?= e($r['checkout_date']) ?></td><td><?= money($r['total_amount']) ?></td><td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td>
<td class="actions"><a href="reservation_form.php?id=<?= (int)$r['id'] ?>">Edit</a>
<?php if($r['status']==='pending'): ?><form method="post"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="confirm"><button>Confirm</button></form><?php endif; ?>
<form method="post" onsubmit="return confirm('Delete this reservation?');"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="action" value="delete"><button class="link-danger">Delete</button></form></td>
</tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="8" class="empty">No reservations found.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_end(); ?>
