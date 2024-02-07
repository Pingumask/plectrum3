<?php

namespace Pingumask\Plectrum;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PDO;
use PDOException;
use Pingumask\Plectrum\Partial\AbstractCommand;

class App
{
    private static mixed $config = null;
    private static ?PDO $database = null;

    public static function basePath(): string
    {
        return dirname(__DIR__);
    }

    public static function getConf(string $section, string $line): mixed
    {
        if (is_null(self::$config)) {
            self::$config = parse_ini_file("../config.ini", true, INI_SCANNER_TYPED);
        }
        if (self::$config === false) {
            return null;
        }
        return self::$config[$section][$line] ?? null;
    }

    /**
     * @throws PDOException
     * @return PDO
     */
    public static function getDB(): PDO
    {
        if (!is_null(self::$database)) {
            return self::$database;
        }
        try {
            $database = new PDO(
                self::getConf('database', 'dsn'),
                self::getConf('database', 'user'),
                self::getConf('database', 'password')
            );
            $database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $database->exec("SET NAMES 'utf8mb4'");
            return $database;
        } catch (Exception $e) {
            throw new PDOException("Database configuration error");
        }
    }

    public static function run(Request $request): Response
    {
        if (!self::checkSignature($request)) {
            return new Response(401, [], "Wrong signature");
        }

        try {
            self::getDB();
        } catch (PDOException $e) {
            return new Response(500, [], "Database misconfiguration");
        }


        $payload = json_decode($request->getBody(), true);
        if ($payload['type'] === 1) {
            $body = json_encode(['type' => 1]) ?: '';
            return new Response(200, [], $body);
        } elseif ($payload['type'] === 2) {
            return self::handleCommand($request);
        }
        return new Response(501, [], "Not implemented");
    }

    public static function sendResponse(Response $response): void
    {
        self::emitHeaders($response);
        self::emitStatusLine($response);
        self::emitBody($response);
    }

    private static function emitHeaders(Response $response): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            $first = strtolower((string)$name) !== 'set-cookie';
            foreach ($values as $value) {
                $header = sprintf('%s: %s', $name, $value);
                header($header, $first);
                $first = false;
            }
        }
    }

    private static function emitStatusLine(Response $response): void
    {
        $statusLine = sprintf(
            'HTTP/%s %s %s',
            $response->getProtocolVersion(),
            $response->getStatusCode(),
            $response->getReasonPhrase()
        );
        header($statusLine, true, $response->getStatusCode());
    }

    private static function emitBody(Response $response): void
    {
        echo $response->getBody();
    }

    private static function checkSignature(Request $request): bool
    {
        $headers = $request->getHeaders();
        $payload = $request->getBody();
        $publicKey = self::getConf('discord', 'public_key');
        if (
            !isset($headers['X-Signature-Ed25519'])
            || !isset($headers['X-Signature-Timestamp'])
        ) {
            return false;
        }

        $signature = $headers['X-Signature-Ed25519'][0];
        $timestamp = $headers['X-Signature-Timestamp'][0];

        if (empty(trim($signature, '0..9A..Fa..f'))) {
            return false;
        }

        $message = $timestamp . $payload;

        $binarySignature = sodium_hex2bin($signature);
        $binaryKey = sodium_hex2bin($publicKey);
        if (empty($binarySignature) || empty($binaryKey)) {
            return false;
        }
        if (!sodium_crypto_sign_verify_detached($binarySignature, $message, $binaryKey)) {
            return false;
        }

        return true;
    }

    private static function handleCommand(Request $request): Response
    {
        $params = explode('/', $request->getUri()->getPath());
        $commandName = (ucfirst($params[1]) ?: 'Home') . "Controller";
        $commmandClass = "Pingumask\Plectrum\Command\\" . $commandName;

        spl_autoload_register(function ($commandName) {
            $commandPath = self::basePath() . "/src/command/$commandName.php";
            if (file_exists($commandPath)) {
                require_once $commandPath;
            }
        });

        if (
            class_exists($commmandClass)
        ) {
            $controller = new $commmandClass();
            if (is_a($controller,  AbstractCommand::class)) {
                return $commmandClass::execute($request);
            } else {
                return new Response(501, [], "Not implemented");
            }
        } else {
            return new Response(501, [], "Not implemented");
        }
    }
}
