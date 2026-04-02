<?php
namespace Spn\Repository;

use Spn\Database\Connection;

class ChatRepository{
    private $conn;
    
    public function __construct(){
        $this->conn = Connection::get();
    }
    
    public function getPublicMessages():array{
        try{
            $stmt = $this->conn->query('SELECT pm.*, u.username FROM public_messages pm INNER JOIN users u ON pm.user_id = u.id;');
            $pm = $stmt->fetch_all(MYSQLI_ASSOC);
            $stmt->free_result();
            return $pm;
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Get Public Messages Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function getConversations(int $id):array{
        try{
            $convStmt = $this->conn->prepare('
                SELECT c.id, c.date_added AS conv_created, c.latest_message, GROUP_CONCAT(DISTINCT u.username) AS participants
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
                $conv['messages'] = [];
                $convArr[$conv['id']] = $conv;
            }
            
            foreach($messages as $msg){
                if(isset($convArr[$msg['conversation_id']])){
                    $convArr[$msg['conversation_id']]['messages'][] = $msg;
                }
            }
            
            return array_values($convArr); //array_values fordi hvis ikke gjør PHP det til en objekt
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Get Conversations Failed: " . $e->getMessage(), 0, $e);
        }
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
        catch(\mysqli_sql_exception $e){
            $this->conn->rollback();
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("makeConversation Failed: " . $e->getMessage(), 0, $e);
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
    
    //finds a conversation between 2 users, return true if found, returns false otherwise
    public function findMutualConv(int $user1_id, int $user2_id):bool{
        try{
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
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Find Mutual Conv Failed: " . $e->getMessage(), 0, $e);
        }
    }
}