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
