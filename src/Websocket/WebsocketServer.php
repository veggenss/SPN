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
            $userId = (int)($request->get['userId'] ?? 0);
            $connections->add($request->fd, $userId);
        });
        
        $this->server->on('Message', function(Server $server, Frame $frame) use ($chatService, $connections){
           $data = json_decode($frame->data, true);
           
           if(!$data){
               return;
           }
           
           $action = $chatService->sendMessage($data);

            if($action['recipient']){
                return;
            }
            
            foreach($connections->allFds() as $fd){
                $server->push($fd, json_encode($action['data']));
            }
        });
        
        $this->server->on('Close', function(Server $server, int $fd) use ($connections){
           $connections->remove($fd); 
        });
    }
    
    public function start(): void
    {
        $this->server->start();
    }
}