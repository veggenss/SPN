<?php

use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '../');
$dotenv->load();

return[
    'mail'=>[
        'host'=>$_ENV['MAIL_HOST'],
        'username'=>$_ENV['MAIL_USERNAME'],
        'password'=>$_ENV['MAIL_PASSWORD'],
        'port'=>$_ENV['MAIL_PORT'],
        'encryption'=>$_ENV['MAIL_ENCRYPTION'],
    ],
];
