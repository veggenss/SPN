<?php
namespace Spn\Service;

require_once __DIR__ . '/db.inc.php';

use function Spn\Database\Connection;

class Authenticate{
    private \mysqli $mysqli;

    public function __construct(){
        $this->mysqli = Connection();
    }
}