<?php
namespace Spn\Repository;

use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
    
    public function getPublicMessages(){
        $stmt = $this->conn->query('SELECT pm.*, u.username FROM public_messages pm INNER JOIN users u ON pm.user_id = u.id;');
        $pm = $stmt->fetch_all(MYSQLI_ASSOC);
        $stmt->free_result();
        return $pm;
    }
    
    public function getPrivateMessages(int $id){
        $stmt = $this->conn->prepare('
            SELECT pm.id, pm.message, pm.date_sent, u.username AS user1_username, u2.username AS user2_username FROM private_messages pm 
            INNER JOIN users u ON pm.sender_id = u.id
            INNER JOIN conversation_members cm ON pm.conversation_id = cm.conversation_id
            INNER JOIN users u2 ON cm.user_id = u2.id AND u2.id != ?
            WHERE pm.conversation_id IN (SELECT conversation_id FROM conversation_members WHERE user_id = ?)
            ORDER BY pm.conversation_id, pm.date_sent ASC;');
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $pm = $res->fetch_assoc();
        $res->free();
        $stmt->close();
        return $pm;
    }
    
    public function getConversations(int $id){
        $stmt = $this->conn->prepare('
            SELECT c.*, pm.message FROM conversations c 
            INNER JOIN conversation_members cm ON c.id = cm.conversation_id 
            LEFT JOIN private_messages pm ON pm.id = c.latest_message
            WHERE cm.user_id = ?;');
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $conv = $res->fetch_assoc();
        $res->free();
        $stmt->close();
        return $conv;
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