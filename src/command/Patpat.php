<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Pingumask\Plectrum\Partial\AbstractCommand;
use Pingumask\Plectrum\Partial\Embed;
use Pingumask\Plectrum\Partial\DiscordConst;

class Patpat extends AbstractCommand
{
    public const NAME = 'patpat';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = "Consoler quelqu'un";
    public const OPTIONS = [
        [
            "name" => "destinataire",
            "description" => "A qui faire le câlin",
            "type" => DiscordConst::OPTION_TYPE_STRING,
            "required" => true,
        ],
    ];
    const IMAGES = [
        "https://c.tenor.com/o4_qJ_1tzz8AAAAC/good-night.gif",
        "https://c.tenor.com/5VbS6pyBYvsAAAAC/gif-fofinho-heart.gif",
        "https://c.tenor.com/2kmDRTqIIDAAAAAC/kitty-so-cute-head-pat.gif",
        "https://c.tenor.com/qRW7PesyukIAAAAC/peach-cat-goma.gif",
        "https://c.tenor.com/6fa6sool-Y4AAAAi/love-pat.gif",
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
            description: "<@{$interaction->member->user->id}> fait un patpat à {$target} <3.",
            imageUrl: $picked
        );

        //TODO: edit embed to remove image after delay
        //TODO: check mentions handling

        return self::genReply(content: "<@{$interaction->member->user->id}> fait un patpat à {$target} <3.", embeds: [$embed]);
    }
}
