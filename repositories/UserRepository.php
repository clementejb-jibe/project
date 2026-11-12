<?php
require_once __DIR__ . '/../entities/User.php';
require_once __DIR__ . '/../entities/PublicUser.php';

class UserRepository
{
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function findByUsername($username)
    {
        $statement = $this->db->prepare('SELECT * FROM users WHERE username = ?');
        $statement->execute(array($username));
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        if ($row['role'] === 'ADMIN') return new User($row['id'], $row['username'], $row['password'], $row['role']);
        return new PublicUser($row['id'], $row['username'], $row['password'], $row['role']);
    }

    public function create($username, $password)
    {
        $statement = $this->db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        return $statement->execute(array($username, password_hash($password, PASSWORD_DEFAULT), 'USER'));
    }

    public function createAdmin($username, $password)
    {
        $statement = $this->db->prepare('INSERT INTO users (username, password, role) VALUES (?, ?, ?)');
        return $statement->execute(array($username, password_hash($password, PASSWORD_DEFAULT), 'ADMIN'));
    }

    public function getAccount($userId)
    {
        $statement = $this->db->prepare('SELECT id, username, role, created_at FROM users WHERE id=?');
        $statement->execute(array($userId));
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function usernameExists($username)
    {
        $statement = $this->db->prepare('SELECT id FROM users WHERE username = ?');
        $statement->execute(array($username));
        return (bool) $statement->fetchColumn();
    }
}
