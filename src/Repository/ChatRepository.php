<?php
namespace Spn\Repository;

use Exception;
use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct()
    {
        $this->conn = Connection::get();
    }
    
    public function getPublicMessages(): array
    {
        try{
            $stmt = $this->conn->query('SELECT pm.*, u.username FROM public_messages pm INNER JOIN users u ON pm.sender_id = u.id;');
            $pm = $stmt->fetch_all(MYSQLI_ASSOC);
            $stmt->free_result();
            return $pm;
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Get Public Messages Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function getConversations(int $id): array
    {
        try{
            $convStmt = $this->conn->prepare('
                SELECT c.id, c.date_added AS conv_created, c.latest_message, GROUP_CONCAT(DISTINCT u.username) AS participants, GROUP_CONCAT(DISTINCT u.id) AS participants_id
                FROM conversations c
                JOIN conversation_members cm ON c.id = cm.conversation_id
                JOIN users u ON cm.user_id = u.id
                WHERE c.id IN (SELECT conversation_id FROM conversation_members WHERE user_id = ?)
                GROUP BY c.id ORDER BY c.date_added ASC;
            ');
            $convStmt->bind_param("i", $id);
            $convStmt->execute();
            $convRes = $convStmt->get_result();
            
            $conversations = $convRes->fetch_all(MYSQLI_ASSOC);
            $convRes->free();
            $convStmt->close();
            
            $msgStmt = $this->conn->prepare('
                SELECT pm.*, sender.username AS sender_username FROM private_messages pm
                JOIN users sender ON pm.sender_id = sender.id
                WHERE pm.conversation_id IN (SELECT conversation_id FROM conversation_members WHERE user_id = ?)
                ORDER BY pm.conversation_id, pm.date_sent ASC;
            ');
            $msgStmt->bind_param("i", $id);
            $msgStmt->execute();
            $msgRes = $msgStmt->get_result();
            
            $messages = $msgRes->fetch_all(MYSQLI_ASSOC);
            $msgRes->free();
            $msgStmt->close();
            
            $convArr = [];
            
            foreach($conversations as $conv){
                $conv['participants'] = explode(',', $conv['participants']);
                $conv['participants_id'] = $conv['participants_id'] 
                    |> (fn($arr) => explode(',', $arr)) 
                    |> (fn($arr) => array_map('intval', $arr));
                $conv['messages'] = [];
                $convArr[$conv['id']] = $conv;
            }
            
            foreach($messages as $msg){
                if(isset($convArr[$msg['conversation_id']])){
                    $convArr[$msg['conversation_id']]['messages'][] = $msg;
                }
            }
            
            return array_values($convArr); //array_values fordi hvis ikke gjør PHP det om til en objekt
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Get Conversations Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function makeConversation(int $user1_id, int $user2_id): int
    {
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
        catch(\mysqli_sql_exception $e){
            $this->conn->rollback();
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("makeConversation Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function savePrivateMessage(array $data)
    {
        try{
            $stmt = $this->conn->prepare('INSERT INTO private_messages (conversation_id, sender_id, message) VALUES (?, ?, ?)');
            $stmt->bind_param("iis", $data['conv_id'], $data['sender_id'], $data['message']);
            
            $status = $stmt->execute();
            $stmt->close();
            return $status;
        }
        catch(\mysqli_sql_exception $e){
            throw new \Spn\Exceptions\DatabaseException("Private Message Insetion Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function savePublicMessage(array $data)
    {
        try{
            $stmt = $this->conn->prepare('INSERT INTO public_messages (sender_id, message) VALUES (?, ?)');
            $stmt->bind_param("is", $data['sender_id'], $data['message']);
            
            $status = $stmt->execute();
            $stmt->close();
            return $status;
        }
        catch(\mysqli_sql_exception $e){
            throw new \Spn\Exceptions\DatabaseException("Public Message Insetion Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function removePrivateMessage(int $id)
    {
        
    }
    
    public function removePublicMessage(int $id)
    {
        
    }
    
    public function removeConversation(int $id)
    {
        
    }
    
    //finds a conversation between 2 users, return true if found, returns false otherwise
    public function findMutualConv(array $user_ids)
    {
        try{
            $user_ids = array_values(array_unique($user_ids));
            $count = count($user_ids);
            
            if($count < 2 || $count > 10){
                throw new \Spn\Exceptions\InvalException("User count must be between 2 and 10");
            }
            
            $placeholders = implode(',', array_fill(0, $count, '?'));
            
            $stmt = $this->conn->prepare("
                SELECT conversation_id FROM conversation_members
                GROUP BY conversation_id
                HAVING COUNT(*) = ? AND SUM(user_id IN ($placeholders)) = ? 
                LIMIT 1;
            ");
            
            $types = str_repeat('i', $count + 2);
            $params = array_merge([$count], $user_ids, [$count]);
            
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
                
            $res = $stmt->get_result();
            $stmt->close();
            if($row = $res->fetch_assoc()){
                $res->free();
                return (int)$row['conversation_id'];
            }
            $res->free();
            return NULL;
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Find Mutual Conv Failed: " . $e->getMessage(), 0, $e);
        }
    }
}