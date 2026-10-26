<?php

require __DIR__.'/../vendor/autoload.php';

$env = Dotenv\Dotenv::parse(file_get_contents(__DIR__.'/../.env'));
if (empty($env['LOCAL_MYSQL_ROOT_PASSWORD'])) {
    throw new RuntimeException('Local MySQL administrator password is missing from .env.');
}

$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', $env['LOCAL_MYSQL_ROOT_PASSWORD'], [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);
$pdo->exec('SHUTDOWN');
echo "Local MySQL stopped.\n";
