<?php

require __DIR__ . '/bootstrap.php';

use Spn\Websocket\WebsocketServer;

$server = new WebsocketServer("127.0.0.1", 9501);
$server->start();