<?php
require __DIR__ . '/../bootstrap.php';

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;

use Spn\Controllers\AuthController;
use Spn\Controllers\ChatController;
use Spn\Controllers\UserController;

$dispatcher = \FastRoute\simpleDispatcher(function(RouteCollector $r){
    //Root
    $r->addRoute('GET', '/', [AuthController::class, 'showLogin']);
    
    //Auth
    $r->addRoute('GET', '/login', [AuthController::class, 'showLogin']);
    $r->addRoute('POST', '/login', [AuthController::class, 'login']);
    $r->addRoute('GET', '/register', [AuthController::class, 'showRegister']);
    $r->addRoute('POST', '/register', [AuthController::class, 'register']);
    $r->addRoute('GET', '/password_reset', [AuthController::class, 'showPasswordReset']);
    $r->addRoute('GET', '/logout', [AuthController::class, 'logout']);
    
    //Chat
    $r->addRoute('GET', '/chat', [ChatController::class, 'showChat']);
    
    //API
    $r->addRoute('POST', '/api/get-user-logs', [ChatController::class, 'getUserLogs']);
    $r->addRoute('POST', '/api/make-conv', [ChatController::class, 'makeConversation']);
    $r->addRoute('POST', '/api/send-message', [ChatController::class, 'sendMessage']);
    
});

$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$publicRoutes = [
    '/login',
    '/register',
    '/password_reset'
];

if(!in_array($uri, $publicRoutes)){
    if(!$_SESSION['user']['id']){
        header('Location: /login');
        exit;
    }
}

$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch($routeInfo[0]){
    case Dispatcher::NOT_FOUND:
        http_response_code(404);
        echo "404 Not Found";
        break;
    
    case Dispatcher::METHOD_NOT_ALLOWED:
        http_response_code(405);
        $allowedMethods = $routeInfo[1];
        echo 405 . " Method Not Allowed: " . $allowedMethods;
        break;
        
    case Dispatcher::FOUND:
        [$class, $method] = $routeInfo[1];
        $vars = $routeInfo[2];
        call_user_func_array([new $class, $method], $vars);
        break;
}
