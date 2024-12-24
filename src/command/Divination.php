<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Embed;
use Pingumask\Discord\Flag;
use Pingumask\Discord\OptionType;

class Divination extends AbstractCommand
{
    public const NAME = 'divination';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = 'Donne une réponse aléatoire';
    public const OPTIONS = [
        [
            "name" => "question",
            "description" => "La question à laquelle le bot va répondre",
            "type" => OptionType::STRING,
            "required" => true,
        ],
    ];
    const PHRASES = [
        "Oui.",
        "Bien sûr.",
        "Tout à fait.",
        "Tutafeh.",
        "Absolument !",
        "Evidemment !",
        "Tu en doutes ?",
        "Oui, mais attention, ça pourrait changer.",
        "C'est une certitude absolue !",
        "Oui, mais ça sera pas facile !",
        "C'est une certitude",
        "A n'en point douter",

        "Ca se pourrait",
        "Peut être",
        "C'est pas impossible",
        "Ptêt bein qu'oui, Ptêt bein qu'non",
        "Mais j'en sais rien moi !",
        "Je t'en pose des questions ?",
        "C'est pas toi qui décide !",
        "Y a vraiment des gens qui se demandent ça ?",
        "La réponse devrait te paraitre évidente.",
        "Je refuse de répondre à ça, mais j'en pense pas moins !",
        "Peu me chaut.",
        "Je n'en ai cure.",
        "J'ai pas envie de répondre à ça",
        "Je vois pas pourquoi je prendrais la peine de répondre.",

        "Non ||, mais t'as vu c'que t'écoutes ?||",
        "Non, mais c'est pas faute d'essayer.",
        "Mais ça va pas de dire des choses comme ça ?",
        "Non, mais ça viendra peut-être.",
        "Pas vraiment...",
        "Lol nope.",
        "Non.",
        "Absolument ||pas !||",
        "Pas du tout !",
        "Et la marmotte, elle met le chocolat dans le papier alu.",
        "Ha ha ha... Non !",
        "Négatif.",
        "Mais bien sûr que non enfin !",
    ];

    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $question = $interaction->data->options[0]->value;
        $reponse = self::PHRASES[array_rand(self::PHRASES)];
        if (strlen($question) > 1200) {
            self::reply(content: "J'ai pas compris la question", flags: Flag::EPHEMERAL->value);
            return;
        }
        $embed = new Embed(description: "**Question:** {$question}\n**Réponse:** {$reponse}");

        self::reply(embeds: [$embed]);
    }
}
