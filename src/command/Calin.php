<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;
use Pingumask\Plectrum\Partial\Embed;
use Pingumask\Plectrum\Partial\DiscordConst;

class Calin extends AbstractCommand
{
    public const NAME = 'calin';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = "Envoyer un calin à quelqu'un";
    public const OPTIONS = [
        [
            "name" => "destinataire",
            "description" => "A qui faire le câlin",
            "type" => DiscordConst::OPTION_TYPE_STRING,
            "required" => true,
        ],
    ];
    const IMAGES = [
        "https://c.tenor.com/DxMIq9-tS5YAAAAC/milk-and-mocha-bear-couple.gif",
        "https://c.tenor.com/vVBFWMH7J9oAAAAC/hug-peachcat.gif",
        "https://c.tenor.com/wqCAHtQuTnkAAAAC/milk-and-mocha-hug.gif",
        "https://c.tenor.com/FduR7Yr84OQAAAAC/milk-and-mocha-kiss.gif",
        "https://c.tenor.com/jX1-mxefJ54AAAAC/cat-hug.gif",
    ];

    public static function execute(Request $request): Response
    {
        $interaction = json_decode($request->getBody());
        // TODO: handle per guild timer
        $picked = self::IMAGES[array_rand(self::IMAGES)];
        $target = $interaction->data->options[0]->value;
        if (strlen($target) > 300) {
            return self::genReply(content: "Ça fait beaucoup là, non ?", flags: DiscordConst::FLAG_EPHEMERAL);
        }
        $embed = new Embed(
            description: "<@{$interaction->member->user->id}> fait un câlin à {$target} <3.",
            imageUrl: $picked
        );

        //TODO: edit embed to remove image after delay
        //TODO: check mentions handling

        return self::genReply(content: "<@{$interaction->member->user->id}> fait un câlin à {$target} <3.", embeds: [$embed]);
    }
}
