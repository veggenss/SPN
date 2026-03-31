<?php
namespace Spn\Repository;

use Exception;
use mysqli_sql_exception;
use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
    
    public function getPublicMessages():array{
        $stmt = $this->conn->query('SELECT pm.*, u.username FROM public_messages pm INNER JOIN users u ON pm.user_id = u.id;');
        $pm = $stmt->fetch_all(MYSQLI_ASSOC);
        $stmt->free_result();
        return $pm;
    }
    
    public function getPrivateMessages(int $id):mixed{
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
        $pm = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        $stmt->close();
        return $pm;
    }
    
    public function getConversations(int $id):mixed{
        $stmt = $this->conn->prepare('
            SELECT c.*, pm.message FROM conversations c 
            INNER JOIN conversation_members cm ON c.id = cm.conversation_id 
            LEFT JOIN private_messages pm ON pm.id = c.latest_message
            WHERE cm.user_id = ?;');
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $conv = $res->fetch_all(MYSQLI_ASSOC);
        $res->free();
        $stmt->close();
        return $conv;
    }
    
    public function makeConversation(int $user1_id, int $user2_id):int|bool{
        $this->conn->begin_transaction();
        try{
            $stmt = $this->conn->prepare('INSERT INTO conversations () VALUES ()');
            $stmt->execute();
            $stmt->close();
            $conv_id = $this->conn->insert_id;
            
            $stmt = $this->conn->prepare('INSERT INTO conversation_members (conversation_id, user_id) VALUES (?, ?), (?, ?)');
            $stmt->bind_param("iiii", $conv_id, $user1_id, $conv_id, $user2_id);
            $stmt->execute();
            $this->conn->commit();
            $stmt->close();
            return $conv_id;
        }
        catch(mysqli_sql_exception $exception){
            $this->conn->rollback();
            error_log($exception->getMessage());
            throw $exception;
        }
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
    
    //Finds a conversation between 2 users, return true if found, returns false otherwise
    public function findMutualConv(int $user1_id, int $user2_id):bool{
        $stmt = $this->conn->prepare('
            SELECT cm1.conversation_id 
            FROM conversation_members cm1 
            JOIN conversation_members cm2 
            ON cm1.conversation_id = cm2.conversation_id 
            WHERE cm1.user_id = ? 
            AND cm2.user_id = ? 
            LIMIT 1');
        $stmt->bind_param("ii", $user1_id, $user2_id);
        $stmt->execute();
        
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        
        $res->free();
        $stmt->close();
        return $exists;
    }
}