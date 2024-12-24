<?php

namespace Pingumask\Plectrum\Command;

use GuzzleHttp\Psr7\Request;
use Pingumask\Discord\AbstractCommand;
use Pingumask\Discord\Button;
use Pingumask\Discord\ButtonStyle;
use Pingumask\Discord\Embed;
use Pingumask\Discord\Flag;
use Pingumask\Discord\Message;
use Pingumask\Discord\OptionType;
use Pingumask\Discord\Permission;
use Pingumask\Plectrum\App;

class Rename extends AbstractCommand
{
    public const NAME = 'rename';
    public const CATEGORY = 'outils';
    public const DESCRIPTION = 'Effectue une demande de changement de pseudo auprès de la modération';
    public const OPTIONS = [
        [
            "name" => "pseudo",
            "description" => "Le nouveau pseudo que vous souhaitez avoir",
            "type" => OptionType::STRING,
            "required" => false,
        ],
    ];

    public static function execute(Request $request): void
    {
        // check if config OK
        $interaction = json_decode($request->getBody());
        $database = App::getDB();
        $sqlout = <<<SQL
            SELECT `value` FROM plectrum_config WHERE guild = ? AND tag = "RENAME_CHANNEL";
        SQL;
        $stmt = $database->prepare($sqlout);
        $stmt->execute([$interaction?->guild?->id]);

        /** @var int */
        $renameChannel = (int)$stmt->fetch(\PDO::FETCH_COLUMN) ?: 0;

        /** @var string */
        $newNick = $interaction?->data?->options[0]?->value ?? "";

        if (!$renameChannel) {
            if ((int)$interaction?->member?->permissions & Permission::ADMINISTRATOR) {
                self::reply(content: "Pour activer les demandes de rename sur ce serveur, utilisez `/configrename #channel-de-reception-des-demandes`", flags: Flag::EPHEMERAL->value);
                return;
            }
            self::reply(content: "Les demandes de rename ne sont pas actives sur ce serveur", flags: Flag::EPHEMERAL->value);
            return;
        }

        //TODO: mettre en place un cooldown

        //TODO: vérifier que le membre est modifiable par le bot

        // Pseudo trop long
        if (strlen($newNick) > 32) {
            self::reply(content: "Un pseudo Discord ne peut être plus long que 32 caractères", flags: Flag::EPHEMERAL->value);
            return;
        }

        // Pseudo identique à l'ancien
        if ($interaction?->member?->nick === $newNick) {
            self::reply(content: "Mais, c'est le même pseudo qu'avant ça...", flags: Flag::EPHEMERAL->value);
            return;
        }

        // Demande prise en compte
        if (empty($newNick)) {
            self::reply(content: "Votre demande de réinitialisation de pseudo a été transmise à l'équipe de modération", flags: Flag::EPHEMERAL->value);
        } else {
            self::reply(content: "Votre demande de changement de pseudo a été transmise à l'équipe de modération", flags: Flag::EPHEMERAL->value);
        }

        //TODO: ajouter les boutons d'intéraction
        $modMessage = new Embed(title: "Demande de rename");
        $modMessage
            ->addField(name: "Channel", value: "<#{$interaction?->channel_id}>", inline: false)
            ->addField(name: "Demandeur", value: "<@{$interaction?->member?->user?->id}>", inline: true)
            ->addField(name: "Ancien Pseudo", value: $interaction?->member?->nick ?? $interaction?->member?->user?->global_name ?? "null", inline: true)
            ->addField(name: "Nouveau Pseudo", value: empty($newNick) ? "[Réinitialisation]" : $newNick, inline: true)
            ->addField(name: "Status", value: "🕐 En attente", inline: true);

        $timestamp = time();

        $message = new Message(embeds: [$modMessage]);
        $message->addComponentsRow()
            ->addButton(style: ButtonStyle::SUCCESS, label: 'Accepter', custom_id: 'AcceptRename')
            ->addButton(style: ButtonStyle::DANGER, label: 'Refuser', custom_id: 'RejectRename');
        $message->sendToChannel(channel: $renameChannel);
    }
}
