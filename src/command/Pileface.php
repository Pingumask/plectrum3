<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;

class Pileface extends AbstractCommand
{
    public const NAME = 'pileface';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Tire à pile ou face';
    public const OPTIONS = [];

    public static function execute(Request $request): Response
    {
        $coin = rand(0, 1) ? 'Pile' : 'Face';
        return self::genReply($coin, false);
    }
}
