<?php

namespace Pingumask\Plectrum\Button;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractButton;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Plectrum\Command\Dico;

class PreviousDefinition extends AbstractButton
{
    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $message = Message::__cast($interaction->message);
        $pageField = explode('/', $message->data->embeds[0]->getField('Page')?->value ?? '1/1');
        [$current, $max] = $pageField;
        $page = (((int)$current - 1) % (int)$max) ?: (int)$max; // Modulo of current page -1, use last page if modulo is 0
        $newMessage = Dico::getWord($message->data->embeds[0]->title ?? 'Test', $page);
        if (is_null($newMessage)) {
            self::reply(content: "Erreur: Page suivante introuvable", flags: Flag::EPHEMERAL->value);
            die();
        }
        $newMessage->type = InteractionCallback::UPDATE_MESSAGE;
        self::replyWithMessage($newMessage);
    }
}
