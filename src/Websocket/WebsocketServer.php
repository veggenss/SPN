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
            $token = $request->get['token'] ?? null;
            $userId = ((new \Spn\Service\UserService)->getUserFromToken((string)$token));
            
            if (!$userId) {
                $server->disconnect($request->fd, 1008, "Invalid token");
                return;
            }
            
            $connections->add($request->fd, $userId);
            print "New Connection! \nToken => $token \nfd => $request->fd \nuserID => $userId \n";
        });
        
        $this->server->on('message', function(Server $server, Frame $frame) use ($chatService, $connections)
        {
            $data = json_decode($frame->data, true);
            print_r($data);
            if(!$data){
                return;
            }
            
            $msg = $chatService->sendMessage($data);
            
            $connections->pruneDeadFds($server);
            
            if(!empty($msg['participants_id']) && count($msg['participants_id']) > 1){
                foreach($connections->findFdsByUsers($msg['participants_id']) as $fd){
                    if($server->isEstablished($fd)){
                        $server->push($fd, json_encode($msg));
                    }
                } 
            }
            else{
                foreach($connections->allFds() as $fd){
                    if($server->isEstablished($fd)){
                        $server->push($fd, json_encode($msg));
                    }
                }             
            }
        });
        
        $this->server->on('Close', fn($server, $fd) => $connections->remove($fd));
        
        $this->server->on('Disconnect', fn($server, $fd) => $connections->remove($fd));
    }
    
    public function start(): void
    {
        $this->server->start();
    }
}