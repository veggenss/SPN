<?php

class AuthService{
    private mysqli $mysqli;

    public function __construct(mysqli $mysqli){
        $this->mysqli = $mysqli;
    }


}