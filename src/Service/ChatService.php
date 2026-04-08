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
    
    public function createWsToken($user_id): string
    {
        $this->userRepo->removeToken($user_id);
        
        $token = bin2hex(random_bytes(32));
        $expireAt = time() + 1200;
        if(!$this->userRepo->saveToken($token, $user_id, $expireAt)){
            throw new \Spn\Exceptions\InvalException("Couldn't save User Token");
        }
        return $token;
    }
    
    public function getChat(): array
    {
        return $this->chatRepo->getPublicMessages();
    }
    
    public function getConversations(int $user_id): array
    {
        return $this->chatRepo->getConversations($user_id);
    }
    
    public function makeConversation(int $userId, array $data): array|bool
    {
        $participants = [];
        
        foreach($data['parties'] as $party){
            $participants[] = (int)$this->userRepo->findByName($party)['id'];
        }
        
        if(in_array($userId, $participants)){
            throw new \Spn\Exceptions\InvalException("Kan ikke starte samtale med degselv!");
        }
        
        $participants[] = $userId;
        
        $userIds = $participants
            |> (fn($arr) => array_map('intval', $arr))
            |> (fn($arr) => array_unique($arr))
            |> (fn($arr) => array_values($arr));
        return $this->chatRepo->makeConversation($userIds, $data['conv_name']) ?: false;
    }
    
    public function sendMessage(array $data): array
    {
        if(empty($data['participants_id']) || count($data['participants_id']) <= 1){
            if(!$this->chatRepo->savePublicMessage($data)){
                throw new \Spn\Exceptions\ChatException("Kunne ikke dytte melding!");
            }
            $data['conv_id'] = NULL; //for nomalizing returning JSON
            return $data;
        }
        
        $data['conv_id'] = $this->chatRepo->findConvByParties($data['participants_id']);
        
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