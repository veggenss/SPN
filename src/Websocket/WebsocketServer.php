<?php
namespace Spn\Websocket;

use OpenSwoole\Websocket\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\WebSocket\Frame;
use Spn\Service\ChatService;
use Spn\Websocket\ConnectionManager;

class WebsocketServer
{
    private Server $server;
    
    public function __construct(private string $host, private int $port)
    {
        $this->server = new Server($this->host, $this->port);
        
        $connections = new ConnectionManager;
        $chatService = new ChatService;    
        
        $this->server->on('Start', fn() => print "Websocket started on {$this->host}:{$this->port}\n");
        
        $this->server->on('Open', function(Server $server, Request $request) use ($connections)
        {
            $userId = (int)$connections->newConn();
            $connections->add($request->fd, $userId);
            print_r($connections->allFds());
        });
        
        $this->server->on('message', function(Server $server, Frame $frame) use ($chatService, $connections)
        {
            $data = json_decode($frame->data, true);
            print_r($data);
            if(!$data){
                return;
            }
            
            $action = $chatService->sendMessage($data);
            
            if(count($action['participants_id']) > 1){
                foreach($connections->findFdsByUsers($action['participants_id']) as $fd){
                    $server->push((string)$fd, json_encode($action['data']));
                } 
            }
            else{
                foreach($connections->allFds() as $fd){
                    $server->push((string)$fd, json_encode($action['data']));
                }             
            }
        });
        
        $this->server->on('Close', function(string $fd) use ($connections)
        {
           $connections->remove((string)$fd); 
        });
    }
    
    public function start(): void
    {
        $this->server->start();
    }
}