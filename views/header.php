<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book Catalog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="index.php">Book Catalog</a>
    <nav>
        <?php if (isset($_SESSION['user'])): ?>
            <span>Hello, <?= e($_SESSION['user']['username']) ?></span>
            <?php if ($_SESSION['user']['role'] === 'USER'): ?><a href="index.php?page=account">My account</a><?php endif; ?>
            <?php if ($_SESSION['user']['role'] === 'ADMIN'): ?><a href="index.php?page=borrowers">Borrowers</a><?php endif; ?>
            <a href="index.php?page=logout">Log out</a>
        <?php else: ?>
            <a href="index.php?page=user-login">User login</a>
            <a href="index.php?page=admin-login">Admin login</a>
        <?php endif; ?>
    </nav>
</header>
<main class="wrap">
<?php if (!empty($_SESSION['flash'])): ?><div class="notice"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
