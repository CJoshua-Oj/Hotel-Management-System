<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();$id=filter_var($_GET['id']??$_POST['id']??0,FILTER_VALIDATE_INT)?:0;
$room=['room_number'=>'','room_type_id'=>'','floor'=>'','status'=>'available'];
if($id){$s=$pdo->prepare('SELECT * FROM rooms WHERE id=?');$s->execute([$id]);$room=$s->fetch()?:$room;}
$types=$pdo->query('SELECT * FROM room_types ORDER BY name')->fetchAll();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$number=post_string('room_number');$type=post_int('room_type_id');$floor=post_string('floor');$status=post_string('status','available');
 if($number===''||$type<=0){flash('error','Room number and room type are required.');}
 elseif($id){$s=$pdo->prepare('UPDATE rooms SET room_number=?,room_type_id=?,floor=?,status=? WHERE id=?');$s->execute([$number,$type,$floor,$status,$id]);flash('success','Room updated.');redirect('rooms.php');}
 else{$s=$pdo->prepare('INSERT INTO rooms(room_number,room_type_id,floor,status) VALUES(?,?,?,?)');$s->execute([$number,$type,$floor,$status]);flash('success','Room added.');redirect('rooms.php');}
}
page_start($id?'Edit Room':'Add Room','rooms');
?>
<div class="page-head"><div><h1><?= $id?'Edit':'Add' ?> Room</h1></div><a class="btn btn-light" href="rooms.php">Back</a></div>
<form method="post" class="card form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label>Room Number<input name="room_number" value="<?= e($room['room_number']) ?>" required></label>
<label>Room Type<select name="room_type_id" required><option value="">Select type</option><?php foreach($types as $t): ?><option value="<?= (int)$t['id'] ?>" <?= (int)$room['room_type_id']===(int)$t['id']?'selected':'' ?>><?= e($t['name']) ?> - <?= money($t['price']) ?></option><?php endforeach; ?></select></label>
<label>Floor<input name="floor" value="<?= e($room['floor']) ?>"></label>
<label>Status<select name="status"><?php foreach(['available','occupied','maintenance'] as $s): ?><option value="<?= $s ?>" <?= $room['status']===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?></select></label>
<div class="full"><button class="btn btn-primary">Save Room</button></div></form>
<?php page_end(); ?>
