<?php require __DIR__ . '/header.php'; ?>
<h1><?= $accountType === 'ADMIN' ? 'Admin registration' : 'User registration' ?></h1>
<?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?>
<div class="switcher"><a href="index.php?page=user-login">User login</a><a href="index.php?page=user-register">User registration</a><a href="index.php?page=admin-login">Admin login</a><a href="index.php?page=admin-register">Admin registration</a></div>
<form class="card form narrow" method="post"><label>Username<input name="username" required minlength="3" maxlength="30" autocomplete="username"></label><label>Password<input type="password" name="password" required minlength="6" autocomplete="new-password"></label><?php if ($accountType === 'ADMIN'): ?><label>Admin registration code<input type="password" name="admin_code" required></label><?php endif; ?><button class="button">Register</button></form>
<?php require __DIR__ . '/footer.php'; ?>
