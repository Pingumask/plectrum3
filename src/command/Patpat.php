<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Embed;
use Pingumask\Discord\Flag;
use Pingumask\Discord\OptionType;
use Pingumask\Plectrum\Model\Cooldown;

class Patpat extends AbstractCommand
{
    public const NAME = 'patpat';
    public const CATEGORY = 'fun';
    public const DESCRIPTION = "Consoler quelqu'un";
    public const OPTIONS = [
        [
            "name" => "destinataire",
            "description" => "A qui faire le câlin",
            "type" => OptionType::STRING,
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
            command: 'Patpat',
            interval: 300
        )) {
            self::reply(content: "Tu fais ça trop souvent, tu pourra recommencer <t:$time:R>", flags: Flag::EPHEMERAL->value);
            return;
        } else {
            Cooldown::set(
                guild: $interaction->guild_id,
                user: $interaction->member->user->id,
                command: 'Patpat'
            );
        }

        $embed = new Embed(
            imageUrl: $picked,
        );

        // send initial patpat with mention
        self::reply(content: "<@{$interaction->member->user->id}> fait un patpat à {$target} <3.", embeds: [$embed]);

        sleep(30);

        // remove gif
        $embed = new Embed(
            description: "<@{$interaction->member->user->id}> fait un patpat à {$target} <3.",
        );
        self::updateReply(request: $request, content: "", embeds: [$embed]);
    }
}
