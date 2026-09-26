<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=post_int('id');
 if(post_string('action')==='delete'&&$id){try{$s=$pdo->prepare('DELETE FROM rooms WHERE id=?');$s->execute([$id]);flash('success','Room deleted.');}catch(PDOException $e){flash('error','This room cannot be deleted because it has related records.');}}
 redirect('rooms.php');
}
$q=trim((string)($_GET['q']??''));
$sql="SELECT r.*,t.name type_name,t.price FROM rooms r JOIN room_types t ON t.id=r.room_type_id";
$params=[];
if($q!==''){$sql.=" WHERE r.room_number LIKE ? OR r.floor LIKE ? OR t.name LIKE ?";$like="%$q%";$params=[$like,$like,$like];}
$sql.=" ORDER BY r.room_number";
$s=$pdo->prepare($sql);$s->execute($params);$rooms=$s->fetchAll();
page_start('Rooms','rooms');
?>
<div class="page-head"><div><h1>Rooms</h1><p class="muted">Room inventory and availability</p></div><a class="btn btn-primary" href="room_form.php">Add Room</a></div>
<form class="toolbar" method="get"><input name="q" value="<?= e($q) ?>" placeholder="Search room, floor or type"><button class="btn">Search</button><a class="btn btn-light" href="rooms.php">Clear</a></form>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Room</th><th>Type</th><th>Floor</th><th>Rate</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rooms as $r): ?><tr><td><?= e($r['room_number']) ?></td><td><?= e($r['type_name']) ?></td><td><?= e($r['floor']) ?></td><td><?= money($r['price']) ?></td><td><span class="badge <?= e($r['status']) ?>"><?= e(ucfirst($r['status'])) ?></span></td><td class="actions"><a href="room_form.php?id=<?= (int)$r['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this room?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$r['id'] ?>"><button class="link-danger">Delete</button></form></td></tr><?php endforeach; ?>
<?php if(!$rooms): ?><tr><td colspan="6" class="empty">No rooms found.</td></tr><?php endif; ?></tbody></table></div></div>
<?php page_end(); ?>
