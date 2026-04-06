<?php

require __DIR__ . '/bootstrap.php';

use Spn\Websocket\WebsocketServer;

$server = new WebsocketServer($_ENV['SOCK_HOST'], $_ENV['SOCK_PORT']);
$server->start();