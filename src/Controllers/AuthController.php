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
    
    
    //login user
    public function login(){
        try{
            $data = [
                'username' => $_POST['username'],
                'password' => $_POST['password']
            ];
            
            $user = $this->auth->login($data);
            $_SESSION['user']['id'] = $user['id'];
            $_SESSION['user']['username'] = $user['username'];
            $_SESSION['user']['email'] = $user['email'];
            
            header('Location: /chat');
            exit;
        }
        catch(\Spn\Exceptions\InvalException $e){
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => $e->getMessage()
            ];
            header('Location: /login');
            exit;
        }
        catch(\Spn\Exceptions\DatabaseException $e){
            error_log($e->getMessage());
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Noe gikk galt! Vennligst prøv igjen"
            ];
            header('Location: /login');
            exit;
        }
        catch(\Exception $e){
            error_log($e->getMessage());
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Ukjent feil!"
            ];
        }
    }
    
    
    //register user
    public function register(){
        try{
            $data = [
                'username' => $_POST['username'],
                'password' => $_POST['password'],
                'email' => $_POST['email']
            ];
            
            $this->auth->register($data);
            
            $_SESSION['flash'] = [
                "class" => "success",
                "message" => "Bruker Registrert!"
            ];
            header('Location: /login');
            exit;
        }
        catch(\Spn\Exceptions\InvalException $e){
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => $e->getMessage()
            ];
            header('Location: /register');
            exit;
        }
        catch(\Spn\Exceptions\DatabaseException $e){
            error_log($e->getMessage());
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Noe gikk galt! Vennligst prøv igjen"
            ];
            header('Location: /register');
            exit;
        }
        catch(\Exception $e){
            error_log($e->getMessage());
            $_SESSION['flash'] = [
                "class" => "error",
                "message" => "Ukjent feil!"
            ];
        }
    }
    
    
    //logout user
    public function logout(){
        session_destroy();
        header('Location: /login');
        exit;
    }
}