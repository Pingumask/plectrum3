<?php
require_once '../vendor/autoload.php';

use Pingumask\Plectrum\App;

$clientId = App::getConf('discord', 'client_id');
$token = App::getConf('discord', 'token');
$commands = [];
$dir = new DirectoryIterator("../src/command");
$nb = 0;
foreach ($dir as $fileinfo) {
    $filename = $fileinfo->getFileName();
    list($className, $extension) = explode('.', $filename);
    $class = "Pingumask\Plectrum\Command\\$className";
    if (class_exists($class)) {
        $command = new $class();
        $commands[] = $command::getDefinition();
        $nb++;
    }
}
$body = json_encode($commands);
$client = new GuzzleHttp\Client();
$res = $client->request(
    'POST',
    "https://discord.com/api/applications/$clientId/commands",
    [
        'headers' => [
            'Authorization' => "Bot $token",
            'Content-Type' => 'application/json'
        ],
        'body' => $body
    ]
);
