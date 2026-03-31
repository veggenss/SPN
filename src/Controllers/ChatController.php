<?php
namespace Spn\Controllers;

use Spn\Service\ChatService;

class ChatController{
    private ChatService $chat;
    
    public function __construct(){
        $this->chat = new ChatService;
    }
    
    private function authUser(){
        if(!$_SESSION['user']){
            header('Location: /login');
            exit;
        }
    }
    
    public function showChat(){
        $this->authUser();
        require __DIR__ . '/../../views/chat/main.php';
    }
    
    public function getChat(){  
        $this->authUser();
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
    
    public function makeConversation(){
        $data = json_decode(file_get_contents('php://input'), true);
        $res = $this->chat->makeConversation($_SESSION['user']['id'], $data);
       
        header('Content-Type: application/json');
        if(!$res){
            echo json_encode([
                'message' => 'Could Not Create Conversation'
            ]);
            exit;
        }
        
        echo json_encode([
            'conversation' => $res
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