<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;

class Carte extends AbstractCommand
{
    public const NAME = 'carte';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Tire une carte';
    const VALEURS = [
        "A",
        "2",
        "3",
        "4",
        "5",
        "6",
        "7",
        "8",
        "9",
        "10",
        "V",
        "D",
        "R",
    ];
    const SYMBOLES = ["♠", "♥", "♣", "♦"];

    public static function execute(Request $request): Response
    {
        $symbole = self::SYMBOLES[array_rand(self::SYMBOLES)];
        $valeur = self::VALEURS[array_rand(self::VALEURS)];
        $embed = [
            "title" => $symbole . $valeur,
        ];
        return self::genReply(embeds: [$embed]);
    }
}
