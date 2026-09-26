<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();

$pdo = db();
$id = filter_var($_GET['id'] ?? $_POST['id'] ?? 0, FILTER_VALIDATE_INT) ?: 0;
$customer = [
    'first_name'=>'','last_name'=>'','gender'=>'','address'=>'','country'=>'',
    'nationality'=>'','city'=>'','age'=>'','passport'=>'','phone'=>'',
    'company'=>'','email'=>'','note'=>''
];

if ($id) {
    $stmt=$pdo->prepare('SELECT * FROM customers WHERE id=?');
    $stmt->execute([$id]);
    $customer=$stmt->fetch() ?: $customer;
}

if ($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf();
    $data=[
        post_string('first_name'), post_string('last_name'), post_string('gender'),
        post_string('address'), post_string('country'), post_string('nationality'),
        post_string('city'), post_string('age'), post_string('passport'),
        post_string('phone'), post_string('company'), post_string('email'),
        post_string('note')
    ];
    if ($data[0]==='' || $data[1]==='') {
        flash('error','First name and last name are required.');
    } elseif ($id) {
        $stmt=$pdo->prepare('UPDATE customers SET first_name=?,last_name=?,gender=?,address=?,country=?,nationality=?,city=?,age=?,passport=?,phone=?,company=?,email=?,note=? WHERE id=?');
        $stmt->execute([...$data,$id]);
        flash('success','Customer updated.');
        redirect('customers.php');
    } else {
        $stmt=$pdo->prepare('INSERT INTO customers (first_name,last_name,gender,address,country,nationality,city,age,passport,phone,company,email,note) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)');
        $stmt->execute($data);
        flash('success','Customer added.');
        redirect('customers.php');
    }
}
page_start($id ? 'Edit Customer':'Add Customer','customers');
?>
<div class="page-head"><div><h1><?= $id ? 'Edit Customer':'Add Customer' ?></h1></div><a class="btn btn-light" href="customers.php">Back</a></div>
<?php foreach (consume_flash() as $f): ?><div class="alert <?= e($f['type']) ?>"><?= e($f['message']) ?></div><?php endforeach; ?>
<form method="post" class="card form-grid">
<?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>">
<label>First Name<input name="first_name" value="<?= e($customer['first_name']) ?>" required></label>
<label>Last Name<input name="last_name" value="<?= e($customer['last_name']) ?>" required></label>
<label>Gender<select name="gender"><option value="">Select</option><?php foreach(['Male','Female','Other'] as $v): ?><option <?= $customer['gender']===$v?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select></label>
<label>Age<input name="age" value="<?= e($customer['age']) ?>"></label>
<label>Phone<input name="phone" value="<?= e($customer['phone']) ?>"></label>
<label>Email<input type="email" name="email" value="<?= e($customer['email']) ?>"></label>
<label>Passport / ID<input name="passport" value="<?= e($customer['passport']) ?>"></label>
<label>Country<input name="country" value="<?= e($customer['country']) ?>"></label>
<label>Nationality<input name="nationality" value="<?= e($customer['nationality']) ?>"></label>
<label>City<input name="city" value="<?= e($customer['city']) ?>"></label>
<label>Company<input name="company" value="<?= e($customer['company']) ?>"></label>
<label class="full">Address<textarea name="address"><?= e($customer['address']) ?></textarea></label>
<label class="full">Notes<textarea name="note"><?= e($customer['note']) ?></textarea></label>
<div class="full"><button class="btn btn-primary" type="submit">Save Customer</button></div>
</form>
<?php page_end(); ?>
