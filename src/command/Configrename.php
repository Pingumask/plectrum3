<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\ChannelType;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Discord\OptionType;
use Pingumask\Discord\Permission;
use Pingumask\Plectrum\App;

class Configrename extends AbstractCommand
{
    public const NAME = 'configrename';
    public const CATEGORY = 'admin';
    public const DESCRIPTION = 'Configure le salon dans lequel doivent apparaitre les demandes de rename';

    public static function execute(Request $request): void
    {
        // check if config OK
        $interaction = json_decode($request->getBody());
        if (!(int)$interaction?->member?->permissions & Permission::ADMINISTRATOR) {
            self::reply(content: "Cette commande est reservée aux administrateurs du serveur !", flags: Flag::EPHEMERAL->value);
            return;
        }

        //reply
        $message = new Message(type: InteractionCallback::CHANNEL_MESSAGE_WITH_SOURCE, content: "Channel de récéption des demandes de rename:");
        $message->addComponentsRow()
            ->addChannelSelect(
                custom_id: 'RenameChannelSelect',
                placeholder: 'Channel de réception des rename',
                channel_types: [ChannelType::GUILD_TEXT],
            );
        self::replyWithMessage($message);
    }
}
