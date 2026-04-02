<?php
namespace Spn\Controllers;

use Spn\Service\ChatService;

class ChatController{
    private ChatService $chat;
    
    public function __construct(){
        $this->chat = new ChatService;
    }
    
    public function showChat(){
        require __DIR__ . '/../../views/chat/main.php';
    }
    
    
    //fetch relevant logs
    public function getUserLogs(){  
        try{
            header('Content-Type: application/json');
            echo json_encode([
                'public' => $this->chat->getChat(),
                'conversations' => $this->chat->getConversations($_SESSION['user']['id'])
            ]);
            exit; 
        }
        catch(\Spn\Exceptions\InvalException $e){
            echo json_encode([
                "class" => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }
        catch(\Spn\Exceptions\DatabaseException $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Noe gikk galt! Vennligst prøv igjen."
            ]);
            exit;
        }
        catch(\Exception $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Ukjent feil!"
            ]);
        }
    }
    
    
    //create user conversation
    public function makeConversation(){
        $data = json_decode(file_get_contents('php://input'), true);
        try{
            header('Content-Type: application/json');
            echo json_encode([
                'conversation' => $this->chat->makeConversation($_SESSION['user']['id'], $data)
            ]);
            exit;
        }
        catch(\Spn\Exceptions\InvalException $e){
            echo json_encode([
                "class" => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }
        catch(\Spn\Exceptions\DatabaseException $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Noe gikk galt! Vennligst prøv igjen"
            ]);
            exit;
        }
        catch(\Exception $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Ukjent feil!"
            ]);
        }
    }
    
    
    //send user message
    public function sendMessage(){
        $data = json_decode(file_get_contents('php://input'), true);
        try{
            $this->chat->sendMessage($data);
            exit;
        }
        catch(\Spn\Exceptions\InvalException $e){
            echo json_encode([
                "class" => "error",
                "message" => $e->getMessage()
            ]);
            exit;
        }
        catch(\Spn\Exceptions\DatabaseException $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Noe gikk galt! Vennligst prøv igjen"
            ]);
            exit;
        }
        catch(\Exception $e){
            error_log($e->getMessage());
            echo json_encode([
                "class" => "error",
                "message" => "Ukjent feil!"
            ]);
        }
    }
}