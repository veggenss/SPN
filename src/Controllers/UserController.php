<?php
namespace Spn\Controllers;

use Spn\Service\UserService;

class UserController{
    private UserService $user;
    
    public function __construct(){
        $this->user = new UserService;
    }
    
    public function showProfile(){
        require __DIR__ . '/../../views/user/profile.php';
    }
}