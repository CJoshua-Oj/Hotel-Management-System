<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();$id=filter_var($_GET['id']??$_POST['id']??0,FILTER_VALIDATE_INT)?:0;
$r=['customer_id'=>'','room_id'=>'','checkin_date'=>date('Y-m-d'),'checkout_date'=>date('Y-m-d',strtotime('+1 day')),'adults'=>1,'children'=>0,'special_request'=>'','status'=>'pending'];
if($id){$s=$pdo->prepare('SELECT * FROM reservations WHERE id=?');$s->execute([$id]);$r=$s->fetch()?:$r;}
$customers=$pdo->query('SELECT id,first_name,last_name FROM customers ORDER BY first_name,last_name')->fetchAll();
$rooms=$pdo->query("SELECT rm.id,rm.room_number,t.name,t.price FROM rooms rm JOIN room_types t ON t.id=rm.room_type_id WHERE rm.status IN ('available','occupied') ORDER BY rm.room_number")->fetchAll();

function reservation_total(PDO $pdo,int $roomId,string $in,string $out): float {
    if(!$roomId || !valid_date($in)||!valid_date($out)) return 0;
    $days=(new DateTime($in))->diff(new DateTime($out))->days;
    if($days<1) return 0;
    $s=$pdo->prepare('SELECT t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id WHERE r.id=?');
    $s->execute([$roomId]);$price=(float)($s->fetchColumn()?:0);
    return $days*$price;
}

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $customer=post_int('customer_id');$room=post_int('room_id');$in=post_string('checkin_date');$out=post_string('checkout_date');
 $adults=post_int('adults',1);$children=post_int('children',0);$special=post_string('special_request');$status=post_string('status','pending');
 $total=reservation_total($pdo,$room,$in,$out);
 if(!$customer||!$room||!valid_date($in)||!valid_date($out)||$out<=$in){flash('error','Please provide a customer, room and valid dates.');}
 elseif($id){$s=$pdo->prepare('UPDATE reservations SET customer_id=?,room_id=?,checkin_date=?,checkout_date=?,adults=?,children=?,special_request=?,status=?,total_amount=? WHERE id=?');$s->execute([$customer,$room,$in,$out,$adults,$children,$special,$status,$total,$id]);flash('success','Reservation updated.');redirect('reservations.php');}
 else{$s=$pdo->prepare('INSERT INTO reservations(customer_id,room_id,reservation_date,checkin_date,checkout_date,adults,children,special_request,status,total_amount) VALUES(?,?,CURDATE(),?,?,?,?,?,?,?)');$s->execute([$customer,$room,$in,$out,$adults,$children,$special,$status,$total]);flash('success','Reservation created.');redirect('reservations.php');}
}
page_start($id?'Edit Reservation':'New Reservation','reservations');
?>
<div class="page-head"><div><h1><?= $id?'Edit':'New' ?> Reservation</h1></div><a class="btn btn-light" href="reservations.php">Back</a></div>
<form method="post" class="card form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label class="full">Customer<select name="customer_id" required><option value="">Select customer</option><?php foreach($customers as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$r['customer_id']===(int)$c['id']?'selected':'' ?>><?= e($c['first_name'].' '.$c['last_name']) ?></option><?php endforeach; ?></select></label>
<label>Room<select name="room_id" required><option value="">Select room</option><?php foreach($rooms as $rm): ?><option value="<?= (int)$rm['id'] ?>" <?= (int)$r['room_id']===(int)$rm['id']?'selected':'' ?>><?= e($rm['room_number'].' - '.$rm['name'].' ('.money($rm['price']).')') ?></option><?php endforeach; ?></select></label>
<label>Status<select name="status"><?php foreach(['pending','confirmed','cancelled','completed'] as $s): ?><option value="<?= $s ?>" <?= $r['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></label>
<label>Check-in<input type="date" name="checkin_date" value="<?= e($r['checkin_date']) ?>" required></label>
<label>Check-out<input type="date" name="checkout_date" value="<?= e($r['checkout_date']) ?>" required></label>
<label>Adults<input type="number" min="1" name="adults" value="<?= (int)$r['adults'] ?>"></label>
<label>Children<input type="number" min="0" name="children" value="<?= (int)$r['children'] ?>"></label>
<label class="full">Special Request<textarea name="special_request"><?= e($r['special_request']) ?></textarea></label>
<div class="full"><button class="btn btn-primary">Save Reservation</button></div>
</form>
<?php page_end(); ?>
