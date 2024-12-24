<?php

namespace Pingumask\Discord;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Discord\InteractionInterface;
use Pingumask\Discord\Embed;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Plectrum\App;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractInteraction implements InteractionInterface
{
    /**
     * @param Embed[] $embeds
     * @param InteractionCallback $type
     */
    protected static function reply(?string $content = null, int $flags = 0, array $embeds = [], InteractionCallback $type = InteractionCallback::CHANNEL_MESSAGE_WITH_SOURCE): void
    {
        $message = new Message(
            type: $type,
            content: $content,
            embeds: $embeds,
            flags: $flags,
        );
        self::replyWithMessage($message);
    }

    protected static function replyWithMessage(Message $message): void
    {
        $body = json_encode($message);
        assert(is_string($body));
        $token = App::getConf('discord', 'token');
        $reply = new Response(
            status: 200,
            headers: [
                'Authorization' => "Bot $token",
                'Content-Type' => 'application/json'
            ],
            body: $body,
        );
        App::sendReply($reply);
    }

    /**
     * @param Embed[] $embeds
     */
    protected static function updateReply(Request $request, string $content = "", int $flags = 0, array $embeds = []): void
    {
        $message = new Message(
            content: $content,
            embeds: $embeds,
            flags: $flags,
        );
        self::updateReplyWithMessage($request, $message);
    }

    public static function updateReplyWithMessage(Request $request, Message $message): void
    {
        $body = json_encode($message->data);
        assert(is_string($body));

        $interaction = json_decode($request->getBody(), true);
        $database = App::getDB();
        $sql = <<<SQL
            INSERT INTO plectrum_logs (`type`, headers, body) VALUES (?, ?, ?)
        SQL;
        $stmt = $database->prepare($sql);
        $stmt->execute(['patch', "https://discord.com/api/v10/webhooks/{$interaction['application_id']}/{$interaction['token']}/messages/@original", $body]);
        $result = self::makeApiCall(
            method: "PATCH",
            url: "https://discord.com/api/v10/webhooks/{$interaction['application_id']}/{$interaction['token']}/messages/@original",
            body: $body,
        );
        $stmt->execute(['response', json_encode($result->getHeaders()), json_encode((string)$result->getBody())]);
    }

    /**
     * TODO: log call and response
     */
    public static function makeApiCall(string $url, string $body = '', string $method = 'GET'): ResponseInterface
    {
        $token = App::getConf('discord', 'token');
        $guzzleClient = new \GuzzleHttp\Client();
        return $guzzleClient->request(
            $method,
            $url,
            [
                'headers' => [
                    'Authorization' => "Bot $token",
                    'Content-Type' => 'application/json',
                ],
                'body' => $body,
            ]
        );
    }
}
