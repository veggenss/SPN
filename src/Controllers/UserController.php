<?php
namespace Spn\Controllers;

use Spn\Service\UserService;

class UserController{
    private UserService $user;
    
    public function __construct()
    {
        $this->user = new UserService;
    }
    
    public function showProfile(): void
    {
        require __DIR__ . '/../../views/user/profile.php';
    }
    
    //logout user
    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
        exit;
    }
}