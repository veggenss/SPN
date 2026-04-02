<?php
namespace Spn\WebSocket;

use OpenSwoole\Websocket\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Websocket\Frame;
use Spn\Service\ChatService;

$server = new Server("127.0.0.1", 9501);

$server->on("Start", function(Server $server)
{
    echo "OpenSwoole Websocket Started on spn.local:9501\n";
}); 

$server->on('Open', function(Server $server, Request $request)
{ 
    echo "Connection open: {$request->fd}\n";
    $server->tick(1000, function() use ($server, $request)
    {
        $server->push($request->fd, json_encode(["hello", time()]));
    });
});

$server->on('Message', function(Server $server, Frame $frame)
{
    echo "received message: {$frame->data}\n";

    $server->push($frame->fd, json_encode(["hello", time()]));
});

$server->on('Close', function(Server $server, int $fd)
{
    echo "Connection close: {$fd}\n";
});

$server->on('Disconnect', function(Server $server, int $fd)
{
    echo "connection disconnect: {$fd}\n";
});

$server->start();