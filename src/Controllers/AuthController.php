<?php
namespace Spn\Controllers;

use Spn\Service\AuthService;

class AuthController{
    private AuthService $auth;
    
    public function __construct(){
        $this->auth = new AuthService;
    }
    
    public function showRegister(){
        require __DIR__ . '/../../views/auth/register.php';
    }
    
    public function showLogin(){
        require __DIR__ . '/../../views/auth/login.php';
    }
    
    public function showPasswordReset(){
        require __DIR__ . '/../../views/auth/password_reset.php';
    }
    
    public function login(){
        $data = [
            'username' => $_POST['username'],
            'password' => $_POST['password']
        ];
        
        $user = $this->auth->login($data);
        if(!$user){
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Brukernavn eller Passord er feil"
            ];
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        $_SESSION['user']['id'] = $user['id'];
        $_SESSION['user']['username'] = $user['username'];
        $_SESSION['user']['email'] = $user['email'];
        
        header('Location: ' . BASE_URL . '/chat');
        exit;
    }
    
    public function register(){
        $data = [
            'username' => $_POST['username'],
            'password' => $_POST['password'],
            'email' => $_POST['email']
        ];
        
        $user = $this->auth->register($data);
        if(!$user){
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Noe Gikk Galt!"
            ];
            header('Location: ' . BASE_URL . '/regiser');
            exit;
        }

        $_SESSION['flash'] = [
            "class" => "success",
            "message" => "Bruker Registrert!"
        ];
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    public function logout(){
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}