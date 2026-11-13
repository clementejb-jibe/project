<?php
$accountType = $accountType ?? 'USER';
$error = $error ?? '';
require __DIR__ . '/header.php';
?>
<h1><?= $accountType === 'ADMIN' ? 'Admin login' : 'User login' ?></h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<div class="switcher"><a href="index.php?page=user-login">User login</a><a href="index.php?page=user-register">User registration</a><a href="index.php?page=admin-login">Admin login</a><a href="index.php?page=admin-register">Admin registration</a></div>
<form class="card form narrow" method="post"><label>Username<input name="username" required autocomplete="username"></label><label>Password<input type="password" name="password" required autocomplete="current-password"></label><button class="button">Log in</button><p>Selected: <?= $accountType === 'ADMIN' ? 'administrator' : 'student/user' ?></p></form>
<?php require __DIR__ . '/footer.php'; ?>
