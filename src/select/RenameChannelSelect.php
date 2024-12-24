<?php

namespace Pingumask\Plectrum\Select;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractSelect;
use Pingumask\Discord\Embed;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;
use Pingumask\Plectrum\App;

class RenameChannelSelect extends AbstractSelect
{
    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $message = Message::__cast($interaction->message);
        $channel = $interaction->data->values[0];

        //save config to db
        $database = App::getDB();
        $sqlout = <<<SQL
            DELETE FROM plectrum_config WHERE guild = ? AND tag = ?;
        SQL;
        $stmt = $database->prepare($sqlout);
        $stmt->execute([$interaction?->guild?->id, 'RENAME_CHANNEL']);
        $sqlin = <<<SQL
            INSERT INTO plectrum_config (guild, tag, value) VALUES (?, ?, ?)
        SQL;
        $stmt = $database->prepare($sqlin);
        $stmt->execute([$interaction?->guild?->id, 'RENAME_CHANNEL', $channel]);

        $message->data->content = "Channel de réception des rename : <#{$channel}>";
        $message->data->components = [];
        $message->type = InteractionCallback::UPDATE_MESSAGE;
        self::replyWithMessage($message);
    }
}
