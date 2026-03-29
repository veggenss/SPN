<?php
session_start();
header('Content-Type: application/json');

require '../Service/DirectMessage.php';

use Spn\Service\DirectMessage;

$directMessage = new DirectMessage();
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'] ?? $_GET['action'] ?? NULL;

switch($action){

    case 'getUserId':
        echo json_encode($directMessage->getUserId($_GET['reciverUser']));
        exit;

    case 'createConversation':
        echo json_encode($directMessage->createConversation($data['user1_id'], $data['user2_id']));
        exit;

    case 'loadConversationDiv':
        echo json_encode($directMessage->loadConversationDiv($data));
        exit;

    case 'loadConversationLog':
        echo json_encode($directMessage->loadConversationLog($data));
        exit;

    default:
        echo json_encode(["success" => false, "message" => "No action"]);
        exit;
}