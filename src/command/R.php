<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;

class R extends AbstractCommand
{
    public const NAME = 'r';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Lance des dés';
    public const OPTIONS = [
        [
            "name" => "dés",
            "description" => "Le nombre de dés et le nombre de faces au format jeu de rôle : [dés]d[faces]",
            "type" => 3, //type 3 = STRING
            "required" => true,
        ],
    ];

    public static function execute(Request $request): Response
    {
        return self::genReply("Cette commande n'est pas vraiment prete, déso", false);
    }
}
