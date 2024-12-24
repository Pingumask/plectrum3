<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;

class Pileface extends AbstractCommand
{
    public const NAME = 'pileface';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Tire à pile ou face';
    public const OPTIONS = [];

    public static function execute(Request $request): void
    {
        $coin = rand(0, 1) ? 'Pile' : 'Face';
        self::reply(content: $coin);
    }
}
