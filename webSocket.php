<?php
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\App;
use Ratchet\WebSocket\WsConnection;
use Spn\Service\DirectMessage;
use Spn\Service\GlobalChat;
use  Spn\Database\WebServer;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/include/db.inc.php';
require __DIR__ . '/Service/DirectMessage.php';
require __DIR__ . '/Service/GlobalChat.php';

$socketParams = new WebServer();

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $userConnections = [];
    private GlobalChat $globalChatService;
    private DirectMessage $directMessage;

    public function __construct() {
        $this->clients = new \SplObjectStorage();
        $this->directMessage = new DirectMessage();
        $this->globalChatService = new GlobalChat();
    }

    private function date():string{
        return date("[Y/m/d l:H:i:s]");
    }

    private function sendToUser(int $userId, int $recipientId, string $message):void{
        if (isset($this->userConnections[$userId])) {
            foreach ($this->userConnections[$userId] as $conn) {
                $conn->send($message);
            }
        }

        // sender meldingen til mottaker
        if (isset($this->userConnections[$recipientId])) {
            foreach ($this->userConnections[$recipientId] as $conn) {
                $conn->send($message);
            }
        }
    }

    public function onOpen(ConnectionInterface $conn):void{

        /** @var WsConnection $conn */
        $query = [];
        parse_str($conn->httpRequest->getUri()->getQuery(), $query);

        $userId = isset($query['userId']) ? (int)$query['userId'] : null;
        $this->clients[$conn] = ['userId' => $userId];
        $resourceId = spl_object_id($conn);

        if ($userId !== null){

            if(!isset($this->userConnections[$userId])){
                $this->userConnections[$userId] = new \SplObjectStorage();
            }

            $this->userConnections[$userId][$conn] = true;

            echo "{$this->date()} ID-{$userId}({$resourceId}) er tilkoblet\n";
            file_put_contents(__DIR__ . '/webSocketLog.syslog', "{$this->date()} ID-{$userId}({$resourceId}) er tilkoblet\n", FILE_APPEND);
        }
        else {
            echo "{$this->date()} Ukjent bruker koblet til {$resourceId}\n";
            file_put_contents(__DIR__ . '/webSocketLog.syslog', "{$this->date()} Ukjent bruker koblet til {$resourceId}\n", FILE_APPEND);
        }
    }

    // når en melding blir sendt
    public function onMessage(ConnectionInterface $fromConn, $msg):void{
        try {
            $data = json_decode($msg, true);

            // hvis det ikke var noe i meldingen
            if (!$data || !isset($data['username'], $data['message'], $data['profilePictureUrl'])) {
                return;
            }

            $userId = $this->clients[$fromConn]['userId'] ?? null;
            if (!$userId) {
                echo "{$this->date()} Bruker-ID mangler fra tilkoblingen\n";
                file_put_contents(__DIR__ . '/WebSocket_error.log', "{$this->date()} Bruker-ID mangler fra tilkoblingen\n", FILE_APPEND);
                return;
            }

            // dataen fra meldingen
            $messageData = [
                'recipientId' => $data['recipientId'],
                'type' => $data['type'],
                'username' => $data['username'],
                'userId' => $userId,
                'profilePictureUrl' => $data['profilePictureUrl'],
                'message' => $data['message']
            ];

            // sjekker hvis du er i global chat, og logger deretter til global_chat_log.txt :D
            if ($data['type'] === 'global' && $data['recipientId'] === 'all') {
                $encodedMessage = json_encode($messageData);

                $response = $this->globalChatService->pushMessage($messageData);

                if (!$response['success']) {
                    echo $response['message'];
                    return;
                }

                foreach ($this->clients as $clientConn) {
                    $clientConn->send($encodedMessage);
                }
            }

            // hvis du ikke er i global chat, call heller på directMessage() funksjonen og pass messageData over til den
            elseif ($data['type'] === 'direct' && $data['recipientId'] !== 'all') {
                $dmResponse = $this->directMessage->pushMessage($messageData);
                if (!$dmResponse['success']) {
                    echo $dmResponse['message'];
                    return;
                }
                if (!$this->directMessage->previewString($dmResponse['convId'], $messageData['message']))
                    return;
                $messageData['convId'] = $dmResponse['convId'];
                $this->sendToUser($messageData['userId'], $messageData['recipientId'], json_encode($messageData));
            }
        }
        catch (\Throwable $e){
            file_put_contents(__DIR__ . '/WebSocket_error.log', $this->date() . " Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);
        }
    }

    // når tilkobling til websocket blir lukket -> når en bruker disconnecter eller hvis tilkoblingen krasjer
    public function onClose(ConnectionInterface $conn):void{
        $userId = $this->clients[$conn]['userId'] ?? 'unknown';
        $resourceId = spl_object_id($conn);

        foreach ($this->userConnections as $userId => $connections) {
            if (isset($connections[$conn])) {
                unset($connections[$conn]);

                if (count($connections) === 0) {
                    unset($this->userConnections[$userId]);
                }

                break;
            }
        }
        unset($this->clients[$conn]);

        echo "{$this->date()} ID-{$userId}({$resourceId}) er frakoblet\n";
        file_put_contents(__DIR__ . '/webSocketLog.syslog', "{$this->date()} ID-{$userId}({$resourceId}) er frakoblet\n", FILE_APPEND);
    }

    // sender feilmeldinger til error log fil :D
    public function onError(ConnectionInterface $conn, \Exception $e):void{
        file_put_contents(__DIR__ . '/WebSocket_error.log', $this->date() . " Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n", FILE_APPEND);

        $conn->close();
    }
}

// lager websocket :D
$server = new App($socketParams->socketParams()['hostname'], $socketParams->socketParams()['port']);
$server->route($socketParams->socketParams()['route'], new Chat, ['*']);
$server->run();
