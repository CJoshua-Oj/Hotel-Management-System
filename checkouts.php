<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=post_int('id');$payment=(float)($_POST['payment_amount']??0);$method=post_string('payment_method','cash');
 if($id){
  try{$pdo->beginTransaction();
   $s=$pdo->prepare("SELECT * FROM checkins WHERE id=? AND checkout_status='active' FOR UPDATE");$s->execute([$id]);$ch=$s->fetch();
   if(!$ch) throw new RuntimeException('Active check-in not found.');
   $pdo->prepare("UPDATE checkins SET checkout_status='completed',actual_checkout=NOW() WHERE id=?")->execute([$id]);
   $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([(int)$ch['room_id']]);
   if($payment>0){$p=$pdo->prepare('INSERT INTO payments(checkin_id,amount,payment_method,payment_date,received_by) VALUES(?,?,?,?,?)');$p->execute([$id,$payment,$method,date('Y-m-d H:i:s'),(int)$_SESSION['admin_id']]);}
   $pdo->commit();flash('success','Guest checked out successfully.');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Checkout failed: '.$e->getMessage());}
 }
 redirect('checkouts.php');
}

$id=filter_var($_GET['id']??0,FILTER_VALIDATE_INT)?:0;
$selected=null;
if($id){$s=$pdo->prepare("SELECT ch.*,CONCAT(c.first_name,' ',c.last_name) guest,rm.room_number,t.price FROM checkins ch JOIN customers c ON c.id=ch.customer_id JOIN rooms rm ON rm.id=ch.room_id JOIN room_types t ON t.id=rm.room_type_id WHERE ch.id=? AND ch.checkout_status='active'");$s->execute([$id]);$selected=$s->fetch();}
$rows=$pdo->query("SELECT ch.id,ch.checkout_date,ch.amount,CONCAT(c.first_name,' ',c.last_name) guest,rm.room_number FROM checkins ch JOIN customers c ON c.id=ch.customer_id JOIN rooms rm ON rm.id=ch.room_id WHERE ch.checkout_status='active' ORDER BY ch.checkout_date")->fetchAll();

page_start('Check-out','checkouts');
?>
<div class="page-head"><div><h1>Check-out</h1><p class="muted">Complete guest departures and record payment</p></div></div>
<?php if($selected): ?>
<div class="card">
<h2>Checkout: <?= e($selected['guest']) ?> &middot; Room <?= e($selected['room_number']) ?></h2>
<p>Expected checkout: <strong><?= e($selected['checkout_date']) ?></strong> &middot; Bill: <strong><?= money($selected['amount']) ?></strong></p>
<form method="post" class="form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$selected['id'] ?>">
<label>Payment Amount<input type="number" step="0.01" min="0" name="payment_amount" value="<?= e($selected['amount']) ?>"></label>
<label>Payment Method<select name="payment_method"><?php foreach(['cash','card','bank_transfer','cheque'] as $m): ?><option value="<?= $m ?>"><?= ucwords(str_replace('_',' ',$m)) ?></option><?php endforeach; ?></select></label>
<div class="full"><button class="btn btn-primary">Complete Checkout</button></div></form>
</div>
<?php endif; ?>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Guest</th><th>Room</th><th>Expected Check-out</th><th>Bill</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r): ?><tr><td><?= e($r['guest']) ?></td><td><?= e($r['room_number']) ?></td><td><?= e($r['checkout_date']) ?></td><td><?= money($r['amount']) ?></td><td><a class="btn btn-small" href="checkouts.php?id=<?= (int)$r['id'] ?>">Open</a></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="5" class="empty">No active guests.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_end(); ?>
