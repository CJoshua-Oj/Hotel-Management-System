<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();

$pdo = db();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = post_string('action');
    $id = post_int('id');
    if ($action === 'delete' && $id > 0) {
        $stmt = $pdo->prepare('DELETE FROM customers WHERE id=?');
        $stmt->execute([$id]);
        flash('success', 'Customer deleted.');
    }
    redirect('customers.php');
}

$q = post_string('q', (string)($_GET['q'] ?? ''));
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR phone LIKE ? ORDER BY id DESC");
    $like = '%' . $q . '%';
    $stmt->execute([$like,$like,$like,$like]);
    $customers = $stmt->fetchAll();
} else {
    $customers = $pdo->query('SELECT * FROM customers ORDER BY id DESC')->fetchAll();
}

page_start('Customers', 'customers');
?>
<div class="page-head">
    <div><h1>Customers</h1><p class="muted">Guest records and contact details</p></div>
    <a class="btn btn-primary" href="customer_form.php">Add Customer</a>
</div>
<form class="toolbar" method="get">
    <input name="q" value="<?= e($q) ?>" placeholder="Search name, email or phone">
    <button class="btn" type="submit">Search</button>
    <a class="btn btn-light" href="customers.php">Clear</a>
</form>
<div class="card">
<div class="table-wrap"><table>
<thead><tr><th>Name</th><th>Gender</th><th>Phone</th><th>Email</th><th>Nationality</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($customers as $c): ?>
<tr>
<td><?= e($c['first_name'].' '.$c['last_name']) ?></td>
<td><?= e($c['gender']) ?></td>
<td><?= e($c['phone']) ?></td>
<td><?= e($c['email']) ?></td>
<td><?= e($c['nationality']) ?></td>
<td class="actions"><a href="customer_form.php?id=<?= (int)$c['id'] ?>">Edit</a>
<form method="post" onsubmit="return confirm('Delete this customer?');"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="link-danger" type="submit">Delete</button></form></td>
</tr>
<?php endforeach; ?>
<?php if (!$customers): ?><tr><td colspan="6" class="empty">No customers found.</td></tr><?php endif; ?>
</tbody></table></div></div>
<?php page_end(); ?>
