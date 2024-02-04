<?php

namespace Pingumask\Plectrum\Controller;

use Pingumask\Plectrum\Model\AbstractCommand;

class Ping extends AbstractCommand
{
    public const NAME = "Missing Name";
    public const CATEGORY = "Missing Category";
    public const DESCRIPTION = "Missing Description";
    public const UTILISATION = "Missing Utilisation";
    public const OPTIONS = [];

    public static function execute($payload)
    {
        return [
            'code' => 200,
            'payload' => [
                'type' => 4,
                'data' => [
                    'tts' => false,
                    'content' => 'pong',
                    'embeds' => [],
                    'allowed_mentions' => [
                        'parse' => []
                    ]
                ]
            ]
        ];
    }
}
