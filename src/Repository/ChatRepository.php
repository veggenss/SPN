<?php
namespace Spn\Repository;

use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
    
    public function getPublicMessages(){
        $stmt = $this->conn->query("SELECT * FROM public_messages");
    }
    
    public function getConversations(int $id){
        
    }
    
    public function getPrivateMessages(int $id){
        
    }
    
    public function makeConversation(int $user1_id, int $user2_id){
        
    }
    
    public function savePrivateMessage(array $data){
        return true;
    }
    
    public function savePublicMessage(array $data){
        return true;
    }
    
    public function updatePreview(int $id){
        
    }
    
    public function removePrivateMessage(int $id){
        
    }
    
    public function removePublicMessage(int $id){
        
    }
    
    public function removeConversation(int $id){
        
    }
}