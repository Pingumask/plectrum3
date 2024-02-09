<?php

namespace Pingumask\Plectrum\Partial;

use GuzzleHttp\Client as GuzzleCLient;
use GuzzleHttp\Psr7\Request;
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
    protected static function genReply(string $content = "", bool $ephemeral = false, array $embeds = []): Response
    {
        $body = json_encode([
            'type' => 4,
            'data' => [
                'tts' => false,
                'content' => $content,
                'ephemeral' => $ephemeral,
                'embeds' => $embeds,
                'allowed_mentions' => [
                    'parse' => []
                ]
            ]
        ]);
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
