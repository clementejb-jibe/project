<?php
require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    private $service;
    public function __construct($service) { $this->service = $service; }

    public function login($role = 'USER')
    {
        $accountType = $role;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->service->login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
            if ($user && $user->role === $role) { session_regenerate_id(true); $_SESSION['user'] = array('id' => $user->id, 'username' => $user->username, 'role' => $user->role); header('Location: index.php'); exit; }
            $error = 'Username or password is incorrect for this account type.';
        }
        require __DIR__ . '/../views/login.php';
    }

    public function register($role = 'USER')
    {
        $accountType = $role;
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $result = $role === 'ADMIN' ? $this->service->registerAdmin(trim($_POST['username'] ?? ''), $_POST['password'] ?? '', $_POST['admin_code'] ?? '') : $this->service->register(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
            if ($result === true) { $_SESSION['flash'] = 'Account created. You can now sign in.'; header('Location: index.php?page=' . ($role === 'ADMIN' ? 'admin-login' : 'user-login')); exit; }
            $error = $result;
        }
        require __DIR__ . '/../views/register.php';
    }

    public function logout()
    {
        $_SESSION = array();
        session_destroy();
        header('Location: index.php?page=login');
        exit;
    }
}
