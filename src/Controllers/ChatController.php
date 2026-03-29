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
        $public = $this->chat->getChat(NULL);
        $private = $this->chat->getChat($_SESSION['id']);
        $conversations = $this->chat->getConversations($_SESSION['id']);
    }
    
    public function sendMessage(array $data){
        $res = $this->chat->sendMessage($data);
        if(!$res){
            return "sendMessage Failed!";
            exit;
        }
        exit;
    }
}