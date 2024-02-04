<?php

use Pingumask\Plectrum\Controller\Ping;
use Pingumask\Plectrum\Model\Config;

ini_set('display_errors', '1');
ini_set('log_errors', '1');
error_reporting(E_ALL);
$client_public_key = Config::get('discord', 'client_id');

$headers = getallheaders();
$payload = file_get_contents('php://input');
$result = endpointVerify($headers, $payload, $client_public_key);
http_response_code($result['code']);
echo json_encode($result['payload']);
if ($result['code'] !== 200) {
    fwrite(fopen('php://stderr', 'w'), "[{$result['code']}] {$result['payload']}" . PHP_EOL);
}

function endpointVerify(array $headers, string $payload, string $publicKey): array
{
    if (
        !isset($headers['X-Signature-Ed25519'])
        || !isset($headers['X-Signature-Timestamp'])
    ) {
        return ['code' => 401, 'payload' => 'Missing Header'];
    }

    $signature = $headers['X-Signature-Ed25519'];
    $timestamp = $headers['X-Signature-Timestamp'];

    if (!trim($signature, '0..9A..Fa..f') == '') {
        return ['code' => 401, 'payload' => 'Malformed Signature'];
    }

    $message = $timestamp . $payload;
    $binarySignature = sodium_hex2bin($signature);
    $binaryKey = sodium_hex2bin($publicKey);

    if (!sodium_crypto_sign_verify_detached($binarySignature, $message, $binaryKey)) {
        return ['code' => 401, 'payload' => 'Wrong Signature'];
    }

    $payload = json_decode($payload, true);
    switch ($payload['type']) {
        case 1:
            return ['code' => 200, 'payload' => ['type' => 1]];
        case 2:
            return Ping::execute($payload);
        default:
            return ['code' => 400, 'payload' => 'Unknown Payload Type'];
    }
}
