<?php
namespace Spn\Service;

use Spn\Repository\ChatRepository;
use Spn\Repository\UserRepository;

class ChatService{
    private ChatRepository $chatRepo;
    private UserRepository $userRepo;
    
    public function __construct()
    {
        $this->chatRepo = new ChatRepository;
        $this->userRepo = new UserRepository;
    }
    
    public function getChat(): array
    {
        return $this->chatRepo->getPublicMessages();
    }
    
    public function getConversations(int $user_id): array
    {
        return $this->chatRepo->getConversations($user_id);
    }
    
    public function makeConversation(int $user1_id, string $user2_name): array|bool
    {
        $user2_id = $this->userRepo->findByName($user2_name)['id'];
        if(!$user2_id){
            throw new \Spn\Exceptions\InvalException("Bruker '{$user2_name}' eksisterer ikke.");
        }
        elseif($user2_id === $user1_id){
            throw new \Spn\Exceptions\InvalException("Kan ikke starte samtale med degselv!");
        }
        
        if($this->chatRepo->findMutualConv([$user1_id, $user2_id]))
        {
            throw new \Spn\Exceptions\InvalException("Samtalen finnes allerede.");
        }
        
        return $this->chatRepo->makeConversation($user1_id, $user2_id) ?: false;
    }
    
    public function sendMessage(array $data): array
    {
        if(count($data['participants_id']) <= 1 || NULL){
            if(!$this->chatRepo->savePublicMessage($data)){
                throw new \Spn\Exceptions\ChatException("Kunne ikke dytte melding!");
            }
            return $data;
        }
        
        $data['conv_id'] = $this->chatRepo->findMutualConv($data['participants_id']);
        
        if(!$this->chatRepo->savePrivateMessage($data)){
           throw new \Spn\Exceptions\ChatException("Kunne ikke dytte melding!");
        }
        return $data; 
    }

    public function removeMessage(int $id, ?int $convId)
    {   
        if($convId === NULL){
            return $this->chatRepo->removePublicMessage($id);
        }
        return $this->chatRepo->removePrivateMessage($id);
    }

    public function removeConversation(int $id, int $user_id)
    {
        
    }
}