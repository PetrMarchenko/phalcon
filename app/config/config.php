<?php

$dbConfig = [
    'adapter'     => 'Mysql',
    'host'        => '127.0.0.1',
    'username'    => 'root',
    'password'    => 'root',
    'dbname'      => 'phalcon_starter',
    'port'        => '3306',
    'persistent'  => true,
];

return new \Phalcon\Config([
    'debug' => true,
    'database' => $dbConfig,
    'db' => [
        'master' => $dbConfig,
        'slave' => $dbConfig,
    ],
    'mail' => array(
        'host' => 'smtp.gmail.com',
        'port' => 465,
        'username' => 'yii2starter@gmail.com',
        'password' => '123456qw',
        'security' => 'ssl',
        'from' => 'yii2starter@gmail.com'
    ),
]);
