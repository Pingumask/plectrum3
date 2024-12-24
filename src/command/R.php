<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Embed;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\OptionType;

class R extends AbstractCommand
{
    public const NAME = 'r';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Lance des dés';
    public const OPTIONS = [
        [
            "name" => "dés",
            "description" => "Le nombre de dés et le nombre de faces au format jeu de rôle : [dés]d[faces]",
            "type" => OptionType::STRING,
            "required" => true,
        ],
    ];

    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        if (!str_contains($interaction->data->options[0]->value, 'd')) {
            self::reply(content: "L'envoi de dés doit se faire au format jeu de rôles : [nombre de dés]d[nombre de faces]
                    \nexample: \`/r 3d6\` pour lancer trois dés à six faces.", flags: Flag::EPHEMERAL->value);
            return;
        }
        list($dices, $sides) = explode('d', $interaction->data->options[0]->value);
        $dices = (int)$dices;
        $sides = (int)$sides;

        if (empty($dices) || $dices <  1 || empty($sides) || $sides < 1) {
            self::reply(content: "L'envoi de dés doit se faire au format jeu de rôles : [nombre de dés]d[nombre de faces]
                    \nexample: \`/r 3d6\` pour lancer trois dés à six faces.", flags: Flag::EPHEMERAL->value);
            return;
        }

        if ($dices > 10_000) {
            self::reply(content: "Impossible de lancer plus de 10 000 dés à la fois.", flags: Flag::EPHEMERAL->value);
            return;
        }

        self::reply(type: InteractionCallback::DEFERRED_CHANNEL_MESSAGE_WITH_SOURCE);

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

        $title = "Lance {$dices} dé" . (($dices > 1) ? "s" : "") . " à {$sides} faces :";

        $embed = new Embed(title: $title, description: $description, footerText: $footer);

        self::updateReply(request: $request, embeds: [$embed]);
    }
}
