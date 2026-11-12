<?php
class AuthService
{
    private $users;
    public function __construct($users) { $this->users = $users; }

    public function login($username, $password)
    {
        $user = $this->users->findByUsername($username);
        if ($user && password_verify($password, $user->password)) return $user;
        return null;
    }

    public function register($username, $password)
    {
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) || strlen($password) < 6) return 'Use a username of 3-30 letters, numbers, or underscores and a password of at least 6 characters.';
        if ($this->users->usernameExists($username)) return 'That username is already taken.';
        $this->users->create($username, $password);
        return true;
    }

    public function registerAdmin($username, $password, $code)
    {
        if ($code !== ADMIN_REGISTRATION_CODE) return 'The admin registration code is incorrect.';
        if (!preg_match('/^[a-zA-Z0-9_]{3,30}$/', $username) || strlen($password) < 6) return 'Use a username of 3-30 letters, numbers, or underscores and a password of at least 6 characters.';
        if ($this->users->usernameExists($username)) return 'That username is already taken.';
        $this->users->createAdmin($username, $password);
        return true;
    }
}
