<?php

namespace Pingumask\Plectrum\Partial;

use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\App;
use Pingumask\Plectrum\Partial\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{

    /**
     * @return array<mixed> the definition of the command for registration process
     */
    public static function getDefinition(): array
    {
        return [
            'name' => static::NAME,
            'type' => 1,
            'description' => static::DESCRIPTION,
            'category' => static::CATEGORY,
            'options' => static::OPTIONS
        ];
    }

    /**
     * @param array<mixed> $embeds
     */
    protected static function genReply(?string $content = null, ?int $flags = 0, ?array $embeds = null): Response
    {
        $message = [
            'type' => 4,
            'data' => [
                'allowed_mentions' => [
                    'parse' => []
                ]
            ]
        ];
        if (!is_null($content)) {
            $message['data']['content'] = $content;
        }
        if (!is_null($embeds)) {
            $message['data']['embeds'] = $embeds;
        }
        if ($flags) {
            $message['data']['flags'] = $flags;
        }
        $body = json_encode($message);
        assert(is_string($body));
        $token = App::getConf('discord', 'token');
        return new Response(
            200,
            [
                'Authorization' => "Bot $token",
                'Content-Type' => 'application/json'
            ],
            $body
        );
    }
}
