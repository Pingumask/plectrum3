<?php

namespace Pingumask\Plectrum\Button;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractButton;
use Pingumask\Discord\Flag;
use Pingumask\Discord\InteractionCallback;
use Pingumask\Discord\Message;

class RejectRename extends AbstractButton
{
    public static function execute(Request $request): void
    {
        $interaction = json_decode($request->getBody());
        $message = Message::__cast($interaction->message);
        $newEmbed = $message->data->embeds[0];
        $now = time();
        $channel = 0;
        $demandeur = "";
        foreach ($newEmbed->fields as &$field) {
            if ($field->name === "Status") {
                $field->value = "🚫 Refusée par <@{$interaction->member->user->id}> le <t:{$now}>";
            }
            if ($field->name === "Channel") {
                $channel = (int)str_replace(["<#", ">"], "", $field->value);
            }
            if ($field->name === "Demandeur") {
                $demandeur = $field->value;
            }
        }
        self::reply(
            type: InteractionCallback::UPDATE_MESSAGE,
            content: $message->data->content,
            embeds: [$newEmbed],
        );
        $feedback = new Message(
            content: "{$demandeur} Demande de rename rejetée par l'équipe de modération",
        );
        $feedback->sendToChannel($channel);
    }
}
