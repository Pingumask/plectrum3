<?php
require_once './vendor/autoload.php';

use GuzzleHttp\Psr7\Request;
use Pingumask\Plectrum\App;

$client_public_key = App::getConf('discord', 'client_id');
if (App::getConf('env', 'debug')) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

$body = file_get_contents('php://input') ?: '';
$request = new Request(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    getallheaders(),
    $body
);

$response = App::run($request);
App::sendResponse($response);
