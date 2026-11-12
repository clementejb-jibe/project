<?php
define('ADMIN_REGISTRATION_CODE', 'BOOK-ADMIN-2026');

// Change these values if your local MySQL setup uses a different account.
class Database
{
    private $host = 'localhost';
    private $database = 'book_catalog';
    private $username = 'root';
    private $password = '';

    public function connect()
    {
        try {
            $connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password
            );
            $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $connection;
        } catch (PDOException $error) {
            die('Could not connect to the database. Check config/database.php and import database.sql.');
        }
    }
}
