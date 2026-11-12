<?php
class User
{
    public $id;
    public $username;
    public $password;
    public $role;

    public function __construct($id = null, $username = '', $password = '', $role = 'USER')
    {
        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->role = $role;
    }
}
