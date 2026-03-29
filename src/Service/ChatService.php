<?php
namespace Spn\Service;

use Spn\Repository\ChatRepository;

class ChatService{
    private ChatRepository $chatRepo;
    
    public function __construct(){
        $this->chatRepo = new ChatRepository;
    }
}