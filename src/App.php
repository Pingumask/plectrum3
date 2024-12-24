<?php

namespace Pingumask\Plectrum;

use Exception;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PDO;
use PDOException;
use Pingumask\Discord\AbstractButton;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\AbstractSelect;
use Pingumask\Discord\ComponentType;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\InteractionType;
use RuntimeException;

class App
{
    private static mixed $config = null;
    private static ?PDO $database = null;

    public static function basePath(): string
    {
        return dirname(__DIR__);
    }

    public static function getConf(string $section, string $line, mixed $default = null): mixed
    {
        if (is_null(self::$config)) {
            self::$config = parse_ini_file(self::basePath() . "/config.ini", true, INI_SCANNER_TYPED);
        }
        if (self::$config === false) {
            return null;
        }
        return self::$config[$section][$line] ?? $default;
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

    public static function run(Request $request): void
    {
        try {
            self::logRequest($request);
        } catch (PDOException $e) {
            self::sendReply(new Response(503, [], "Database misconfiguration"));
            return;
        }
        if (!self::getConf('discord', 'skip_signature_check') && !self::checkSignature($request)) {
            self::sendReply(new Response(401, [], "Wrong signature"));
            return;
        }

        $payload = json_decode($request->getBody(), true);
        switch ($payload['type']) {
            case InteractionType::INTERACTION_TYPE_PING->value:
                $body = json_encode(['type' => InteractionCallback::PONG]) ?: '';
                self::sendReply(new Response(200, [], $body));
                return;
            case InteractionType::INTERACTION_TYPE_APPLICATION_COMMAND->value:
                self::handleCommand($request);
                return;
            case InteractionType::INTERACTION_TYPE_MESSAGE_COMPONENT->value:
                self::handleComponent($request);
                return;
            default:
                self::sendReply(new Response(501, [], "Interaction not implemented"));
        }
    }

    public static function sendReply(Response $response): void
    {
        ignore_user_abort(true);
        ob_start();
        self::emitBody($response);
        $response->withHeader('Content-Encoding', 'none');
        $response->withHeader('Content-Length', (string)ob_get_length());
        $response->withHeader('Connection', 'close');
        self::emitStatusLine($response);
        self::emitHeaders($response);
        self::logResponse($response);

        // Flush all output.
        ob_end_flush();
        @ob_flush();
        flush();

        // close fpm request

        session_write_close();
        fastcgi_finish_request();
    }

    private static function logRequest(Request $request): void
    {
        $database = self::getDB();
        $sql = <<<SQL
            DELETE FROM plectrum_logs WHERE time < NOW() - INTERVAL 7 DAY;
            INSERT INTO plectrum_logs (`type`, headers, body) VALUES ('request', ?, ?)
        SQL;
        $pdo = $database->prepare($sql);
        $pdo->execute([json_encode($request->getHeaders()), $request->getBody()]);
    }

    private static function logResponse(Response $response): void
    {
        $database = self::getDB();
        $sql = <<<SQL
            INSERT INTO plectrum_logs (`type`, headers, body) VALUES ('response', ?, ?)
        SQL;
        $pdo = $database->prepare($sql);
        $pdo->execute([json_encode($response->getHeaders()), $response->getBody()]);
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
            empty($headers['X-Signature-Ed25519'])
            || empty($headers['X-Signature-Timestamp'])
        ) {
            return false;
        }

        $signature = $headers['X-Signature-Ed25519'][0];
        $timestamp = $headers['X-Signature-Timestamp'][0];

        if (empty($signature)) {
            return false;
        }

        if (!empty(trim($signature, '0..9A..Fa..f'))) {
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

    private static function handleCommand(Request $request): void
    {
        $payload = json_decode($request->getBody());
        $commandName = $payload->data->name;
        $commmandClass = "Pingumask\Plectrum\Command\\" . ucfirst($commandName);

        spl_autoload_register(function ($commandName) {
            $commandPath = self::basePath() . "/src/command/" . ucfirst($commandName) . ".php";
            if (file_exists($commandPath)) {
                require_once $commandPath;
            }
        });

        if (
            class_exists($commmandClass)
        ) {
            $controller = new $commmandClass();
            if (is_a($controller,  AbstractCommand::class)) {
                $commmandClass::execute($request);
            } else {
                self::sendReply(new Response(501, [], "$commmandClass is not a command"));
            }
        } else {
            self::sendReply(new Response(501, [], "$commmandClass command is not implemented"));
        }
    }

    private static function handleComponent(Request $request): void
    {
        $payload = json_decode($request->getBody());
        switch ($payload->data->component_type) {
            case ComponentType::BUTTON->value:
                self::handleButton($request);
                return;
            case ComponentType::CHANNEL_SELECT->value:
                self::handleChannelSelect($request);
                return;
            default:
                self::sendReply(new Response(501, [], "Component type  {$payload->data->component_type} not implemented"));
        }
    }

    private static function handleButton(Request $request): void
    {
        $payload = json_decode($request->getBody());
        $buttonName = $payload->data->custom_id;
        $buttonClass = "Pingumask\Plectrum\Button\\$buttonName";

        spl_autoload_register(function ($buttonName) {
            $buttonPath = self::basePath() . "/src/button/{$buttonName}.php";
            if (file_exists($buttonPath)) {
                require_once $buttonPath;
            }
        });

        if (
            class_exists($buttonClass)
        ) {
            $controller = new $buttonClass();
            if (is_a($controller,  AbstractButton::class)) {
                $buttonClass::execute($request);
            } else {
                self::sendReply(new Response(501, [], "$buttonClass is not a button"));
            }
        } else {
            self::sendReply(new Response(501, [], "$buttonClass button is not implemented"));
        }
    }

    private static function handleChannelSelect(Request $request): void
    {
        $payload = json_decode($request->getBody());
        $selectName = $payload->data->custom_id;
        $selectClass = "Pingumask\Plectrum\Select\\$selectName";

        spl_autoload_register(function ($selectName) {
            $selectPath = self::basePath() . "/src/select/{$selectName}.php";
            if (file_exists($selectPath)) {
                require_once $selectPath;
            }
        });

        if (
            class_exists($selectClass)
        ) {
            $controller = new $selectClass();
            if (is_a($controller,  AbstractSelect::class)) {
                $selectClass::execute($request);
            } else {
                self::sendReply(new Response(501, [], "$selectClass is not a select component"));
            }
        } else {
            self::sendReply(new Response(501, [], "$selectClass select is not implemented"));
        }
    }
}
