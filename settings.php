<?php
declare(strict_types=1);
require_once __DIR__ . '/functions.php';
require_login();
$pdo=db();

if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $values=[
  'hotel_name'=>post_string('hotel_name'),
  'hotel_phone'=>post_string('hotel_phone'),
  'hotel_email'=>post_string('hotel_email'),
  'hotel_address'=>post_string('hotel_address')
 ];
 $s=$pdo->prepare('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');
 foreach($values as $k=>$v)$s->execute([$k,$v]);
 flash('success','Hotel settings saved.');
 redirect('settings.php');
}
page_start('Hotel Settings','settings');
?>
<div class="page-head"><div><h1>Hotel Settings</h1><p class="muted">Basic hotel profile information</p></div></div>
<form method="post" class="card form-grid"><?= csrf_field() ?>
<label>Hotel Name<input name="hotel_name" value="<?= e(setting('hotel_name','My Hotel')) ?>" required></label>
<label>Phone<input name="hotel_phone" value="<?= e(setting('hotel_phone')) ?>"></label>
<label>Email<input type="email" name="hotel_email" value="<?= e(setting('hotel_email')) ?>"></label>
<label class="full">Address<textarea name="hotel_address"><?= e(setting('hotel_address')) ?></textarea></label>
<div class="full"><button class="btn btn-primary">Save Settings</button></div>
</form>
<div class="card">
<h2>Administrator Account</h2>
<p>Use the account created during installation to sign in. Passwords are stored using PHP's secure password hashing.</p>
</div>
<?php page_end(); ?>
