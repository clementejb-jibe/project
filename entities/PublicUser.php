<?php
require_once __DIR__ . '/User.php';

class PublicUser extends User
{
    public $borrowedBooks = array();

    public function canBorrow()
    {
        return count($this->borrowedBooks) < 3;
    }
}
