<?php
namespace Spn\Service;

use Spn\Repository\ChatRepository;

class ChatService{
    private ChatRepository $chatRepo;
    
    public function __construct(){
        $this->chatRepo = new ChatRepository;
    }
    
    public function getChat(?int $id){
        if($id === NULL){
            $public = $this->chatRepo->getPublicMessages();
            return $public ?: [];  
        }
        
        $private = $this->chatRepo->getPrivateMessages($id);
        return $private ?: [];
    }
    
    public function getConversations(int $id){
        $conv = $this->chatRepo->getConversations($id);
        return $conv ?: [];
    }

    public function sendMessage(array $data):bool{
        switch($data['type']){
            case 'public':
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

    public function removeMessage(int $id, string $type){
        switch($type){
            case 'public':
                return $this->chatRepo->removePublicMessage($id);
                break;
            
            case 'private':
                return $this->chatRepo->removePrivateMessage($id);
                break;
            
            default:
                return false;
                break;
        }
    }
    
    public function removeConversation(int $id, int $user_id){
        
    }
}