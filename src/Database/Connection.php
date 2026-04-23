<?php
namespace Spn\Database;

use mysqli;

class Connection{
    private static ?mysqli $conn = null;
    
    public static function get(): mysqli{
        if(!self::$conn){
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            
            self::$conn = new \mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME']
            );
            
            if(self::$conn->connect_errno){
                die("Database Connection Error: " . self::$conn->connect_error);
            }
        }
        return self::$conn;
    }
}