<?php

namespace Spn\Websocket;

use OpenSwoole\Table;

class ConnectionManager
{
    private Table $table;
    
    public function __construct()
    {
        $this->table = new Table(1024);
        $this->table->column('user_id', Table::TYPE_INT);
        $this->table->create();
    }
 
    public function newConn()
    {
        $user = ((new \Spn\Service\UserService)->getUserSession());
        return $user['id'];
    }
    
    public function allFds(): array
    {
        $fds = [];
        foreach($this->table as $key => $_){
            $fds[] = (int)$key;
        }
        
        return $fds;
    }
    
    public function findFdsByUsers(array $userId): array
    {   
        $fds = [];
        if(count($userId) === 1){
            foreach($this->table as $row){
                if($row['user_id'] === $userId){
                    $fds = $row['fd'];
                }
            } 
            return $fds;
        }
        
        foreach($userId as $id){
            foreach($this->table as $row){
                if($row['user_id'] === $id){
                    $fds[] = $row['fd'];
                }
            }
        }
        return $fds;
    }
    
    public function add(string $fd, int $userId)
    {
        $this->table->set((string)$fd, array('user_id' => $userId));
    }
    
    public function remove(string $fd)
    {
        $this->table->del((string)$fd);
    }
}