<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $id=post_int('id');
    if (post_string('action')==='delete' && $id) {
        try {
            $stmt=$pdo->prepare('DELETE FROM room_types WHERE id=?');
            $stmt->execute([$id]);
            flash('success','Room type deleted.');
        } catch (PDOException $e) {
            flash('error','This room type is in use and cannot be deleted.');
        }
    }
    redirect('room_types.php');
}
$types=$pdo->query('SELECT * FROM room_types ORDER BY id DESC')->fetchAll();
page_start('Room Types','room_types');
?>
<div class="page-head"><div><h1>Room Types</h1><p class="muted">Define room categories and rates</p></div><a class="btn btn-primary" href="room_type_form.php">Add Room Type</a></div>
<div class="card"><div class="table-wrap"><table><thead><tr><th>Type</th><th>Price/Night</th><th>Adults</th><th>Children</th><th>Note</th><th>Actions</th></tr></thead><tbody>
<?php foreach($types as $t): ?><tr>
<td><?= e($t['name']) ?></td><td><?= money($t['price']) ?></td><td><?= (int)$t['adult_capacity'] ?></td><td><?= (int)$t['child_capacity'] ?></td><td><?= e($t['note']) ?></td>
<td class="actions"><a href="room_type_form.php?id=<?= (int)$t['id'] ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this room type?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$t['id'] ?>"><button class="link-danger">Delete</button></form></td>
</tr><?php endforeach; ?>
<?php if(!$types): ?><tr><td colspan="6" class="empty">No room types.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php page_end(); ?>
