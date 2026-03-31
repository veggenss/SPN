<?php
namespace Spn\Controllers;

use Spn\Service\ChatService;

class ChatController{
    private ChatService $chat;
    
    public function __construct(){
        $this->chat = new ChatService;
    }

    public function showChat(){
        require __DIR__ . '/../../views/chat/main.php';
    }
    
    public function getChat(){  
        if(!$_SESSION['user']){
            header('Location: /login');
            exit;
        }
        $public = $this->chat->getChat(NULL);
        $private = $this->chat->getChat($_SESSION['user']['id']);
        $conversations = $this->chat->getConversations($_SESSION['user']['id']);
        
        header('Content-Type: application/json');
        if(!$public){
            http_response_code(500);
            echo json_encode(['error' => 'Failed to fetch chat']);
            exit;
        }
        
        echo json_encode([
            'public' => $public,
            'private' => $private,
            'conversations' => $conversations
        ]);
        exit;
    }
    
    public function sendMessage(){
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->chat->sendMessage($data);
        if(!$res){
            return "sendMessage Failed!";
            exit;
        }
        exit;
    }
}