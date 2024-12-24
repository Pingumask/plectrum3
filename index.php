<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
set_time_limit(900);
error_reporting(E_ALL);

require_once './vendor/autoload.php';

use Pingumask\Plectrum\App;
use GuzzleHttp\Psr7\Request;

$client_public_key = App::getConf('discord', 'client_id');

$body = file_get_contents('php://input');
$request = new Request(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI'],
    getallheaders(),
    $body
);

$response = App::run($request);
die();
