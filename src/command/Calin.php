<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Embed;
use Pingumask\Discord\Flag;
use Pingumask\Discord\OptionType;
use Pingumask\Plectrum\Model\Cooldown;

class Calin extends AbstractCommand
{
    public const NAME = 'calin';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = "Envoyer un calin à quelqu'un";
    public const OPTIONS = [
        [
            "name" => "destinataire",
            "description" => "A qui faire le câlin",
            "type" => OptionType::STRING,
            "required" => true,
        ],
    ];
    const IMAGES = [
        "https://c.tenor.com/DxMIq9-tS5YAAAAC/milk-and-mocha-bear-couple.gif",
        "https://c.tenor.com/vVBFWMH7J9oAAAAC/hug-peachcat.gif",
        "https://c.tenor.com/wqCAHtQuTnkAAAAC/milk-and-mocha-hug.gif",
        "https://c.tenor.com/FduR7Yr84OQAAAAC/milk-and-mocha-kiss.gif",
    ];

    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $picked = self::IMAGES[array_rand(self::IMAGES)];
        $target = $interaction->data->options[0]->value;
        if (strlen($target) > 300) {
            self::reply(content: "Ça fait beaucoup là, non ?", flags: Flag::EPHEMERAL->value);
            return;
        }

        if ($time = Cooldown::getEnd(
            guild: $interaction->guild_id,
            user: $interaction->member->user->id,
            command: 'Calin',
            interval: 300
        )) {
            self::reply(content: "Tu fais ça trop souvent, tu pourra recommencer <t:$time:R>", flags: Flag::EPHEMERAL->value);
            return;
        } else {
            Cooldown::set(
                guild: $interaction->guild_id,
                user: $interaction->member->user->id,
                command: 'Calin'
            );
        }

        $embed = new Embed(
            imageUrl: $picked,
        );

        // send initial calin with mention
        self::reply(content: "<@{$interaction->member->user->id}> fait un câlin à {$target} <3.", embeds: [$embed]);

        sleep(30);

        // remove gif
        $embed = new Embed(
            description: "<@{$interaction->member->user->id}> fait un calin à {$target} <3.",
        );
        self::updateReply(request: $request, content: "", embeds: [$embed]);
    }
}
