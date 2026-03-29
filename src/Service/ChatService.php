<?php
namespace Spn\Service;

use Spn\Repository\ChatRepository;

class ChatService{
    private ChatRepository $chatRepo;
    
    public function __construct(){
        $this->chatRepo = new ChatRepository;
    }
    
    public function getChat(?int $id):{
        if(!$id){
            return $this->chatRepo->getPublicMessages();
        }
        return $this->chatRepo->getPrivateMessages($id);
    }
    
    public function getConversations(int $id){
        return $this->chatRepo->getConversations($id);
    }

    public function sendMessage(array $data):bool{
        switch($data['type']){
            case 'public'||'global':
                return $this->chatRepo->savePublicMessage($data);
                break;
            
            case 'private':
                return $this->chatRepo->savePrivateMessage($data);
                break;
            
            default:
                return false;
                break;
        }
    }

    public function removeMessage(array $id, string $type){
        
    }
    
    public function removeConversation(int $id, int $user_id){
        
    }
}