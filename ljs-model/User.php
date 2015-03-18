<?php

class User {
    public $id;
    public $userName;
    public $password;
    public $email;

    function __construct() {
        $this->id = -1;
    }
}
