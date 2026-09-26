<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
$customers=$pdo->query('SELECT id,first_name,last_name FROM customers ORDER BY first_name,last_name')->fetchAll();
$rooms=$pdo->query("SELECT r.id,r.room_number,t.name,t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id WHERE r.status='available' ORDER BY r.room_number")->fetchAll();

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$customer=post_int('customer_id');$room=post_int('room_id');$in=post_string('checkin_date');$out=post_string('checkout_date');$adults=post_int('adults',1);$children=post_int('children',0);$discount=(float)($_POST['discount']??0);$extra=(float)($_POST['extra_charges']??0);
 if(!$customer||!$room||!valid_date($in)||!valid_date($out)||$out<$in){flash('error','Please provide valid guest, room and dates.');}
 else{
   $days=max(1,(new DateTime($in))->diff(new DateTime($out))->days);
   $s=$pdo->prepare('SELECT t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id WHERE r.id=?');$s->execute([$room]);$rate=(float)$s->fetchColumn();
   $amount=max(0,$days*$rate+$extra-$discount);
   try{$pdo->beginTransaction();
      $s=$pdo->prepare('INSERT INTO checkins(customer_id,room_id,checkin_date,checkout_date,adults,children,discount,extra_charges,amount,checkout_status,created_by) VALUES(?,?,?,?,?,?,?,?,?,?,?)');
      $s->execute([$customer,$room,$in,$out,$adults,$children,$discount,$extra,$amount,'active',(int)$_SESSION['admin_id']]);
      $pdo->prepare("UPDATE rooms SET status='occupied' WHERE id=?")->execute([$room]);
      $pdo->prepare("UPDATE reservations SET status='completed' WHERE customer_id=? AND room_id=? AND checkin_date=? AND status IN ('pending','confirmed')")->execute([$customer,$room,$in]);
      $pdo->commit();flash('success','Guest checked in successfully.');redirect('checkins.php');
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();flash('error','Check-in failed: '.$e->getMessage());}
 }
}
page_start('Check-in Guest','checkins');
?>
<div class="page-head"><div><h1>Check In Guest</h1></div><a class="btn btn-light" href="checkins.php">Back</a></div>
<form method="post" class="card form-grid"><?= csrf_field() ?>
<label class="full">Customer<select name="customer_id" required><option value="">Select customer</option><?php foreach($customers as $c): ?><option value="<?= (int)$c['id'] ?>"><?= e($c['first_name'].' '.$c['last_name']) ?></option><?php endforeach; ?></select></label>
<label>Room<select name="room_id" required><option value="">Select available room</option><?php foreach($rooms as $r): ?><option value="<?= (int)$r['id'] ?>"><?= e($r['room_number'].' - '.$r['name'].' ('.money($r['price']).'/night)') ?></option><?php endforeach; ?></select></label>
<label>Check-in Date<input type="date" name="checkin_date" value="<?= date('Y-m-d') ?>" required></label>
<label>Check-out Date<input type="date" name="checkout_date" value="<?= date('Y-m-d',strtotime('+1 day')) ?>" required></label>
<label>Adults<input type="number" name="adults" min="1" value="1"></label>
<label>Children<input type="number" name="children" min="0" value="0"></label>
<label>Discount<input type="number" step="0.01" min="0" name="discount" value="0"></label>
<label>Extra Charges<input type="number" step="0.01" min="0" name="extra_charges" value="0"></label>
<div class="full"><button class="btn btn-primary">Complete Check-in</button></div>
</form>
<?php page_end(); ?>
