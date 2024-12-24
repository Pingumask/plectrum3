<?php

namespace Pingumask\Plectrum\Button;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractButton;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Member;
use Pingumask\Discord\Message;

class AcceptRename extends AbstractButton
{
    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $message = Message::__cast($interaction->message);
        $newEmbed = $message->data->embeds[0];
        $now = time();
        $channel = 0;
        $demandeur = "";
        $oldNick = "";
        $newNick = "";
        $validator = $interaction->member->user->id;
        foreach ($newEmbed->fields as &$field) {
            if ($field->name === "Status") {
                $field->value = "✅ Acceptée par <@{$validator}> le <t:{$now}>";
            }
            if ($field->name === "Channel") {
                $channel = (int)str_replace(["<#", ">"], "", $field->value);
            }
            if ($field->name === "Demandeur") {
                $demandeur = $field->value;
            }
            if ($field->name === "Ancien Pseudo") {
                $oldNick = $field->value;
            }
            if ($field->name === "Nouveau Pseudo") {
                $newNick = $field->value;
            }
        }
        self::reply(
            type: InteractionCallback::UPDATE_MESSAGE,
            content: $message->data->content,
            embeds: [$newEmbed],
        );
        $newNick = ($newNick === '[Réinitialisation]') ? null : $newNick;
        if (is_null($newNick)) {
            $feedback = new Message(
                content: "{$demandeur} Votre demande de réinitialisation de pseudo a été acceptée par <@{$validator}>",
            );
        } else {
            $feedback = new Message(
                content: "{$demandeur} Votre demande de rename de `{$oldNick}` en `{$newNick}` a été acceptée par <@{$validator}>",
            );
        }
        $feedback->sendToChannel($channel);

        Member::setNickname(
            guild_id: $interaction->guild->id,
            user_id: (int)str_replace(["<@", ">"], "", $demandeur),
            nickname: $newNick,
        );
    }
}
