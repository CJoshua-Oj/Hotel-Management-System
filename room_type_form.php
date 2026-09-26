<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
$id=filter_var($_GET['id'] ?? $_POST['id'] ?? 0,FILTER_VALIDATE_INT)?:0;
$type=['name'=>'','price'=>'','adult_capacity'=>1,'child_capacity'=>0,'note'=>''];
if($id){$s=$pdo->prepare('SELECT * FROM room_types WHERE id=?');$s->execute([$id]);$type=$s->fetch()?:$type;}
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $name=post_string('name');$price=(float)($_POST['price']??0);$adult=post_int('adult_capacity',1);$child=post_int('child_capacity',0);$note=post_string('note');
 if($name===''||$price<0){flash('error','Room type and a valid price are required.');}
 elseif($id){$s=$pdo->prepare('UPDATE room_types SET name=?,price=?,adult_capacity=?,child_capacity=?,note=? WHERE id=?');$s->execute([$name,$price,$adult,$child,$note,$id]);flash('success','Room type updated.');redirect('room_types.php');}
 else{$s=$pdo->prepare('INSERT INTO room_types(name,price,adult_capacity,child_capacity,note) VALUES(?,?,?,?,?)');$s->execute([$name,$price,$adult,$child,$note]);flash('success','Room type added.');redirect('room_types.php');}
}
page_start($id?'Edit Room Type':'Add Room Type','room_types');
?>
<div class="page-head"><div><h1><?= $id?'Edit':'Add' ?> Room Type</h1></div><a class="btn btn-light" href="room_types.php">Back</a></div>
<form method="post" class="card form-grid"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label>Type Name<input name="name" value="<?= e($type['name']) ?>" required></label>
<label>Price per Night<input type="number" step="0.01" min="0" name="price" value="<?= e($type['price']) ?>" required></label>
<label>Adult Capacity<input type="number" min="1" name="adult_capacity" value="<?= (int)$type['adult_capacity'] ?>"></label>
<label>Child Capacity<input type="number" min="0" name="child_capacity" value="<?= (int)$type['child_capacity'] ?>"></label>
<label class="full">Note<textarea name="note"><?= e($type['note']) ?></textarea></label>
<div class="full"><button class="btn btn-primary">Save Room Type</button></div></form>
<?php page_end(); ?>
