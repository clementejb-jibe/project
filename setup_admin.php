<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/UserRepository.php';
$db = (new Database())->connect();
$users = new UserRepository($db);
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) || strlen($password) < 6) $message = 'Use a username of 3-30 letters, numbers, or underscores and a password of at least 6 characters.';
    elseif ($users->usernameExists($username)) $message = 'That username already exists. Log in with it, or choose another.';
    else {
        $statement = $db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        $statement->execute(array($username, password_hash($password, PASSWORD_DEFAULT), 'ADMIN'));
        $message = 'Admin account created. Remove setup_admin.php from this folder now.';
    }
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Create admin account</title><link rel="stylesheet" href="style.css"></head><body><main class="wrap"><h1>Create the first admin account</h1><?php if ($message): ?><div class="notice"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?><form class="card form narrow" method="post"><label>Username<input name="username" required minlength="3" maxlength="30"></label><label>Password<input type="password" name="password" required minlength="6"></label><button class="button">Create admin</button></form><p><a href="index.php">Go to catalog</a></p></main></body></html>
