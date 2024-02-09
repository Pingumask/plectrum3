<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;
use Pingumask\Plectrum\Partial\Message;

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
        $interaction = json_decode($request->getBody());
        list($dices, $sides) = explode('d', $interaction->data->options[0]->value);
        $dices = (int)$dices;
        $sides = (int)$sides;

        if (empty($dices) || $dices <  1 || empty($sides) || $sides < 1) {
            return self::genReply("L'envoi de dés soit se faire au format jeu de rôles : [nombre de dés]d[nombre de faces]
                    \nexample: \`/r 3d6\` pour lancer trois dés à six faces.", Message::FLAG_EPHEMERAL);
        }

        if ($dices > 10000) {
            return self::genReply("Impossible de lancer plus de 10 000 dés à la fois.", Message::FLAG_EPHEMERAL);
        }

        $rolls = [];
        $total = 0;

        for ($throws = 0; $throws < $dices; $throws++) {
            $throw = rand(1, $sides);
            $total += $throw;
            $rolls[] = $throw;
        }

        $detail = "[" . implode("] [", $rolls) . "]";
        $total = "Total : $total";

        $description = '';
        $footer = "";

        if (strlen($detail) > 1951) {
            $description = $total;
        } else {
            $description = $detail;
            $footer = $total;
        }

        $embed = [
            "title" => "Lance {$dices} dé" . (($dices > 1) ? "s" : "") . " à {$sides} faces :",
            "description" => $description,
            "footer" => [
                "text" => $footer,
            ],
        ];

        return self::genReply(embeds: [$embed]);
    }
}
