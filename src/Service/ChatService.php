<?php
namespace Spn\Service;

use Spn\Repository\ChatRepository;
use Spn\Repository\UserRepository;

class ChatService{
    private ChatRepository $chatRepo;
    private UserRepository $userRepo;
    
    public function __construct(){
        $this->chatRepo = new ChatRepository;
        $this->userRepo = new UserRepository;
    }
    
    public function getChat(?int $user_id):array{
        return $user_id === NULL ? $this->chatRepo->getPublicMessages() : $this->chatRepo->getPrivateMessages($user_id);
    }
    
    public function getConversations(int $user_id):array{
        return $this->chatRepo->getConversations($user_id);
    }
    
    public function makeConversation(int $user1_id, string $user2_name):array|bool{
        $user2_id = $this->userRepo->findByName($user2_name)['id'];
        if(!$user2_id){
            throw new \Spn\Exceptions\InvalException("Bruker '{$user2_name}' eksisterer ikke.");
        }
        elseif($user2_id === $user1_id){
            throw new \Spn\Exceptions\InvalException("Kan ikke starte samtale med degselv!");
        }
        
        if($this->chatRepo->findMutualConv($user1_id, $user2_id)){
            throw new \Spn\Exceptions\InvalException("Samtalen finnes allerede.");
        }
        
        return $this->chatRepo->makeConversation($user1_id, $user2_id) ?: false;
    }
    
    public function sendMessage(array $data):bool{
        switch($data['type']){
            case 'public':
                return $this->chatRepo->savePublicMessage($data) ?: false;
                break;
            
            case 'private':
                return $this->chatRepo->savePrivateMessage($data) ?: false;
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