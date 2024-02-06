<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;

class Ping extends AbstractCommand
{
    public const NAME = "Ping";
    public const CATEGORY = "Infos";
    public const DESCRIPTION = "Teste la latence du bot";
    public const UTILISATION = "{prefix}ping";
    public const OPTIONS = [];

    public static function execute(Request $request): Response
    {
        $body = json_encode([
            'type' => 4,
            'data' => [
                'tts' => false,
                'content' => 'pong',
                'ephemeral' => true,
                'embeds' => [],
                'allowed_mentions' => [
                    'parse' => []
                ]
            ]
        ]);
        assert(is_string($body));
        return new Response(
            200,
            [],
            $body
        );
    }
}
