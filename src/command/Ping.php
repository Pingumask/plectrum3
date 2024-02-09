<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;

class Ping extends AbstractCommand
{
    public const NAME = 'ping';
    public const CATEGORY = 'infos';
    public const DESCRIPTION = 'Teste la latence du bot';
    public const OPTIONS = [];

    public static function execute(Request $request): Response
    {
        return self::genReply(content: 'Pong');
    }
}
