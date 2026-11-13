<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/repositories/UserRepository.php';
require_once __DIR__ . '/repositories/BookRepository.php';
require_once __DIR__ . '/services/AuthService.php';
require_once __DIR__ . '/services/BookService.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/BookController.php';
require_once __DIR__ . '/controllers/AccountController.php';

function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function readableDate($value) { $date = strtotime($value); return $date ? date('M j, Y \\a\\t g:i A', $date) : ''; }
function requireLogin() { if (!isset($_SESSION['user'])) { header('Location: index.php?page=login'); exit; } }
function requireAdmin() { requireLogin(); if ($_SESSION['user']['role'] !== 'ADMIN') { http_response_code(403); exit('You do not have permission to view this page.'); } }
function requireUser() { requireLogin(); if ($_SESSION['user']['role'] !== 'USER') { http_response_code(403); exit('You do not have permission to do that.'); } }

$db = (new Database())->connect();
$auth = new AuthController(new AuthService(new UserRepository($db)));
$bookController = new BookController(new BookRepository($db), new BookService(new BookRepository($db)));
$bookRepository = new BookRepository($db);
$accountController = new AccountController(new UserRepository($db), $bookRepository);
$page = $_GET['page'] ?? 'books';
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);

if ($page === 'login' || $page === 'user-login') $auth->login('USER');
elseif ($page === 'admin-login') $auth->login('ADMIN');
elseif ($page === 'register' || $page === 'user-register') $auth->register('USER');
elseif ($page === 'admin-register') $auth->register('ADMIN');
elseif ($page === 'logout') $auth->logout();
elseif ($page === 'account') { requireUser(); $accountController->show(); }
elseif ($page === 'borrowers') { requireAdmin(); $bookController->borrowers(); }
elseif ($page === 'pay-fine' && $_SERVER['REQUEST_METHOD'] === 'POST') { requireUser(); if (!$id) { header('Location: index.php?page=account'); exit; } $accountController->pay($id); }
elseif ($page === 'receipt') { requireUser(); if (!$id) { header('Location: index.php?page=account'); exit; } $accountController->receipt($id); }
elseif ($page === 'books') { $bookController->list(); }
elseif ($page === 'add') { requireAdmin(); $bookController->form(); }
elseif ($page === 'edit') { requireAdmin(); if (!$id) { header('Location: index.php'); exit; } $bookController->form($id); }
elseif ($page === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') { requireAdmin(); if (!$id) { header('Location: index.php'); exit; } $bookController->delete($id); }
elseif ($page === 'borrow' && $_SERVER['REQUEST_METHOD'] === 'POST') { requireUser(); if (!$id) { header('Location: index.php'); exit; } $bookController->borrow($id); }
elseif ($page === 'return' && $_SERVER['REQUEST_METHOD'] === 'POST') { requireUser(); if (!$id) { header('Location: index.php'); exit; } $bookController->returnBook($id); }
else { http_response_code(404); echo 'Page not found.'; }

if (empty($GLOBALS['pageView'])) exit;
$view = $GLOBALS['pageView'];
extract($GLOBALS['pageData'] ?? array(), EXTR_SKIP);
?><!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Book Catalog</title><link rel="stylesheet" href="style.css"></head>
<body>
<header class="topbar"><a class="brand" href="index.php">Book Catalog</a><nav>
<?php if (isset($_SESSION['user'])): ?><span>Hello, <?= e($_SESSION['user']['username']) ?></span><?php if ($_SESSION['user']['role'] === 'USER'): ?><a href="index.php?page=account">My account</a><?php endif; ?><?php if ($_SESSION['user']['role'] === 'ADMIN'): ?><a href="index.php?page=borrowers">Borrowers</a><?php endif; ?><a href="index.php?page=logout">Log out</a><?php else: ?><a href="index.php?page=user-login">User login</a><a href="index.php?page=admin-login">Admin login</a><?php endif; ?>
</nav></header><main class="wrap">
<?php if (!empty($_SESSION['flash'])): ?><div class="notice"><?= e($_SESSION['flash']) ?></div><?php unset($_SESSION['flash']); endif; ?>
<?php if ($view === 'books'): ?>
<?php if (!empty($borrowNotice)): ?><div class="modal-backdrop"><section class="modal card"><h2>Borrowing confirmed</h2><p><strong><?= e($borrowNotice['title']) ?></strong> by <?= e($borrowNotice['author']) ?></p><p>Borrowed: <?= e(readableDate($borrowNotice['borrowed_at'])) ?></p><p>Return by: <?= e(readableDate($borrowNotice['due_at'])) ?></p><a class="button" href="index.php?page=account">View my account</a><a class="close-link" href="index.php">Close</a></section></div><?php endif; ?>
<div class="heading"><div><h1>Books</h1><p>Browse the catalog and see what is available.</p></div><?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'ADMIN'): ?><div><a class="button" href="index.php?page=borrowers">View borrowers</a> <a class="button" href="index.php?page=add">Add a book</a></div><?php endif; ?></div>
<?php if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'USER'): ?><section class="card"><h2>My borrowed books (<?= count($myBooks) ?>/3)</h2><?php if (!$myBooks): ?><p>You have not borrowed any books yet.</p><?php else: ?><ul class="borrowed-list"><?php foreach ($myBooks as $book): ?><li><?= e($book['title']) ?> <form method="post" action="index.php?page=return&id=<?= (int) $book['id'] ?>"><button class="link-button">Return</button></form></li><?php endforeach; ?></ul><?php endif; ?></section><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Author</th><th>Genre</th><th>Published</th><th>Stock</th><th>Status</th><?php if (isset($_SESSION['user'])): ?><th>Action</th><?php endif; ?></tr></thead><tbody><?php if (!$allBooks): ?><tr><td colspan="<?= isset($_SESSION['user']) ? 7 : 6 ?>">No books have been added yet.</td></tr><?php endif; ?><?php foreach ($allBooks as $book): ?><tr><td><?= e($book['title']) ?></td><td><?= e($book['author']) ?></td><td><?= e($book['genre']) ?></td><td><?= e($book['year_published']) ?></td><td><?= (int) $book['available_copies'] ?> available / <?= (int) $book['total_copies'] ?> total</td><td><span class="status <?= strtolower(e($book['status'])) ?>"><?= e($book['status']) ?></span></td><?php if (isset($_SESSION['user'])): ?><td class="actions"><?php if ($_SESSION['user']['role'] === 'ADMIN'): ?><a href="index.php?page=edit&id=<?= (int) $book['id'] ?>">Edit</a><form method="post" action="index.php?page=delete&id=<?= (int) $book['id'] ?>" onsubmit="return confirm('Delete this book?')"><button class="link-button danger">Delete</button></form><?php elseif ((int) $book['available_copies'] > 0 && count($myBooks) < 3): ?><form method="post" action="index.php?page=borrow&id=<?= (int) $book['id'] ?>"><button class="link-button">Borrow</button></form><?php else: ?>—<?php endif; ?></td><?php endif; ?></tr><?php endforeach; ?></tbody></table></div>
<?php elseif ($view === 'login' || $view === 'register'): ?>
<?php $accountType = $accountType ?? 'USER'; $error = $error ?? ''; $registering = $view === 'register'; ?><h1><?= $accountType === 'ADMIN' ? 'Admin ' : 'User ' ?><?= $registering ? 'registration' : 'login' ?></h1><?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?><div class="switcher"><a href="index.php?page=user-login">User login</a><a href="index.php?page=user-register">User registration</a><a href="index.php?page=admin-login">Admin login</a><a href="index.php?page=admin-register">Admin registration</a></div><form class="card form narrow" method="post"><label>Username<input name="username" required <?= $registering ? 'minlength="3" maxlength="30" autocomplete="username"' : 'autocomplete="username"' ?>></label><label>Password<input type="password" name="password" required <?= $registering ? 'minlength="6" autocomplete="new-password"' : 'autocomplete="current-password"' ?>></label><?php if ($registering && $accountType === 'ADMIN'): ?><label>Admin registration code<input type="password" name="admin_code" required></label><?php endif; ?><button class="button"><?= $registering ? 'Register' : 'Log in' ?></button><?php if (!$registering): ?><p>Selected: <?= $accountType === 'ADMIN' ? 'administrator' : 'student/user' ?></p><?php endif; ?></form>
<?php elseif ($view === 'book_form'): ?>
<?php $book = $book ?? array(); $error = $error ?? ''; $editing = !empty($book); ?><h1><?= $editing ? 'Edit book' : 'Add a book' ?></h1><?php if ($error): ?><div class="error"><?= e($error) ?></div><?php endif; ?><form class="card form" method="post"><label>Title<input name="title" required maxlength="200" value="<?= e($book['title'] ?? '') ?>"></label><label>Author<input name="author" required maxlength="150" value="<?= e($book['author'] ?? '') ?>"></label><label>Genre<input name="genre" required maxlength="100" value="<?= e($book['genre'] ?? '') ?>"></label><label>Year published<input type="number" name="year_published" required min="1" max="<?= (int) date('Y') ?>" value="<?= e($book['year_published'] ?? '') ?>"></label><label>Total copies in stock<input type="number" name="total_copies" required min="1" value="<?= e($book['total_copies'] ?? 1) ?>"></label><div class="form-actions"><button class="button" type="submit"><?= $editing ? 'Save changes' : 'Add book' ?></button><a href="index.php">Cancel</a></div></form>
<?php elseif ($view === 'borrowers'): ?>
<h1>Current borrowers</h1><p>These are the books that have not been returned yet.</p><div class="table-wrap"><table><thead><tr><th>Student username</th><th>Book</th><th>Author</th><th>Borrowed</th><th>Return by</th><th>Loan status</th></tr></thead><tbody><?php if (!$borrowers): ?><tr><td colspan="6">No books are currently borrowed.</td></tr><?php endif; ?><?php foreach ($borrowers as $borrower): $late = new DateTime($borrower['due_at']) < new DateTime(); ?><tr><td><?= e($borrower['username']) ?></td><td><?= e($borrower['title']) ?></td><td><?= e($borrower['author']) ?></td><td><?= e(readableDate($borrower['borrowed_at'])) ?></td><td><?= e(readableDate($borrower['due_at'])) ?></td><td><?= $late ? 'Overdue' : 'On loan' ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php elseif ($view === 'account'): ?>
<?php $today = $today ?? new DateTime('today'); ?><h1>My account</h1><section class="card"><h2>Account information</h2><p><strong>Username:</strong> <?= e($account['username']) ?></p><p><strong>Account type:</strong> Student / user</p><p><strong>Member since:</strong> <?= e(readableDate($account['created_at'])) ?></p></section><h2>Borrowing history and fines</h2><?php if (!$loans): ?><div class="card">You have not borrowed any books yet.</div><?php endif; ?><?php foreach ($loans as $loan): $due = new DateTime($loan['due_at']); $daysLate = max(0, (int) $due->diff($today)->format('%r%a')); $fine = max(0, $daysLate * 10 - (float) $loan['paid']); ?><section class="card loan"><h3><?= e($loan['title']) ?> <small>by <?= e($loan['author']) ?></small></h3><p>Borrowed: <?= e(readableDate($loan['borrowed_at'])) ?></p><p>Return by: <?= e(readableDate($loan['due_at'])) ?></p><p>Status: <?= $loan['returned_at'] ? 'Returned on ' . e(readableDate($loan['returned_at'])) : ($daysLate ? 'Overdue by ' . $daysLate . ' day(s)' : 'On loan') ?></p><p>Overdue fine: <strong>₱<?= number_format($fine, 2) ?></strong> <small>(₱10 per day)</small></p><?php if ($fine > 0): ?><form method="post" action="index.php?page=pay-fine&id=<?= (int) $loan['id'] ?>" onsubmit="return confirm('Pay ₱<?= number_format($fine, 2) ?> using the demo payment flow?')"><button class="button">Pay fine and get receipt</button></form><?php endif; ?></section><?php endforeach; ?>
<?php elseif ($view === 'receipt'): ?>
<section class="card receipt"><h1>Payment receipt</h1><p><strong>Receipt number:</strong> <?= e($receipt['receipt_number']) ?></p><p><strong>Paid on:</strong> <?= e(readableDate($receipt['paid_at'])) ?></p><p><strong>Account:</strong> <?= e($_SESSION['user']['username']) ?></p><p><strong>Book:</strong> <?= e($receipt['title']) ?></p><p><strong>Amount paid:</strong> ₱<?= number_format((float) $receipt['amount'], 2) ?></p><p><strong>Payment method:</strong> Demo payment (no real charge)</p><button onclick="window.print()">Print receipt</button> <a href="index.php?page=account">Back to account</a></section>
<?php endif; ?>
</main></body></html>
