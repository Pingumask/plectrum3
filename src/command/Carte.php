<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Embed;

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

    public static function execute(Request $request): void
    {
        $symbole = self::SYMBOLES[array_rand(self::SYMBOLES)];
        $valeur = self::VALEURS[array_rand(self::VALEURS)];

        $embed = new Embed(title: $symbole . $valeur);

        self::reply(embeds: [$embed]);
    }
}
