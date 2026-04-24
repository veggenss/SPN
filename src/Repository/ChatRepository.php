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
            $stmt = $this->conn->query('SELECT pm.*, u.username FROM public_messages pm INNER JOIN users u ON pm.sender_id = u.id ORDER BY date_added ASC;');
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
                SELECT c.id, c.title, c.date_added AS conv_created, c.latest_message, GROUP_CONCAT(DISTINCT u.username) AS participants, GROUP_CONCAT(DISTINCT u.id) AS participants_id
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
            
            return array_values($convArr); //array_values since PHP converts it to object otherwise
        }
        catch(\mysqli_sql_exception $e){
            error_log($e->getMessage());
            throw new \Spn\Exceptions\DatabaseException("Get Conversations Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function makeConversation(array $userIds, string $title): int
    {
        $this->conn->begin_transaction();
        try{
            $stmt = $this->conn->prepare('INSERT INTO conversations (title) VALUES (?);');
            $stmt->bind_param("s", $title);
            $stmt->execute();
            $stmt->close();
            $conv_id = $this->conn->insert_id;

            $placeholders = implode(',', array_fill(0, count($userIds), '(?, ?)'));
            $types = str_repeat('ii', count($userIds));
            $params = [];
            
            foreach($userIds as $id){
                $params[] = $conv_id;
                $params[] = $id;
            }
            
            $stmt = $this->conn->prepare("INSERT INTO conversation_members (conversation_id, user_id) VALUES $placeholders;");
            $stmt->bind_param($types, ...$params);
            
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
    
    public function findConvByParties(array $userIds): mixed
    {
        $numUsers = count($userIds);
        if ($numUsers < 2 || $numUsers > 10) {
            throw new \Spn\Exceptions\InvalException("Participants must be between 2 and 10.");
        }
    
        $placeholders = implode(',', array_fill(0, $numUsers, '?'));
        $sql = "
            SELECT cm.conversation_id
            FROM conversation_members cm
            GROUP BY cm.conversation_id
            HAVING COUNT(*) = ? AND SUM(cm.user_id IN ($placeholders)) = ?
            LIMIT 1;
        ";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $types = str_repeat('i', $numUsers + 2);
            $params = array_merge([$numUsers], $userIds, [$numUsers]);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            return $result['conversation_id'] ?? null;
        } catch (\mysqli_sql_exception $e) {
            throw new \Spn\Exceptions\DatabaseException("findConvByParties Failed: " . $e->getMessage(), 0, $e);
        }
    }
    
    public function savePrivateMessage(array $data)
    {
        try{
            $stmt = $this->conn->prepare('INSERT INTO private_messages (conversation_id, sender_id, message) VALUES (?, ?, ?);');
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
            $stmt = $this->conn->prepare('INSERT INTO public_messages (sender_id, message) VALUES (?, ?);');
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
}
