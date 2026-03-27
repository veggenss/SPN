<?php
namespace Spn\Service;

require_once __DIR__ . '/db.inc.php';

use Spn\Database\WebServer;

class User{
    private \mysqli $mysqli;

    public function __construct(){
        $webServer = new WebServer();
        $this->mysqli = $webServer->connect();
    }

    /**
     * @param $data
     */
    public function newUser(array $data):bool{
        return 0;
    }

    /**
     * @param $data
     */
    public function validateUser(array $data):bool{
        return 0;
    }

    public function deleteUser():bool{
        return 0;
    }
}