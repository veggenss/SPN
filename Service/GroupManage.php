<?php
namespace Spn\Service;

require '../include/db.inc.php';

use Spn\Database\WebServer;

class GroupManage{
    private \mysqli $mysql;

    public function __construct(){
        $webServer = new WebServer();
        $this->mysql = $webServer->connect();
    }
}