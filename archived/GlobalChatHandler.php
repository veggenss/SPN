<?php
session_start();
header('Content-Type: application/json');

// require '../Service/GlobalChat.php';

// use Spn\Service\GlobalChat;

// $globalChat = new GlobalChat();
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? NULL;

switch($action){
    case 'getGlobalLogs':
        echo json_encode($globalChat->getLogs());
        exit;

    case 'pushMessage':
        echo json_encode($globalChat->pushMessage($action['message']));
        exit;

    default:
        echo json_encode(["success" => false, "message" => "No Action"]);
        exit;
}