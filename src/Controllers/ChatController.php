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
}