<?php

class User {
    public $id;
    public $userName;
    public $password;
    public $email;

    function __construct() {
        $this->id = -1;
    }

    static function createFromDb($dbLine) {
        $user = new User();
        $user->id = $dbLine['id'];
        $user->userName = $dbLine['userName'];
        $user->email = $dbLine['email'];
        $user->password = $dbLine['password'];
        return $user;
    }
}
