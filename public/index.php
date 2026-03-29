<?php
require __DIR__ . '/../bootstrap.php';

use Spn\Controllers\AuthController;
use Spn\Controllers\ChatController;

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace(BASE_URL, '', $uri);

switch($uri){
    case '/':
    case '/login':
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
           (new AuthController())->login(); 
        }
        else{
            (new AuthController())->showLogin();
        }
        break;
    
    case '/register':
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            (new AuthController())->register();
        }
        else{
            (new AuthController())->showRegister();
        }
        break;
    
    case '/password_reset':
        (new AuthController())->showPasswordReset();
        break;
        
    case '/chat':
        (new ChatController())->showChat();
        break;
    
    default:
        http_response_code(404);
        echo "404 Not Found";
}
