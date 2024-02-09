<?php
require_once '../vendor/autoload.php';

use Pingumask\Plectrum\App;

$clientId = App::getConf('discord', 'client_id');
$token = App::getConf('discord', 'token');
$commands = [];
$dir = new DirectoryIterator("../src/command");
$client = new GuzzleHttp\Client();
foreach ($dir as $fileinfo) {
    $filename = $fileinfo->getFileName();
    list($className, $extension) = explode('.', $filename);
    $class = "Pingumask\Plectrum\Command\\$className";
    if (class_exists($class)) {
        $command = new $class();
        $body = json_encode($command::getDefinition());
        $client->request(
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
        fwrite(STDOUT, $command::NAME . " registered" . PHP_EOL);
    }
}